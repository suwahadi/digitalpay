<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Slide;

class SlideSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $slides = [
            [
                'image' => 'https://ditusi.co.id/storage/slides/01JZPZK3RFWFZS0SNCF7MMKX9Y.webp',
                'title' => 'Banner',
                'link' => '#',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'image' => 'https://ditusi.co.id/storage/slides/01K4RW4S82QH48KAF1NSTQN2VJ.webp',
                'title' => 'Banner',
                'link' => '#',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'image' => 'https://ditusi.co.id/storage/slides/01K4PD2168PM1SJB22BHAVACQ9.webp',
                'title' => 'Banner',
                'link' => '#',
                'is_active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($slides as $slide) {
            Slide::create($slide);
        }
    }
}