<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\BrevoService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Tests\TestCase;

class OrderExpiredNotificationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.brevo.enabled' => true,
            'services.brevo.api_key' => 'xkeysib-test',
            'services.brevo.api_url' => 'https://api.brevo.com/v3',
            'services.brevo.sender_email' => 'no-reply@digitalpay.web.id',
            'services.brevo.sender_name' => 'DigitalPay',
        ]);
        $this->app->forgetInstance(BrevoService::class);
        Http::fake(['api.brevo.com/*' => Http::response(['messageId' => '<x>'], 201)]);
    }

    private function expiredEmails(): array
    {
        return Http::recorded(fn (Request $request) => str_starts_with($request['subject'] ?? '', 'Pesanan Kedaluwarsa'))->values()->all();
    }

    public function test_expiring_an_order_emails_the_customer_once(): void
    {
        Order::forceCreate([
            'uuid' => (string) Str::uuid(),
            'order_number' => 'DIG-EXP001',
            'customer_name' => 'Pembeli',
            'customer_email' => 'pembeli@digitalpay.web.id',
            'subtotal' => 10000,
            'total_amount' => 10000,
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'expires_at' => now()->subMinute(),
        ]);

        $this->artisan('orders:expire')->assertSuccessful();
        $this->artisan('orders:expire')->assertSuccessful();

        $emails = $this->expiredEmails();
        $this->assertCount(1, $emails);

        [$request] = $emails[0];
        $this->assertSame('Pesanan Kedaluwarsa - DIG-EXP001', $request['subject']);
        $this->assertSame('pembeli@digitalpay.web.id', $request['to'][0]['email']);
        $this->assertStringContainsString('DIG-EXP001', $request['htmlContent']);
        $this->assertStringContainsString('Pesan Lagi', $request['htmlContent']);
        $this->assertSame('expired', Order::where('order_number', 'DIG-EXP001')->value('status'));
    }

    public function test_paid_order_is_not_expired_or_emailed(): void
    {
        Order::forceCreate([
            'uuid' => (string) Str::uuid(),
            'order_number' => 'DIG-PAID01',
            'customer_name' => 'Pembeli',
            'customer_email' => 'pembeli@digitalpay.web.id',
            'subtotal' => 10000,
            'total_amount' => 10000,
            'status' => 'paid',
            'payment_status' => 'paid',
            'expires_at' => now()->subMinute(),
        ]);

        $this->artisan('orders:expire')->assertSuccessful();

        $this->assertCount(0, $this->expiredEmails());
    }
}
