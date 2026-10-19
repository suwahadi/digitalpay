<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::where('slug', 'aplikasi-premium')->first();
        
        $products = [
            [
                'name' => 'Netflix',
                'slug' => 'netflix',
                'short_description' => 'Platform streaming film dan series terpopuler di dunia',
                'description' => 'Netflix adalah layanan streaming yang menawarkan berbagai acara TV pemenang penghargaan, film, anime, dokumenter, dan banyak lagi di ribuan perangkat yang terhubung ke Internet. Kamu bisa menonton sepuasnya, kapan pun kamu mau, tanpa satu iklan pun – semuanya dengan satu harga bulanan yang murah.',
                'image' => '/images/products/netflix.png',
                'features' => [
                    'Streaming unlimited film dan series',
                    'Kualitas HD dan Ultra HD 4K',
                    'Download untuk menonton offline',
                    'Bisa ditonton di berbagai device',
                    'Konten original Netflix eksklusif',
                    'Subtitle bahasa Indonesia tersedia'
                ],
                'is_popular' => true,
                'is_featured' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Spotify',
                'slug' => 'spotify',
                'short_description' => 'Platform streaming musik dengan jutaan lagu dan podcast',
                'description' => 'Spotify adalah platform audio digital yang memberikan akses ke jutaan lagu dan podcast dari seluruh dunia. Nikmati musik favorit tanpa iklan, download lagu untuk offline listening, dan dapatkan rekomendasi musik yang dipersonalisasi.',
                'image' => '/images/products/spotify.png',
                'features' => [
                    'Streaming musik tanpa iklan',
                    'Download musik untuk offline',
                    'Kualitas audio premium',
                    'Playlist dan rekomendasi personal',
                    'Akses ke podcast eksklusif',
                    'Cross-platform compatibility'
                ],
                'is_popular' => true,
                'is_featured' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'YouTube Premium',
                'slug' => 'youtube-premium',
                'short_description' => 'YouTube tanpa iklan dengan fitur premium lengkap',
                'description' => 'YouTube Premium memberikan pengalaman menonton YouTube tanpa gangguan iklan, akses ke YouTube Music, kemampuan download video, dan fitur background play. Nikmati konten favorit dengan pengalaman yang lebih baik.',
                'image' => '/images/products/youtube.png',
                'features' => [
                    'Menonton YouTube tanpa iklan',
                    'Background play (musik tetap putar)',
                    'Download video untuk offline',
                    'Akses YouTube Music premium',
                    'YouTube Originals content',
                    'Picture-in-picture mode'
                ],
                'is_popular' => true,
                'is_featured' => false,
                'sort_order' => 3,
            ],
            [
                'name' => 'Canva Pro',
                'slug' => 'canva-pro',
                'short_description' => 'Tool desain grafis profesional dengan template premium',
                'description' => 'Canva Pro adalah platform desain grafis yang memudahkan siapa pun untuk membuat desain yang menakjubkan. Dengan akses ke jutaan foto, elemen, dan template premium, kamu bisa membuat desain profesional dengan mudah.',
                'image' => '/images/products/canva.png',
                'features' => [
                    'Akses jutaan template premium',
                    'Background remover otomatis',
                    'Brand kit untuk konsistensi brand',
                    'Resize design dengan magic resize',
                    'Kolaborasi tim unlimited',
                    'Cloud storage 100GB'
                ],
                'is_popular' => true,
                'is_featured' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'CapCut Pro',
                'slug' => 'capcut-pro',
                'short_description' => 'Editor video mobile terbaik dengan fitur profesional',
                'description' => 'CapCut Pro adalah aplikasi edit video mobile yang powerful dengan berbagai fitur profesional. Buat video yang menakjubkan dengan effect, transition, dan tools editing yang lengkap tanpa watermark.',
                'image' => '/images/products/capcut.png',
                'features' => [
                    'Edit video tanpa watermark',
                    'Template video trending',
                    'Effect dan filter premium',
                    'Green screen (chroma key)',
                    'Audio library yang luas',
                    'Export resolusi 4K'
                ],
                'is_popular' => true,
                'is_featured' => false,
                'sort_order' => 5,
            ],
            [
                'name' => 'ChatGPT Plus',
                'slug' => 'chatgpt-plus',
                'short_description' => 'AI Assistant terdepan untuk produktivitas dan kreativitas',
                'description' => 'ChatGPT Plus memberikan akses prioritas ke GPT-4, response time yang lebih cepat, dan akses ke fitur-fitur terbaru. Tingkatkan produktivitas dengan AI assistant yang paling canggih saat ini.',
                'image' => '/images/products/chatgpt.png',
                'features' => [
                    'Akses ke GPT-4 model terbaru',
                    'Response time lebih cepat',
                    'Akses prioritas saat traffic tinggi',
                    'Fitur terbaru lebih dulu',
                    'Generate text, code, dan content',
                    'Unlimited conversation'
                ],
                'is_popular' => true,
                'is_featured' => true,
                'sort_order' => 6,
            ],
            [
                'name' => 'Disney+ Hotstar',
                'slug' => 'disney-hotstar',
                'short_description' => 'Platform streaming Disney, Marvel, Star Wars, dan konten lokal',
                'description' => 'Disney+ Hotstar menawarkan koleksi lengkap film dan series dari Disney, Pixar, Marvel, Star Wars, National Geographic, plus konten lokal Indonesia yang eksklusif.',
                'image' => '/images/products/disney.png',
                'features' => [
                    'Konten Disney, Marvel, Star Wars',
                    'Film dan series lokal Indonesia',
                    'Live streaming sports events',
                    'Kualitas streaming hingga 4K',
                    'Download untuk menonton offline',
                    'Parental control untuk anak'
                ],
                'is_popular' => false,
                'is_featured' => false,
                'sort_order' => 7,
            ],
            [
                'name' => 'Apple Music',
                'slug' => 'apple-music',
                'short_description' => 'Platform streaming musik premium dari Apple',
                'description' => 'Apple Music menawarkan akses ke lebih dari 100 juta lagu, playlist yang dikurasi ahli, radio live, dan fitur spatial audio untuk pengalaman mendengar yang immersive.',
                'image' => '/images/products/apple-music.png',
                'features' => [
                    'Streaming 100+ juta lagu',
                    'Spatial Audio dengan Dolby Atmos',
                    'Lossless dan Hi-Res audio',
                    'Radio stations live 24/7',
                    'Sinkronisasi dengan Apple ecosystem',
                    'Offline download unlimited'
                ],
                'is_popular' => false,
                'is_featured' => false,
                'sort_order' => 8,
            ],
            [
                'name' => 'Crunchyroll',
                'slug' => 'crunchyroll',
                'short_description' => 'Platform streaming anime terlengkap dengan subtitle Indonesia',
                'description' => 'Crunchyroll adalah platform streaming anime terbesar di dunia dengan koleksi lebih dari 1000+ anime series. Nikmati anime simulcast terbaru dan series klasik dengan subtitle Indonesia.',
                'image' => '/images/products/crunchyroll.png',
                'features' => [
                    'Library 1000+ anime series',
                    'Simulcast episode terbaru',
                    'Subtitle bahasa Indonesia',
                    'Kualitas streaming HD',
                    'Offline viewing untuk mobile',
                    'Ad-free streaming experience'
                ],
                'is_popular' => false,
                'is_featured' => false,
                'sort_order' => 9,
            ],
            [
                'name' => 'Vidio Premier',
                'slug' => 'vidio-premier',
                'short_description' => 'Platform streaming konten premium Indonesia',
                'description' => 'Vidio Premier menawarkan konten premium Indonesia termasuk film original, series eksklusif, live sports, dan berbagai program entertainment tanpa iklan.',
                'image' => '/images/products/vidio.png',
                'features' => [
                    'Konten original Indonesia',
                    'Live streaming olahraga',
                    'Film dan series premium',
                    'Streaming tanpa iklan',
                    'Download untuk offline',
                    'Multi-device support'
                ],
                'is_popular' => false,
                'is_featured' => false,
                'sort_order' => 10,
            ],
        ];

        foreach ($products as $productData) {
            Product::create([
                'uuid' => Str::uuid(),
                'category_id' => $category->id,
                'name' => $productData['name'],
                'slug' => $productData['slug'],
                'description' => $productData['description'],
                'short_description' => $productData['short_description'],
                'image' => $productData['image'],
                'features' => $productData['features'],
                'is_popular' => $productData['is_popular'],
                'is_featured' => $productData['is_featured'],
                'is_active' => true,
                'sort_order' => $productData['sort_order'],
                // 'meta_title' => $productData['name'] . ' Premium - DigitalPay',
                // 'meta_description' => $productData['short_description'],
            ]);
        }
    }
}
