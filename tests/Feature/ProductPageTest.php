<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
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
}
