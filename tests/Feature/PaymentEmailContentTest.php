<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Setting;
use App\Notifications\AdminPaymentReceivedNotification;
use App\Notifications\OrderCreatedNotification;
use App\Notifications\PaymentSuccessNotification;
use App\Services\BrevoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Notifications\AnonymousNotifiable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class PaymentEmailContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.brevo.enabled' => true,
            'services.brevo.api_key' => 'xkeysib-test',
            'services.brevo.api_url' => 'https://api.brevo.com/v3',
        ]);
        $this->app->forgetInstance(BrevoService::class);
    }

    private function order(array $overrides = []): Order
    {
        $method = PaymentMethod::create([
            'code' => 'qris', 'name' => 'QRIS', 'type' => 'qris', 'provider' => 'midtrans',
            'min_amount' => 0, 'is_active' => true, 'sort_order' => 1, 'fee_amount' => 0,
        ]);

        return Order::forceCreate(array_merge([
            'uuid' => (string) Str::uuid(),
            'order_number' => 'DIG-MAIL001',
            'customer_name' => 'Budi',
            'customer_email' => 'budi@example.com',
            'payment_method_id' => $method->id,
            'payment_gateway' => Order::QRISGATE,
            'subtotal' => 50000,
            'voucher_code' => 'DISKON10',
            'discount_amount' => 5000,
            'fee_amount' => 315,
            'unique_code' => 7,
            'total_amount' => 45322,
            'status' => 'pending',
            'payment_status' => 'pending',
            'expires_at' => now()->addMinutes(30),
        ], $overrides));
    }

    private function sentHtml(): string
    {
        $html = '';
        Http::assertSent(function (Request $request) use (&$html) {
            $html = $request['htmlContent'] ?? '';

            return true;
        });

        return $html;
    }

    public function test_order_created_email_links_to_invoice_and_shows_discount_and_unique_code(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => '<x>'], 201)]);
        $order = $this->order();

        Notification::route('brevo', 'budi@example.com')->notifyNow(new OrderCreatedNotification($order));

        $html = $this->sentHtml();
        $this->assertStringContainsString(route('invoice.show', 'DIG-MAIL001'), $html);
        $this->assertStringContainsString('Bayar Sekarang', $html);
        $this->assertStringContainsString('Diskon Voucher', $html);
        $this->assertStringContainsString('-Rp 5.000', $html);
        $this->assertStringNotContainsString('DISKON10', $html);
        $this->assertStringContainsString('Kode Unik', $html);
        $this->assertStringContainsString('Rp 45.322', $html);
        $this->assertStringNotContainsStringIgnoringCase('qrisgate', $html);
        $this->assertStringNotContainsStringIgnoringCase('brevo', $html);
    }

    public function test_payment_success_email_shows_discount_and_unique_code(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => '<x>'], 201)]);
        $order = $this->order(['status' => 'paid', 'payment_status' => 'paid', 'midtrans_paid_at' => now()]);

        Notification::route('brevo', 'budi@example.com')->notifyNow(new PaymentSuccessNotification($order));

        $html = $this->sentHtml();
        $this->assertStringContainsString('Diskon Voucher', $html);
        $this->assertStringContainsString('Kode Unik', $html);
        $this->assertStringContainsString('Rp 7', $html);
    }

    public function test_rows_hidden_without_discount_or_unique_code(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => '<x>'], 201)]);
        $order = $this->order(['discount_amount' => 0, 'unique_code' => null, 'payment_gateway' => 'midtrans']);

        Notification::route('brevo', 'budi@example.com')->notifyNow(new OrderCreatedNotification($order));

        $html = $this->sentHtml();
        $this->assertStringNotContainsString('Diskon Voucher', $html);
        $this->assertStringNotContainsString('Kode Unik', $html);
    }

    public function test_admin_email_sent_to_contact_email_when_order_is_paid(): void
    {
        Notification::fake();
        Setting::set('contact_email', 'owner@digitalpay.web.id');
        $order = $this->order();

        $order->markAsPaidFromGateway(now());

        Notification::assertSentOnDemand(
            AdminPaymentReceivedNotification::class,
            fn ($notification, $channels, AnonymousNotifiable $notifiable) => $notifiable->routes['brevo'] === 'owner@digitalpay.web.id'
        );
    }

    public function test_admin_email_also_sent_for_midtrans_orders(): void
    {
        Notification::fake();
        $order = $this->order(['payment_gateway' => 'midtrans']);

        $order->updatePaymentStatus('settlement');

        Notification::assertSentOnDemandTimes(AdminPaymentReceivedNotification::class, 1);
    }

    public function test_admin_email_content(): void
    {
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => '<x>'], 201)]);
        $order = $this->order(['status' => 'paid', 'payment_status' => 'paid', 'midtrans_paid_at' => now()]);

        Notification::route('brevo', 'owner@digitalpay.web.id')
            ->notifyNow(new AdminPaymentReceivedNotification($order, 'owner@digitalpay.web.id'));

        Http::assertSent(fn (Request $request) => $request['to'][0]['email'] === 'owner@digitalpay.web.id'
            && str_contains($request['subject'], 'DIG-MAIL001')
            && str_contains($request['htmlContent'], 'Rp 45.322')
            && str_contains($request['htmlContent'], '/admin/orders/'.$order->id));
    }

    public function test_user_pages_do_not_expose_gateway_details(): void
    {
        $order = $this->order();
        $order->payments()->forceCreate([
            'uuid' => (string) Str::uuid(),
            'payment_method_id' => $order->payment_method_id,
            'amount' => 45322,
            'total_amount' => 45322,
            'customer_type' => 'guest',
            'customer_email' => 'budi@example.com',
            'gateway_provider' => Order::QRISGATE,
            'gateway_transaction_id' => 'trx_hidden',
            'gateway_response' => ['checkout_url' => 'https://qrisgate.web.id/pay/trx_hidden'],
            'payment_url' => 'https://qrisgate.web.id/pay/trx_hidden',
            'status' => 'pending',
        ]);

        $json = json_encode($order->fresh()->load('payment')->toArray());

        $this->assertStringNotContainsStringIgnoringCase('qrisgate', $json);
        $this->assertStringNotContainsString('trx_hidden', $json);
    }
}
