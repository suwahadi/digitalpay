<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Notifications\OrderCreatedNotification;
use App\Services\MidtransService;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Notification;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QrisgateCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    private PaymentMethod $qris;

    private PaymentMethod $gopay;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.payment_gateway' => 'qrisgate',
            'services.qrisgate.base_url' => 'https://qrisgate.test/api/v1',
            'services.qrisgate.api_key' => 'qg_live_test',
        ]);
        $this->mock(MidtransService::class)->shouldNotReceive('createTransaction');
        Notification::fake();

        $category = Category::create(['name' => 'Uji', 'slug' => 'uji', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Produk', 'slug' => 'produk', 'is_active' => true,
        ]);
        $this->variant = ProductVariant::create([
            'product_id' => $product->id, 'name' => 'Paket', 'price' => 100000, 'is_active' => true,
        ]);
        $this->qris = PaymentMethod::create([
            'code' => 'qris', 'name' => 'QRIS', 'type' => 'qris', 'provider' => 'midtrans',
            'fee_type' => 'percentage', 'fee_amount' => 0.7, 'min_amount' => 0, 'is_active' => true, 'sort_order' => 1,
        ]);
        $this->gopay = PaymentMethod::create([
            'code' => 'gopay', 'name' => 'GoPay', 'type' => 'ewallet', 'provider' => 'midtrans',
            'fee_type' => 'percentage', 'fee_amount' => 2, 'min_amount' => 0, 'is_active' => true, 'sort_order' => 2,
        ]);
    }

    private function fakeQrisgate(): void
    {
        Http::fake([
            'qrisgate.test/api/v1/transactions' => fn (Request $request) => Http::response([
                'success' => true,
                'data' => [
                    'transaction_id' => 'trx_1',
                    'external_order_id' => $request['external_order_id'],
                    'merchant_name' => 'DigitalPay',
                    'base_amount' => $request['amount'],
                    'unique_code' => 7,
                    'total_amount' => $request['amount'] + 7,
                    'status' => 'PENDING',
                    'qr_string' => '00020101021226QRTEST6304ABCD',
                    'qr_image_base64' => null,
                    'checkout_url' => 'https://qrisgate.test/pay/trx_1',
                    'expires_at' => now()->addMinutes(30)->toIso8601String(),
                ],
            ], 201),
        ]);
    }

    private function checkout(PaymentMethod $method): \Illuminate\Testing\TestResponse
    {
        return $this->post('/checkout/store', [
            'product_id' => $this->variant->product_id,
            'variant_id' => $this->variant->id,
            'customer_name' => 'Pembeli',
            'customer_email' => 'pembeli@digitalpay.web.id',
            'customer_phone' => '081234567890',
            'payment_method_id' => $method->id,
        ]);
    }

    public function test_only_qris_is_offered(): void
    {
        $this->assertSame(['qris'], PaymentMethod::available()->pluck('code')->all());

        $this->checkout($this->gopay)
            ->assertRedirect(route('products.show', 'produk'))
            ->assertSessionHas('error', 'Metode pembayaran ini sedang tidak tersedia.');
        $this->assertSame(0, Order::count());
    }

    public function test_checkout_creates_qrisgate_transaction_and_shows_qr_on_invoice(): void
    {
        $this->fakeQrisgate();

        $response = $this->checkout($this->qris);
        $order = Order::sole();
        $response->assertRedirect(route('invoice.show', $order->order_number));

        Http::assertSent(fn (Request $request) => $request['external_order_id'] === $order->order_number
            && $request['amount'] === 100700);

        $this->assertTrue($order->isQrisgate());
        $this->assertSame(7, (int) $order->unique_code);
        $this->assertSame(100707, (int) $order->total_amount);
        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->payment_status);

        $payment = Payment::sole();
        $this->assertSame('qrisgate', $payment->gateway_provider);
        $this->assertSame('trx_1', $payment->gateway_transaction_id);
        $this->assertSame(100707, (int) $payment->total_amount);

        Notification::assertSentTimes(OrderCreatedNotification::class, 1);

        $this->get(route('invoice.show', $order->order_number))
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Invoice/Show', false)
                ->where('order.qris.qr_string', '00020101021226QRTEST6304ABCD')
                ->missing('order.qris.checkout_url')
                ->missing('order.payment_gateway'));
    }

    public function test_api_failure_cancels_order_and_returns_to_product(): void
    {
        Http::fake(['*' => Http::response(['success' => false, 'error' => 'Saldo kredit tidak cukup'], 402)]);

        $this->checkout($this->qris)
            ->assertRedirect(route('products.show', 'produk'))
            ->assertSessionHas('error', 'Pembayaran QRIS tidak dapat dibuat saat ini. Silakan coba lagi beberapa saat lagi.');

        $order = Order::sole();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('failed', $order->payment_status);
        $this->assertSame(0, Payment::count());
        Notification::assertNothingSent();
    }

    public function test_payment_page_redirects_qrisgate_order_to_invoice(): void
    {
        $this->fakeQrisgate();
        $this->checkout($this->qris);
        $order = Order::sole();

        $this->get(route('payment.show', $order->order_number))
            ->assertRedirect(route('invoice.show', $order->order_number));
    }
}
