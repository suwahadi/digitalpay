<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Voucher;
use App\Services\MidtransService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CheckoutVoucherTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    private PaymentMethod $method;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mock(MidtransService::class, function ($mock) {
            $mock->shouldReceive('createTransaction')->andReturn([
                'success' => true,
                'data' => ['transaction_id' => 'qa-trx'],
                'payment_instructions' => [],
                'payment_data' => [],
            ]);
        });

        $category = Category::create(['name' => 'Uji', 'slug' => 'uji', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id, 'name' => 'Produk', 'slug' => 'produk', 'is_active' => true,
        ]);
        $this->variant = ProductVariant::create([
            'product_id' => $product->id, 'name' => 'Paket', 'price' => 100000, 'is_active' => true,
        ]);
        $this->method = PaymentMethod::create([
            'code' => 'qris', 'name' => 'QRIS', 'type' => 'qris', 'provider' => 'midtrans',
            'fee_type' => 'percentage', 'fee_amount' => 1, 'min_amount' => 0, 'is_active' => true, 'sort_order' => 1,
        ]);
    }

    private function checkout(?string $voucherCode = null): \Illuminate\Testing\TestResponse
    {
        return $this->post('/checkout/store', array_filter([
            'product_id' => $this->variant->product_id,
            'variant_id' => $this->variant->id,
            'customer_name' => 'Pembeli',
            'customer_email' => 'pembeli@digitalpay.web.id',
            'customer_phone' => '081234567890',
            'payment_method_id' => $this->method->id,
            'voucher_code' => $voucherCode,
        ]));
    }

    public function test_totals_are_whole_rupiah_and_discount_plus_fee_are_applied(): void
    {
        Voucher::create([
            'code' => 'HEMAT10', 'name' => 'Hemat 10%', 'discount_type' => 'percentage',
            'discount_value' => 10, 'is_active' => true,
        ]);

        $this->checkout('HEMAT10');

        $order = Order::latest('id')->first();
        $this->assertSame(100000, (int) $order->subtotal);
        $this->assertSame(10000, (int) $order->discount_amount);
        $this->assertSame(900, (int) $order->fee_amount);
        $this->assertSame(90900, (int) $order->total_amount);
        foreach (['subtotal', 'discount_amount', 'fee_amount', 'total_amount'] as $c) {
            $this->assertSame((float) (int) $order->$c, (float) $order->$c, "$c harus rupiah bulat");
            $this->assertGreaterThanOrEqual(0, (int) $order->$c);
        }
    }

    public function test_voucher_usage_count_increments_on_order(): void
    {
        $voucher = Voucher::create([
            'code' => 'SEKALI', 'name' => 'Sekali', 'discount_type' => 'fixed',
            'discount_value' => 5000, 'is_active' => true, 'usage_limit' => 1, 'used_count' => 0,
        ]);

        $this->checkout('SEKALI');

        $this->assertSame(1, $voucher->fresh()->used_count);
    }

    public function test_exhausted_voucher_gives_no_discount_and_is_not_recorded(): void
    {
        Voucher::create([
            'code' => 'HABIS', 'name' => 'Habis', 'discount_type' => 'fixed',
            'discount_value' => 5000, 'is_active' => true, 'usage_limit' => 1, 'used_count' => 1,
        ]);

        $this->checkout('HABIS');

        $order = Order::latest('id')->first();
        $this->assertSame(0, (int) $order->discount_amount);
        $this->assertNull($order->voucher_code);
    }
}
