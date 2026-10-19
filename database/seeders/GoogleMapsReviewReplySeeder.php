<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class GoogleMapsReviewReplySeeder extends Seeder
{
    private const PRICE_PER_REPLY = 7500;

    private const PACKAGES = [5, 10, 20, 30];

    public function run(): void
    {
        $category = Category::firstOrCreate(
            ['slug' => 'layanan-bisnis'],
            [
                'name' => 'Layanan Bisnis',
                'description' => 'Jasa untuk membantu bisnis Anda tampil lebih baik di internet',
                'sort_order' => 3,
                'is_active' => true,
            ]
        );

        $product = Product::firstOrNew(['slug' => 'jasa-balas-ulasan-google-maps']);
        $product->uuid ??= (string) Str::uuid();
        $product->fill([
            'category_id' => $category->id,
            'name' => 'Jasa Balas Ulasan Google Maps',
            'short_description' => 'Balasan yang ditulis khusus untuk setiap ulasan Google Maps bisnis Anda, termasuk ulasan negatif.',
            'description' => '<p>Ulasan yang dibalas menunjukkan calon pelanggan bahwa bisnis Anda aktif dan mendengarkan. Kami menulis balasan untuk ulasan yang sudah ada di profil Google Maps bisnis Anda, disesuaikan dengan isi tiap ulasan dan gaya bahasa bisnis Anda.</p><br>'
                . '<p><strong>Cara order:</strong><br>'
                . '1. Pilih paket sesuai jumlah ulasan yang ingin dibalas.<br>'
                . '2. Tempel link Google Maps bisnis Anda di kolom <strong>Catatan</strong> saat checkout.<br>'
                . '3. Setelah pembayaran, admin menghubungi Anda via WhatsApp. Anda bisa menambahkan admin@digitalpay.web.id sebagai Manajer di Google Business Profile agar balasan kami posting langsung, atau menerima draf balasan untuk Anda posting sendiri.</p><br>'
                . '<p>Ulasan terbaru dan ulasan yang belum punya balasan dikerjakan lebih dulu.</p>',
            'features' => [
                'Balasan ditulis manual, sesuai isi tiap ulasan',
                'Bahasa Indonesia yang sopan, mengikuti gaya bisnis Anda',
                'Termasuk tanggapan untuk ulasan negatif',
                'Dikerjakan 1-3 hari kerja setelah pembayaran',
            ],
            'is_digital' => false,
            'is_popular' => false,
            'is_featured' => false,
            'is_active' => true,
            'sort_order' => 100,
        ]);
        $product->save();

        foreach (self::PACKAGES as $index => $quantity) {
            ProductVariant::updateOrCreate(
                ['product_id' => $product->id, 'name' => "Paket {$quantity} Balasan"],
                [
                    'description' => "Balasan untuk {$quantity} ulasan Google Maps bisnis Anda.",
                    'price' => $quantity * self::PRICE_PER_REPLY,
                    'original_price' => null,
                    'stock_quantity' => null,
                    'is_active' => true,
                    'auto_delivery' => false,
                    'sort_order' => $index + 1,
                ]
            );
        }
    }
}
