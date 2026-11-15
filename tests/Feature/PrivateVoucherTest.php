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
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class PrivateVoucherTest extends TestCase
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
            'product_id' => $product->id, 'name' => 'Paket', 'price' => 100000, 'is_active' => true, 'delivery_type' => 'manual',
        ]);
        $this->method = PaymentMethod::create([
            'code' => 'qris', 'name' => 'QRIS', 'type' => 'qris', 'provider' => 'midtrans',
            'fee_type' => 'fixed', 'fee_amount' => 0, 'min_amount' => 0, 'is_active' => true, 'sort_order' => 1,
        ]);

        Voucher::create([
            'code' => 'PROMOUMUM', 'name' => 'Promo Umum', 'discount_type' => 'fixed',
            'discount_value' => 5000, 'is_active' => true,
        ]);
        Voucher::create([
            'code' => 'KHUSUSVIP', 'name' => 'Khusus VIP', 'discount_type' => 'fixed',
            'discount_value' => 12000, 'is_active' => true, 'is_public' => false,
        ]);
    }

    public function test_existing_vouchers_default_to_public(): void
    {
        $this->assertTrue(Voucher::where('code', 'PROMOUMUM')->first()->is_public);
    }

    public function test_checkout_page_lists_only_public_vouchers(): void
    {
        $this->post('/checkout', ['product_id' => $this->variant->product_id, 'variant_id' => $this->variant->id])
            ->assertInertia(fn (AssertableInertia $page) => $page
                ->component('Checkout/Create', false)
                ->has('availableVouchers', 1)
                ->where('availableVouchers.0.code', 'PROMOUMUM'));
    }

    public function test_private_voucher_code_is_validated_by_server(): void
    {
        $this->postJson('/checkout/voucher', ['code' => 'KHUSUSVIP'])
            ->assertOk()
            ->assertJsonPath('voucher.code', 'KHUSUSVIP')
            ->assertJsonPath('voucher.discount_value', 12000);
    }

    public function test_unknown_inactive_or_used_up_voucher_is_rejected(): void
    {
        Voucher::create(['code' => 'MATI', 'discount_type' => 'fixed', 'discount_value' => 1000, 'is_active' => false]);
        Voucher::create([
            'code' => 'HABIS', 'discount_type' => 'fixed', 'discount_value' => 1000, 'is_active' => true,
            'is_public' => false, 'usage_limit' => 1, 'used_count' => 1,
        ]);

        foreach (['NGASAL', 'MATI', 'HABIS'] as $code) {
            $this->postJson('/checkout/voucher', ['code' => $code])
                ->assertStatus(422)
                ->assertJsonPath('message', 'Voucher tidak valid atau sudah tidak berlaku.');
        }
    }

    public function test_voucher_check_is_rate_limited(): void
    {
        for ($i = 0; $i < 10; $i++) {
            $this->postJson('/checkout/voucher', ['code' => 'COBA'.$i]);
        }

        $this->postJson('/checkout/voucher', ['code' => 'KHUSUSVIP'])->assertStatus(429);
    }

    public function test_private_voucher_discount_applies_on_checkout(): void
    {
        $this->post('/checkout/store', [
            'product_id' => $this->variant->product_id,
            'variant_id' => $this->variant->id,
            'customer_name' => 'Pembeli',
            'customer_email' => 'pembeli@digitalpay.web.id',
            'customer_phone' => '081234567890',
            'payment_method_id' => $this->method->id,
            'voucher_code' => 'KHUSUSVIP',
        ]);

        $order = Order::sole();
        $this->assertSame(12000, (int) $order->discount_amount);
        $this->assertSame(88000, (int) $order->total_amount);
        $this->assertSame(1, Voucher::where('code', 'KHUSUSVIP')->value('used_count'));
    }
}
