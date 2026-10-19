<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class MidtransWebhookTest extends TestCase
{
    use RefreshDatabase;

    private const SERVER_KEY = 'Mid-server-TEST';

    protected function setUp(): void
    {
        parent::setUp();
        config(['midtrans.server_key' => self::SERVER_KEY]);
    }

    private function order(): Order
    {
        $method = PaymentMethod::create([
            'code' => 'qris', 'name' => 'QRIS', 'type' => 'qris', 'provider' => 'midtrans',
            'min_amount' => 0, 'is_active' => true, 'sort_order' => 1, 'fee_amount' => 0,
        ]);
        $order = Order::forceCreate([
            'uuid' => (string) Str::uuid(), 'order_number' => 'DIG-HOOK1', 'customer_name' => 'A',
            'customer_email' => 'a@digitalpay.web.id', 'payment_method_id' => $method->id,
            'subtotal' => 50000, 'total_amount' => 50000, 'status' => 'pending',
            'payment_status' => 'pending', 'expires_at' => now()->addHour(),
        ]);
        Payment::create([
            'uuid' => (string) Str::uuid(), 'order_id' => $order->id, 'payment_method_id' => $method->id,
            'amount' => 50000, 'fee_amount' => 0, 'total_amount' => 50000, 'currency' => 'IDR',
            'customer_type' => 'guest', 'customer_email' => $order->customer_email,
            'customer_phone' => '081234567890', 'gateway_provider' => 'midtrans', 'status' => 'pending',
        ]);

        return $order;
    }

    private function payload(Order $order, string $status): array
    {
        $statusCode = '200';
        $gross = '50000.00';

        return [
            'order_id' => $order->order_number,
            'status_code' => $statusCode,
            'gross_amount' => $gross,
            'transaction_status' => $status,
            'fraud_status' => 'accept',
            'signature_key' => hash('sha512', $order->order_number . $statusCode . $gross . self::SERVER_KEY),
        ];
    }

    public function test_notification_with_invalid_signature_is_rejected(): void
    {
        $order = $this->order();

        $payload = $this->payload($order, 'settlement');
        $payload['signature_key'] = 'palsu';

        $this->postJson('/midtrans/notification', $payload)->assertStatus(401);
        $this->assertSame('pending', $order->fresh()->payment_status);
    }

    public function test_valid_settlement_marks_the_order_paid(): void
    {
        $order = $this->order();

        $this->postJson('/midtrans/notification', $this->payload($order, 'settlement'))->assertOk();

        $order->refresh();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('paid', $order->status);
        $this->assertNotNull($order->midtrans_paid_at);
    }
}
