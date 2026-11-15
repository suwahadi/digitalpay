<?php

namespace Tests\Feature;

use App\Jobs\ProcessDigitalDelivery;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Notifications\PaymentSuccessNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Tests\TestCase;

class QrisgateWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SECRET = 'whsec_test';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.payment_gateway' => 'qrisgate',
            'services.qrisgate.base_url' => 'https://qrisgate.test/api/v1',
            'services.qrisgate.api_key' => 'qg_live_test',
            'services.qrisgate.webhook_secret' => self::SECRET,
        ]);
        Notification::fake();
        Queue::fake();
    }

    private function pendingOrder(array $orderOverrides = [], array $paymentOverrides = []): Payment
    {
        $method = PaymentMethod::create([
            'code' => 'qris', 'name' => 'QRIS', 'type' => 'qris', 'provider' => 'midtrans',
            'min_amount' => 0, 'is_active' => true, 'sort_order' => 1, 'fee_amount' => 0,
        ]);

        $order = Order::forceCreate(array_merge([
            'uuid' => (string) Str::uuid(),
            'order_number' => 'DIG-QG00001',
            'customer_name' => 'Pembeli',
            'customer_email' => 'pembeli@digitalpay.web.id',
            'payment_method_id' => $method->id,
            'payment_gateway' => Order::QRISGATE,
            'subtotal' => 50000,
            'total_amount' => 50007,
            'unique_code' => 7,
            'status' => 'pending',
            'payment_status' => 'pending',
            'expires_at' => now()->addMinutes(30),
        ], $orderOverrides));

        return Payment::forceCreate(array_merge([
            'uuid' => (string) Str::uuid(),
            'order_id' => $order->id,
            'payment_method_id' => $method->id,
            'amount' => 50007,
            'total_amount' => 50007,
            'customer_type' => 'guest',
            'customer_email' => $order->customer_email,
            'gateway_provider' => Order::QRISGATE,
            'gateway_transaction_id' => 'trx_1',
            'status' => Payment::STATUS_PENDING,
            'expired_at' => $order->expires_at,
        ], $paymentOverrides));
    }

    private function sendWebhook(array $payload, ?string $secret = self::SECRET, ?int $timestamp = null): \Illuminate\Testing\TestResponse
    {
        $body = json_encode($payload);
        $ts = (string) ($timestamp ?? time());

        return $this->call('POST', '/qrisgate/webhook', [], [], [], [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_X_QRISGATE_EVENT' => $payload['event'] ?? '',
            'HTTP_X_QRISGATE_TIMESTAMP' => $ts,
            'HTTP_X_QRISGATE_SIGNATURE' => hash_hmac('sha256', $ts.'.'.$body, $secret),
            'HTTP_X_QRISGATE_DELIVERY_ID' => 'dlv_1',
            'HTTP_X_QRISGATE_ATTEMPT' => '1',
        ], $body);
    }

    private function paidPayload(): array
    {
        return [
            'event' => 'payment.paid',
            'transaction_id' => 'trx_1',
            'external_order_id' => 'DIG-QG00001',
            'base_amount' => 50000,
            'unique_code' => 7,
            'total_amount' => 50007,
            'customer_name' => 'Pembeli',
            'paid_at' => now()->toIso8601String(),
            'created_at' => now()->subMinutes(2)->toIso8601String(),
        ];
    }

    public function test_paid_webhook_marks_order_paid_and_starts_delivery_once(): void
    {
        $payment = $this->pendingOrder();

        $this->sendWebhook($this->paidPayload())->assertOk()->assertJson(['received' => true]);
        $this->sendWebhook($this->paidPayload())->assertOk();

        $order = $payment->order->fresh();
        $this->assertSame('paid', $order->status);
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame(Payment::STATUS_SUCCESS, $payment->fresh()->status);

        Queue::assertPushed(ProcessDigitalDelivery::class, 1);
        Notification::assertSentTimes(PaymentSuccessNotification::class, 1);
    }

    public function test_invalid_signature_is_rejected(): void
    {
        $payment = $this->pendingOrder();

        $this->sendWebhook($this->paidPayload(), 'wrong-secret')->assertStatus(401);
        $this->sendWebhook($this->paidPayload(), self::SECRET, time() - 600)->assertStatus(401);

        $this->assertSame('pending', $payment->order->fresh()->status);
        Queue::assertNothingPushed();
    }

    public function test_late_payment_on_expired_order_is_still_processed(): void
    {
        $payment = $this->pendingOrder(
            ['status' => 'expired', 'payment_status' => 'expired', 'expires_at' => now()->subMinute()],
            ['status' => Payment::STATUS_EXPIRED]
        );

        $this->sendWebhook($this->paidPayload())->assertOk();

        $this->assertSame('paid', $payment->order->fresh()->status);
        Queue::assertPushed(ProcessDigitalDelivery::class, 1);
    }

    public function test_expired_webhook_expires_order_without_email(): void
    {
        $payment = $this->pendingOrder();

        $this->sendWebhook(array_merge($this->paidPayload(), ['event' => 'payment.expired', 'paid_at' => null]))->assertOk();

        $this->assertSame('expired', $payment->order->fresh()->status);
        $this->assertSame(Payment::STATUS_EXPIRED, $payment->fresh()->status);
        Notification::assertNothingSent();
    }

    public function test_unknown_transaction_is_acknowledged(): void
    {
        $this->sendWebhook(array_merge($this->paidPayload(), ['transaction_id' => 'trx_x', 'external_order_id' => 'DIG-NOPE']))
            ->assertOk();

        Queue::assertNothingPushed();
    }

    public function test_sync_command_marks_paid_from_status_endpoint(): void
    {
        $payment = $this->pendingOrder();
        Http::fake(['qrisgate.test/api/v1/transactions/trx_1' => Http::response([
            'success' => true,
            'data' => ['transaction_id' => 'trx_1', 'status' => 'PAID', 'total_amount' => 50007],
        ])]);

        $this->artisan('qrisgate:sync')->assertSuccessful();

        $this->assertSame('paid', $payment->order->fresh()->status);
        Queue::assertPushed(ProcessDigitalDelivery::class, 1);
    }

    public function test_sync_command_leaves_pending_transactions_alone(): void
    {
        $payment = $this->pendingOrder();
        Http::fake(['*' => Http::response(['data' => ['transaction_id' => 'trx_1', 'status' => 'PENDING']])]);

        $this->artisan('qrisgate:sync')->assertSuccessful();

        $this->assertSame('pending', $payment->order->fresh()->status);
    }
}
