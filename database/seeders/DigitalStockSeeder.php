<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DigitalStock;
use App\Models\ProductVariant;
use App\Models\User;

class DigitalStockSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::first() ?? User::create([
            'uuid' => \Str::uuid(),
            'name' => 'Admin',
            'username' => 'admin',
            'email' => 'admin@digitalpay.web.id',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => 'active'
        ]);

        $variant = ProductVariant::first();
        
        if (!$variant) {
            $this->command->info('No product variants found. Please run ProductSeeder first.');
            return;
        }

        $netflixStocks = [
            [
                'stock_name' => 'Netflix Premium #001',
                'stock_data' => [
                    'type' => 'Premium Account',
                    'username' => 'netflix_premium_001@email.com',
                    'password' => 'SecurePass001!',
                    'duration' => '30 days',
                    'max_screens' => '4',
                    'quality' => '4K Ultra HD'
                ]
            ],
            [
                'stock_name' => 'Netflix Premium #002',
                'stock_data' => [
                    'type' => 'Premium Account',
                    'username' => 'netflix_premium_002@email.com',
                    'password' => 'SecurePass002!',
                    'duration' => '30 days',
                    'max_screens' => '4',
                    'quality' => '4K Ultra HD'
                ]
            ],
            [
                'stock_name' => 'Netflix Premium #003',
                'stock_data' => [
                    'type' => 'Premium Account',
                    'username' => 'netflix_premium_003@email.com',
                    'password' => 'SecurePass003!',
                    'duration' => '30 days',
                    'max_screens' => '4',
                    'quality' => '4K Ultra HD'
                ]
            ],
            [
                'stock_name' => 'Netflix Premium #004',
                'stock_data' => [
                    'type' => 'Premium Account',
                    'username' => 'netflix_premium_004@email.com',
                    'password' => 'SecurePass004!',
                    'duration' => '30 days',
                    'max_screens' => '4',
                    'quality' => '4K Ultra HD'
                ]
            ],
            [
                'stock_name' => 'Netflix Premium #005',
                'stock_data' => [
                    'type' => 'Premium Account',
                    'username' => 'netflix_premium_005@email.com',
                    'password' => 'SecurePass005!',
                    'duration' => '30 days',
                    'max_screens' => '4',
                    'quality' => '4K Ultra HD'
                ]
            ]
        ];

        foreach ($netflixStocks as $stock) {
            DigitalStock::create([
                'product_variant_id' => $variant->id,
                'stock_name' => $stock['stock_name'],
                'stock_data' => $stock['stock_data'],
                'status' => 'available',
                'added_at' => now(),
                'added_by_user_id' => $admin->id,
                'expires_at' => now()->addDays(30)
            ]);
        }

        $this->command->info('Digital stock seeder completed successfully!');
        $this->command->info('Created ' . count($netflixStocks) . ' sample stocks for variant: ' . $variant->name);
    }
}
