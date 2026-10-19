<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            UserSeeder::class,
            CategorySeeder::class,
            PaymentMethodSeeder::class,
            SettingSeeder::class,
            ProductSeeder::class,
            ProductVariantSeeder::class,
            ArticleSeeder::class,
            FaqSeeder::class,
            PageSeeder::class,
            SlideSeeder::class,
            UpdateProductsSeeder::class,
            DigitalStockSeeder::class,
            GoogleMapsReviewReplySeeder::class,
        ]);
    }
}
