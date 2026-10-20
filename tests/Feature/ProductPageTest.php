<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia;
use Tests\TestCase;

class ProductPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_without_short_description_page_still_renders(): void
    {
        $category = Category::create(['name' => 'Uji', 'slug' => 'uji', 'is_active' => true]);
        $product = Product::create([
            'category_id' => $category->id,
            'name' => 'Produk Tanpa Ringkasan',
            'slug' => 'produk-tanpa-ringkasan',
            'description' => '<p>Deskripsi lengkap produk.</p>',
            'is_active' => true,
        ]);
        ProductVariant::create(['product_id' => $product->id, 'name' => 'Paket', 'price' => 1000, 'is_active' => true]);

        $this->assertSame('Deskripsi lengkap produk.', $product->seo_description);
        $this->get('/product/produk-tanpa-ringkasan')->assertOk();
    }

    public function test_product_listing_follows_admin_sort_order_by_default(): void
    {
        $category = Category::create(['name' => 'Uji', 'slug' => 'uji', 'is_active' => true]);

        foreach ([['Produk A', 1, 3], ['Produk B', 2, 2], ['Produk C', 3, 1]] as [$name, $sortOrder, $daysAgo]) {
            $product = Product::create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => '<p>Deskripsi.</p>',
                'is_active' => true,
                'sort_order' => $sortOrder,
            ]);
            $product->forceFill(['created_at' => now()->subDays($daysAgo)])->save();
            ProductVariant::create(['product_id' => $product->id, 'name' => 'Paket', 'price' => 1000, 'is_active' => true]);
        }

        $this->get('/products')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.sort', 'recommended')
            ->where('products.data.0.name', 'Produk A')
            ->where('products.data.1.name', 'Produk B')
            ->where('products.data.2.name', 'Produk C'));

        $this->get('/products/category/uji')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('products.data.0.name', 'Produk A'));

        $this->get('/products?sort=newest')->assertInertia(fn (AssertableInertia $page) => $page
            ->where('products.data.0.name', 'Produk C'));
    }
}
