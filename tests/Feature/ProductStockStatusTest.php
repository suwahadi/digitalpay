<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProductStockStatusTest extends TestCase
{
    use RefreshDatabase;

    private function productWithStocks(array $stocks): Product
    {
        $category = Category::create(['name' => 'Uji', 'slug' => 'uji-' . Str::random(5), 'is_active' => true]);
        $product = Product::create([
            'uuid' => (string) Str::uuid(),
            'category_id' => $category->id,
            'name' => 'Produk Uji',
            'slug' => 'produk-uji-' . Str::random(5),
            'is_active' => true,
        ]);

        foreach ($stocks as $i => $stock) {
            ProductVariant::create([
                'product_id' => $product->id,
                'name' => "Varian {$i}",
                'price' => 1000,
                'stock_quantity' => $stock,
                'is_active' => true,
            ]);
        }

        return $product;
    }

    public function test_variants_without_stock_limit_are_unlimited_not_sold_out(): void
    {
        $this->assertSame('unlimited', $this->productWithStocks([null, null])->stock_status);
        $this->assertSame('unlimited', $this->productWithStocks([null, 0])->stock_status);
    }

    public function test_limited_stock_is_still_counted(): void
    {
        $this->assertSame('in_stock', $this->productWithStocks([8, 5])->stock_status);
        $this->assertSame('low_stock', $this->productWithStocks([3])->stock_status);
        $this->assertSame('out_of_stock', $this->productWithStocks([0, 0])->stock_status);
    }
}
