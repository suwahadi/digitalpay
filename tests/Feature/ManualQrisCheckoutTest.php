<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class ManualQrisCheckoutTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    private PaymentMethod $qris;

    private PaymentMethod $gopay;

    protected function setUp(): void
    {
        parent::setUp();

        config(['services.manual_qris.enabled' => true]);
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

    public function test_only_qris_is_offered_and_other_methods_stay_active(): void
    {
        $this->assertSame(['qris'], PaymentMethod::available()->pluck('code')->all());
        $this->assertTrue($this->gopay->fresh()->is_active);

        $this->checkout($this->gopay)
            ->assertRedirect(route('products.show', 'produk'))
            ->assertSessionHas('error', 'Metode pembayaran ini sedang tidak tersedia.');
        $this->assertSame(0, Order::count());
    }

    public function test_qris_order_gets_fee_and_unique_code_and_goes_to_invoice_without_email(): void
    {
        $this->checkout($this->qris);

        $order = Order::sole();
        $this->assertTrue($order->isManualQris());
        $this->assertSame(700, (int) $order->fee_amount);
        $this->assertGreaterThanOrEqual(1, $order->unique_code);
        $this->assertLessThanOrEqual(999, $order->unique_code);
        $this->assertSame(100700 + $order->unique_code, (int) $order->total_amount);
        $this->assertSame('unpaid', $order->payment_status);
        Notification::assertNothingSent();
    }

    public function test_saya_sudah_bayar_awaits_admin_and_stops_expiry(): void
    {
        $response = $this->checkout($this->qris);
        $order = Order::sole();
        $response->assertRedirect(route('invoice.show', $order->order_number));

        $this->post(route('invoice.manual-paid', $order->order_number))
            ->assertRedirect(route('invoice.show', $order->order_number));

        $order->refresh();
        $this->assertSame('pending', $order->status);
        $this->assertSame('pending', $order->payment_status);
        $this->assertNull($order->expires_at);

        $this->travel(2)->days();
        $this->artisan('orders:expire');
        $this->assertSame('pending', $order->fresh()->status);

        $order->update(['status' => 'paid', 'payment_status' => 'paid']);
        Notification::assertNothingSent();
    }

    public function test_unique_codes_differ_for_same_amount(): void
    {
        $this->checkout($this->qris);
        $this->checkout($this->qris);

        $codes = Order::pluck('unique_code');
        $this->assertCount(2, $codes->unique());
    }
}
