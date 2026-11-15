<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Services\QrisgateException;
use App\Services\QrisgateService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class QrisgateServiceTest extends TestCase
{
    private const SECRET = 'whsec_test';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.qrisgate.base_url' => 'https://qrisgate.test/api/v1',
            'services.qrisgate.api_key' => 'qg_live_test',
            'services.qrisgate.webhook_secret' => self::SECRET,
        ]);
    }

    private function order(): Order
    {
        return new Order([
            'order_number' => 'DIG-ABC1234',
            'customer_name' => 'Budi',
            'total_amount' => 100700,
        ]);
    }

    public function test_create_transaction_sends_order_and_returns_data(): void
    {
        Http::fake([
            'qrisgate.test/api/v1/transactions' => Http::response([
                'success' => true,
                'data' => [
                    'transaction_id' => 'trx_1',
                    'external_order_id' => 'DIG-ABC1234',
                    'base_amount' => 100700,
                    'unique_code' => 7,
                    'total_amount' => 100707,
                    'status' => 'PENDING',
                    'qr_string' => '000201010212',
                    'checkout_url' => 'https://qrisgate.test/pay/trx_1',
                    'expires_at' => '2026-11-15T10:00:00.000Z',
                ],
            ], 201),
        ]);

        $data = app(QrisgateService::class)->createTransaction($this->order());

        $this->assertSame('trx_1', $data['transaction_id']);
        $this->assertSame(100707, $data['total_amount']);

        Http::assertSent(fn (Request $request) => $request->method() === 'POST'
            && $request->hasHeader('Authorization', 'Bearer qg_live_test')
            && $request['external_order_id'] === 'DIG-ABC1234'
            && $request['amount'] === 100700
            && $request['payment_method'] === 'QRIS');
    }

    public function test_create_transaction_throws_on_api_error(): void
    {
        Http::fake(['*' => Http::response(['success' => false, 'error' => 'Saldo kredit tidak cukup'], 402)]);

        $this->expectException(QrisgateException::class);
        $this->expectExceptionMessage('Saldo kredit tidak cukup');

        app(QrisgateService::class)->createTransaction($this->order());
    }

    public function test_create_transaction_throws_without_api_key(): void
    {
        config(['services.qrisgate.api_key' => null]);
        Http::fake();

        $this->expectException(QrisgateException::class);

        app(QrisgateService::class)->createTransaction($this->order());
    }

    public function test_get_transaction_returns_status(): void
    {
        Http::fake(['qrisgate.test/api/v1/transactions/trx_1' => Http::response(['data' => ['transaction_id' => 'trx_1', 'status' => 'PAID']])]);

        $this->assertSame('PAID', app(QrisgateService::class)->getTransaction('trx_1')['status']);
    }

    public function test_webhook_signature_verification(): void
    {
        $service = app(QrisgateService::class);
        $body = '{"event":"payment.paid"}';
        $ts = (string) time();
        $sig = hash_hmac('sha256', $ts.'.'.$body, self::SECRET);

        $this->assertTrue($service->verifyWebhookSignature($body, $sig, $ts));
        $this->assertTrue($service->verifyWebhookSignature($body, 'sha256='.$sig, $ts));
        $this->assertFalse($service->verifyWebhookSignature($body.' ', $sig, $ts));
        $this->assertFalse($service->verifyWebhookSignature($body, $sig, (string) (time() - 301)));
        $this->assertFalse($service->verifyWebhookSignature($body, null, $ts));
    }
}
