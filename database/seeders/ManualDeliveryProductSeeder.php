<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\DigitalStock;
use Illuminate\Database\Seeder;

class ManualDeliveryProductSeeder extends Seeder
{
    public function run()
    {
        // Update existing YouTube Premium 3 Bulan variant (ID 17 dari database) 
        // yang sudah ada auto_delivery = 0
        $existingVariant = ProductVariant::find(17);
        if ($existingVariant) {
            $this->command->info('✅ YouTube Premium 3 Bulan variant already exists with manual delivery');
            
            $this->createDigitalStocks($existingVariant, 'YouTube Premium Manual');
        }

        $netflixProduct = Product::where('name', 'Netflix Premium')->first();
        if ($netflixProduct) {
            $manualVariant = ProductVariant::create([
                'product_id' => $netflixProduct->id,
                'name' => '1 Profile Manual Review',
                'description' => 'Netflix Premium dengan review manual untuk kualitas terbaik',
                'price' => 18000.00,
                'original_price' => 20000.00,
                'duration_days' => 30,
                'max_users' => 2,
                'stock_quantity' => null,
                'is_active' => true,
                'auto_delivery' => false,
                'max_downloads' => 5,
                'download_expiry_hours' => 168,
                'delivery_template' => '=== MANUAL DELIVERY ===
Produk: {product_name}
Variant: {variant_name} (Manual Review)
Order: {order_number}
Tanggal: {order_date}
Harga: {price}

=== INFORMASI AKSES ===
Username: {username}
Password: {password}
Durasi: {duration}

=== CATATAN MANUAL REVIEW ===
Akun ini telah melalui proses review manual untuk
memastikan kualitas dan keamanan terbaik.

=== SUPPORT ===
Email: admin@digitalpay.web.id
WhatsApp: +6281234567890

Terima kasih telah berbelanja!',
                'sort_order' => 4
            ]);

            $this->createDigitalStocks($manualVariant, 'Netflix Manual Review');
            $this->command->info('✅ Netflix Manual Review variant created');
        }

        $this->command->info('✅ Manual delivery products setup completed!');
    }

    private function createDigitalStocks(ProductVariant $variant, string $prefix): void
    {
        $existingCount = DigitalStock::where('product_variant_id', $variant->id)->count();
        
        if ($existingCount > 0) {
            $this->command->info("   - {$prefix} already has {$existingCount} stocks");
            return;
        }

        for ($i = 1; $i <= 3; $i++) {
            $paddedIndex = sprintf('%03d', $i);
            DigitalStock::create([
                'product_variant_id' => $variant->id,
                'stock_name' => "{$prefix} #{$paddedIndex}",
                'stock_data' => [
                    'username' => "manual_{$variant->id}_{$paddedIndex}@example.com",
                    'password' => "ManualPass{$variant->id}{$paddedIndex}!",
                    'type' => 'Premium Account (Manual Review)',
                    'quality' => $variant->product->name === 'Netflix Premium' ? '4K Ultra HD' : 'Premium',
                    'duration' => '30 days',
                    'max_screens' => $variant->name === '1 Profile Manual Review' ? '2' : '1',
                ],
                'status' => 'available',
                'added_at' => now(),
                'expires_at' => now()->addMonths(3),
                'admin_notes' => 'Manual delivery stock - requires admin approval',
            ]);
        }

        $this->command->info("   - Created 3 stocks for {$prefix}");
    }
}
