<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\ProductVariant;

class ProductVariantSeeder extends Seeder
{
    public function run(): void
    {
        $this->createNetflixVariants();
        $this->createSpotifyVariants();
        $this->createYouTubeVariants();
        $this->createCanvaVariants();
        $this->createCapCutVariants();
        $this->createChatGPTVariants();
        $this->createDisneyVariants();
        $this->createAppleMusicVariants();
        $this->createCrunchyrollVariants();
        $this->createVidioVariants();
    }

    private function createNetflixVariants()
    {
        $netflix = Product::where('slug', 'netflix')->first();
        
        ProductVariant::create([
            'product_id' => $netflix->id,
            'name' => '1 Profile 2 User',
            'description' => 'Digunakan oleh 2 orang (kamu dan 1 orang lain), bisa kena limit',
            'price' => 13000,
            'original_price' => 15000,
            'duration_days' => 30,
            // 'max_users' => 2,
            'stock_quantity' => 23,
            'sort_order' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $netflix->id,
            'name' => '1 Profile 1 User',
            'description' => 'Digunakan oleh kamu saja, tidak ada gangguan limit',
            'price' => 23500,
            'original_price' => 25000,
            'duration_days' => 30,
            // 'max_users' => 1,
            'stock_quantity' => 9,
            'sort_order' => 2,
        ]);

        ProductVariant::create([
            'product_id' => $netflix->id,
            'name' => '1 Bulan Private',
            'description' => 'Akun Netflix private khusus untuk kamu, full access',
            'price' => 115000,
            'original_price' => 120000,
            'duration_days' => 30,
            // 'max_users' => 1,
            'stock_quantity' => null,
            'sort_order' => 3,
        ]);
    }

    private function createSpotifyVariants()
    {
        $spotify = Product::where('slug', 'spotify')->first();
        
        ProductVariant::create([
            'product_id' => $spotify->id,
            'name' => 'Spotify Premium Individual',
            'description' => 'Akun Spotify premium untuk 1 pengguna',
            'price' => 25000,
            'original_price' => 30000,
            'duration_days' => 30,
            // 'max_users' => 1,
            'stock_quantity' => 15,
            'sort_order' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $spotify->id,
            'name' => 'Spotify Premium Family',
            'description' => 'Akses untuk 6 akun keluarga terpisah',
            'price' => 45000,
            'original_price' => 50000,
            'duration_days' => 30,
            // 'max_users' => 6,
            'stock_quantity' => 8,
            'sort_order' => 2,
        ]);
    }

    private function createYouTubeVariants()
    {
        $youtube = Product::where('slug', 'youtube-premium')->first();
        
        ProductVariant::create([
            'product_id' => $youtube->id,
            'name' => 'YouTube Premium Individual',
            'description' => 'YouTube Premium + YouTube Music untuk 1 user',
            'price' => 35000,
            'original_price' => 40000,
            'duration_days' => 30,
            // 'max_users' => 1,
            'stock_quantity' => 12,
            'sort_order' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $youtube->id,
            'name' => 'YouTube Premium Family',
            'description' => 'YouTube Premium untuk keluarga (6 anggota)',
            'price' => 65000,
            'original_price' => 70000,
            'duration_days' => 30,
            // 'max_users' => 6,
            'stock_quantity' => 5,
            'sort_order' => 2,
        ]);
    }

    private function createCanvaVariants()
    {
        $canva = Product::where('slug', 'canva-pro')->first();
        
        ProductVariant::create([
            'product_id' => $canva->id,
            'name' => 'Canva Pro 1 Bulan',
            'description' => 'Akses penuh ke semua fitur Canva Pro selama 1 bulan',
            'price' => 45000,
            'original_price' => 50000,
            'duration_days' => 30,
            // 'max_users' => 1,
            'stock_quantity' => 20,
            'sort_order' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $canva->id,
            'name' => 'Canva Pro 3 Bulan',
            'description' => 'Akses Canva Pro untuk 3 bulan, lebih hemat',
            'price' => 120000,
            'original_price' => 150000,
            'duration_days' => 90,
            // 'max_users' => 1,
            'stock_quantity' => 10,
            'sort_order' => 2,
        ]);
    }

    private function createCapCutVariants()
    {
        $capcut = Product::where('slug', 'capcut-pro')->first();
        
        ProductVariant::create([
            'product_id' => $capcut->id,
            'name' => 'CapCut Pro 1 Bulan',
            'description' => 'Edit video premium tanpa watermark selama 1 bulan',
            'price' => 30000,
            'original_price' => 35000,
            'duration_days' => 30,
            // 'max_users' => 1,
            'stock_quantity' => 7,
            'sort_order' => 1,
        ]);

        ProductVariant::create([
            'product_id' => $capcut->id,
            'name' => 'CapCut Pro 6 Bulan',
            'description' => 'CapCut Pro untuk 6 bulan, sangat hemat',
            'price' => 150000,
            'original_price' => 180000,
            'duration_days' => 180,
            // 'max_users' => 1,
            'stock_quantity' => 3,
            'sort_order' => 2,
        ]);
    }

    private function createChatGPTVariants()
    {
        $chatgpt = Product::where('slug', 'chatgpt-plus')->first();
        
        ProductVariant::create([
            'product_id' => $chatgpt->id,
            'name' => 'ChatGPT Plus 1 Bulan',
            'description' => 'Akses GPT-4 dan fitur premium selama 1 bulan',
            'price' => 150000,
            'original_price' => 160000,
            'duration_days' => 30,
            // 'max_users' => 1,
            'stock_quantity' => 7,
            'sort_order' => 1,
        ]);
    }

    private function createDisneyVariants()
    {
        $disney = Product::where('slug', 'disney-hotstar')->first();
        
        ProductVariant::create([
            'product_id' => $disney->id,
            'name' => 'Disney+ Hotstar Premium',
            'description' => 'Akses penuh Disney+ Hotstar dengan konten premium',
            'price' => 40000,
            'original_price' => 45000,
            'duration_days' => 30,
            // 'max_users' => 1,
            'stock_quantity' => 15,
            'sort_order' => 1,
        ]);
    }

    private function createAppleMusicVariants()
    {
        $apple = Product::where('slug', 'apple-music')->first();
        
        ProductVariant::create([
            'product_id' => $apple->id,
            'name' => 'Apple Music Individual',
            'description' => 'Apple Music premium dengan Spatial Audio',
            'price' => 50000,
            'original_price' => 55000,
            'duration_days' => 30,
            // 'max_users' => 1,
            'stock_quantity' => 10,
            'sort_order' => 1,
        ]);
    }

    private function createCrunchyrollVariants()
    {
        $crunchyroll = Product::where('slug', 'crunchyroll')->first();
        
        ProductVariant::create([
            'product_id' => $crunchyroll->id,
            'name' => 'Crunchyroll Premium',
            'description' => 'Streaming anime tanpa iklan dengan subtitle Indonesia',
            'price' => 35000,
            'original_price' => 40000,
            'duration_days' => 30,
            // 'max_users' => 1,
            'stock_quantity' => 8,
            'sort_order' => 1,
        ]);
    }

    private function createVidioVariants()
    {
        $vidio = Product::where('slug', 'vidio-premier')->first();
        
        ProductVariant::create([
            'product_id' => $vidio->id,
            'name' => 'Vidio Premier 1 Bulan',
            'description' => 'Akses konten premium dan live sports Indonesia',
            'price' => 25000,
            'original_price' => 30000,
            'duration_days' => 30,
            // 'max_users' => 1,
            'stock_quantity' => 8,
            'sort_order' => 1,
        ]);
    }
}
