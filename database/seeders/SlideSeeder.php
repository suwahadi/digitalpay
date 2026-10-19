<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Slide;

class SlideSeeder extends Seeder
{
    public function run(): void
    {
        Slide::query()->delete();

        Slide::create([
            'image' => '/images/placeholder.png',
            'title' => 'DigitalPay',
            'link' => '#',
            'is_active' => true,
            'sort_order' => 1,
        ]);
    }
}
