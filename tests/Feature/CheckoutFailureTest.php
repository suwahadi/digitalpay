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

class CheckoutFailureTest extends TestCase
{
    use RefreshDatabase;

    private ProductVariant $variant;

    private PaymentMethod $method;

    protected function setUp(): void
    {
        parent::setUp();

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

    private function checkout(array $overrides = []): \Illuminate\Testing\TestResponse
    {
        return $this->post('/checkout/store', array_merge([
            'product_id' => $this->variant->product_id,
            'variant_id' => $this->variant->id,
            'customer_name' => 'Pembeli',
            'customer_email' => 'pembeli@digitalpay.web.id',
            'customer_phone' => '081234567890',
            'payment_method_id' => $this->method->id,
        ], $overrides));
    }

    public function test_gateway_failure_returns_to_product_with_message_and_cancels_quietly(): void
    {
        $this->mock(MidtransService::class)->shouldReceive('createTransaction')
            ->andReturn(['success' => false, 'message' => 'Payment creation failed']);

        $this->checkout()
            ->assertRedirect(route('products.show', 'produk'))
            ->assertSessionHas('error', 'Pembayaran tidak dapat dibuat saat ini. Silakan coba lagi beberapa saat lagi.');

        $order = Order::sole();
        $this->assertSame('cancelled', $order->status);
        $this->assertSame('failed', $order->payment_status);
    }

    public function test_cancelling_a_failed_checkout_sends_no_cancellation_email(): void
    {
        $this->mock(MidtransService::class)->shouldReceive('createTransaction')
            ->andReturn(['success' => false, 'message' => 'Payment creation failed']);
        Notification::fake();
        $user = \App\Models\User::factory()->create();

        $this->actingAs($user)->checkout()->assertRedirect(route('products.show', 'produk'));

        Notification::assertNotSentTo($user, \App\Notifications\OrderCancellationNotification::class);
    }

    public function test_validation_error_returns_to_product_instead_of_home(): void
    {
        $this->variant->update(['is_active' => false]);

        $this->checkout()
            ->assertRedirect(route('products.show', 'produk'))
            ->assertSessionHas('error');

        $this->assertSame(0, Order::count());
    }
}
