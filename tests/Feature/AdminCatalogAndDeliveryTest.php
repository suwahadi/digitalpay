<?php

namespace Tests\Feature;

use App\Filament\Resources\OrderResource\Pages\ListOrders;
use App\Filament\Resources\OrderResource\Pages\ViewOrder;
use App\Filament\Resources\ProductResource\Pages\EditProduct;
use App\Filament\Resources\ProductResource\RelationManagers\VariantsRelationManager;
use App\Models\Category;
use App\Models\DigitalDelivery;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use App\Notifications\ManualDeliveryCompletedNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class AdminCatalogAndDeliveryTest extends TestCase
{
    use RefreshDatabase;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $category = Category::create(['name' => 'Streaming', 'slug' => 'streaming', 'is_active' => true]);
        $this->product = Product::create(['category_id' => $category->id, 'name' => 'Netflix', 'slug' => 'netflix', 'is_active' => true]);
    }

    private function paidManualOrder(): Order
    {
        $variant = ProductVariant::create([
            'product_id' => $this->product->id, 'name' => '1 Bulan', 'price' => 30000,
            'is_active' => true, 'delivery_type' => ProductVariant::DELIVERY_MANUAL,
        ]);

        $order = Order::forceCreate([
            'uuid' => (string) Str::uuid(),
            'order_number' => 'DIG-ADM'.Str::upper(Str::random(4)),
            'customer_name' => 'Budi',
            'customer_email' => 'budi@example.com',
            'payment_gateway' => Order::QRISGATE,
            'subtotal' => 30000,
            'total_amount' => 30000,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);
        OrderItem::create([
            'order_id' => $order->id, 'product_id' => $this->product->id, 'product_variant_id' => $variant->id,
            'product_name' => 'Netflix', 'variant_name' => '1 Bulan', 'price' => 30000, 'quantity' => 1, 'total' => 30000,
        ]);
        $order->markAsPaidFromGateway(now());

        return $order->fresh();
    }

    public function test_variant_is_created_inside_product_page(): void
    {
        Livewire::test(VariantsRelationManager::class, ['ownerRecord' => $this->product, 'pageClass' => EditProduct::class])
            ->callTableAction('create', data: [
                'name' => 'Paket Keluarga',
                'price' => 45000,
                'delivery_type' => ProductVariant::DELIVERY_MANUAL,
                'stock_quantity' => 3,
                'is_active' => true,
            ])
            ->assertHasNoTableActionErrors();

        $variant = ProductVariant::sole();
        $this->assertSame($this->product->id, $variant->product_id);
        $this->assertSame(ProductVariant::DELIVERY_MANUAL, $variant->delivery_type);
        $this->assertSame(3, $variant->stock_quantity);
    }

    public function test_paid_manual_order_shows_in_needs_delivery_filter(): void
    {
        $order = $this->paidManualOrder();

        Livewire::test(ListOrders::class)
            ->filterTable('needs_delivery')
            ->assertCanSeeTableRecords([$order])
            ->assertTableActionVisible('deliver_manual', $order);
    }

    public function test_admin_sends_manual_delivery_from_order_page(): void
    {
        $order = $this->paidManualOrder();

        Livewire::test(ViewOrder::class, ['record' => $order->getRouteKey()])
            ->assertActionVisible('deliver_manual')
            ->callAction('deliver_manual', data: ['content' => "Email: akun@contoh.com\nPassword: rahasia"])
            ->assertHasNoActionErrors();

        $this->assertSame('delivered', $order->orderItems()->first()->delivery_status);
        $this->assertFalse($order->fresh()->needs_manual_delivery);
        $this->assertStringContainsString('akun@contoh.com', DigitalDelivery::sole()->delivery_content);
        Notification::assertSentTimes(ManualDeliveryCompletedNotification::class, 1);

        Livewire::test(ListOrders::class)
            ->filterTable('needs_delivery')
            ->assertCanNotSeeTableRecords([$order]);
    }

    public function test_removed_admin_pages_are_gone(): void
    {
        foreach (['/admin/product-variants', '/admin/order-items', '/admin/digital-deliveries', '/admin/orders/create', '/admin/payments/create'] as $url) {
            $this->get($url)->assertNotFound();
        }

        $this->get(\App\Filament\Resources\ProductResource::getUrl('edit', ['record' => $this->product]))->assertOk()->assertSee('Varian');
        $this->get('/admin/digital-stocks')->assertOk();
    }
}
