<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DigitalDelivery;
use App\Models\DigitalStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Notifications\AdminPaymentReceivedNotification;
use App\Notifications\DigitalDeliveryNotification;
use App\Notifications\ManualDeliveryCompletedNotification;
use App\Services\DigitalDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Tests\TestCase;

class DeliveryFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Notification::fake();
    }

    private function paidOrderFor(string $deliveryType, ?int $stockQuantity = null, int $digitalStocks = 0): Order
    {
        $category = Category::create(['name' => 'Uji', 'slug' => 'uji-'.Str::random(4), 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Produk', 'slug' => 'produk-'.Str::random(4), 'is_active' => true]);
        $variant = ProductVariant::create([
            'product_id' => $product->id, 'name' => 'Paket', 'price' => 10000, 'is_active' => true,
            'delivery_type' => $deliveryType, 'stock_quantity' => $stockQuantity,
        ]);

        for ($i = 0; $i < $digitalStocks; $i++) {
            DigitalStock::create(['product_variant_id' => $variant->id, 'stock_name' => "Akun {$i}", 'stock_data' => ['username' => "user{$i}", 'password' => 'rahasia'], 'status' => 'available']);
        }

        $order = Order::forceCreate([
            'uuid' => (string) Str::uuid(),
            'order_number' => 'DIG-'.Str::upper(Str::random(7)),
            'customer_name' => 'Budi',
            'customer_email' => 'budi@example.com',
            'payment_gateway' => Order::QRISGATE,
            'subtotal' => 10000,
            'total_amount' => 10000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id, 'product_variant_id' => $variant->id,
            'product_name' => 'Produk', 'variant_name' => 'Paket', 'price' => 10000, 'quantity' => 1, 'total' => 10000,
        ]);

        $order->markAsPaidFromGateway(now());

        return $order->fresh();
    }

    public function test_auto_variant_with_stock_is_delivered_automatically(): void
    {
        $order = $this->paidOrderFor('auto', null, 1);

        $this->assertFalse($order->needs_manual_delivery);
        $this->assertSame('delivered', $order->orderItems->first()->delivery_status);
        $this->assertStringContainsString('user0', DigitalDelivery::sole()->delivery_content);
        Notification::assertSentTimes(DigitalDeliveryNotification::class, 1);
    }

    public function test_auto_variant_without_stock_falls_back_to_manual_queue(): void
    {
        $order = $this->paidOrderFor('auto');

        $this->assertTrue($order->needs_manual_delivery);
        $this->assertSame(0, DigitalDelivery::count());
        Notification::assertNotSentTo($order->user ?? new \App\Models\GuestUser('budi@example.com'), DigitalDeliveryNotification::class);
        Notification::assertSentOnDemand(AdminPaymentReceivedNotification::class, function ($notification) {
            return $notification->orderData()['needs_manual_delivery'] === true;
        });
    }

    public function test_manual_variant_is_queued_and_stock_decremented(): void
    {
        $order = $this->paidOrderFor('manual', 5);

        $this->assertTrue($order->needs_manual_delivery);
        $this->assertSame(4, $order->orderItems->first()->productVariant->stock_quantity);
    }

    public function test_admin_manual_delivery_completes_order_and_emails_buyer(): void
    {
        $order = $this->paidOrderFor('manual');
        $item = $order->orderItems->first();

        app(DigitalDeliveryService::class)->deliverManually($item, "Email: akun@contoh.com\nPassword: rahasia", null, 'dikirim admin');

        $delivery = DigitalDelivery::sole();
        $this->assertSame(DigitalDelivery::STATUS_COMPLETED, $delivery->delivery_status);
        $this->assertNull($delivery->digital_stock_id);
        $this->assertSame('delivered', $item->fresh()->delivery_status);
        $this->assertFalse($order->fresh()->needs_manual_delivery);
        $this->assertNotNull($order->fresh()->manual_processed_at);
        Notification::assertSentTimes(ManualDeliveryCompletedNotification::class, 1);
    }
}
