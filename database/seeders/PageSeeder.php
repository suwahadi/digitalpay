<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Page;

class PageSeeder extends Seeder
{
    private const LOREM = '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Donec in ex ultricies, consequat tortor eget, imperdiet nunc. Nam eros lectus, tincidunt eu lacus at, posuere dictum nisi. Integer posuere, sapien vel dignissim aliquam, ipsum lorem tincidunt magna, a facilisis justo nisl quis libero.</p>'
        . '<p>Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae. Suspendisse potenti. Curabitur vel augue at mauris volutpat ullamcorper non sit amet sapien.</p>'
        . '<p>Phasellus at dui vel tortor tristique egestas a nec diam. Mauris vehicula tellus aliquet mi congue, at vehicula lorem faucibus. Nunc dictum nibh vitae turpis placerat, eget feugiat purus convallis. Aliquam erat volutpat. Sed sit amet orci in nisl efficitur porta.</p>';

    public function run(): void
    {
        $pages = [
            'about' => 'Tentang Kami',
            'contact' => 'Kontak',
            'help' => 'Pusat Bantuan',
            'terms' => 'Syarat & Ketentuan',
            'privacy' => 'Kebijakan Privasi',
        ];

        foreach ($pages as $slug => $title) {
            Page::updateOrCreate(
                ['slug' => $slug],
                ['title' => $title, 'content' => self::LOREM, 'is_published' => true]
            );
        }
    }
}
