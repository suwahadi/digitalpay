<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\DigitalStock;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\DigitalDeliveryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class DigitalDeliveryStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_one_stock_is_never_delivered_to_two_order_items(): void
    {
        $category = Category::create(['name' => 'Uji', 'slug' => 'uji', 'is_active' => true]);
        $product = Product::create(['category_id' => $category->id, 'name' => 'Netflix', 'slug' => 'netflix', 'is_digital' => true, 'is_active' => true]);
        $variant = ProductVariant::create([
            'product_id' => $product->id, 'name' => 'Akun', 'price' => 50000, 'is_active' => true,
            'auto_delivery' => true, 'max_downloads' => 3, 'download_expiry_hours' => 168,
        ]);

        DigitalStock::create([
            'product_variant_id' => $variant->id, 'stock_name' => 'Akun 1',
            'stock_data' => ['username' => 'a@a.com', 'password' => 'rahasia'],
            'status' => 'available', 'added_at' => now(),
        ]);

        $order = Order::forceCreate([
            'uuid' => (string) Str::uuid(), 'order_number' => 'DIG-DLV1', 'customer_name' => 'A',
            'customer_email' => 'a@digitalpay.web.id', 'subtotal' => 50000, 'total_amount' => 50000,
            'status' => 'paid', 'payment_status' => 'paid', 'expires_at' => now()->addDay(),
        ]);
        $makeItem = fn () => OrderItem::create([
            'order_id' => $order->id, 'product_id' => $product->id, 'product_variant_id' => $variant->id,
            'product_name' => $product->name, 'variant_name' => $variant->name,
            'quantity' => 1, 'price' => 50000, 'total' => 50000,
        ]);

        $service = app(DigitalDeliveryService::class);

        $service->processOrderItemDelivery($makeItem());

        $threw = false;
        try {
            $service->processOrderItemDelivery($makeItem());
        } catch (\Throwable $e) {
            $threw = true;
        }

        $this->assertTrue($threw, 'Order item kedua seharusnya gagal karena stok habis');
        $this->assertSame(1, DigitalStock::where('status', 'delivered')->count());
        $this->assertSame(1, \App\Models\DigitalDelivery::count());
        $this->assertSame(0, DigitalStock::where('status', 'available')->count());
    }
}
