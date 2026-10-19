<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        Category::create([
            'name' => 'Aplikasi Premium',
            'slug' => 'aplikasi-premium',
            'description' => 'Berbagai aplikasi premium untuk kebutuhan entertainment, produktivitas, dan pembelajaran',
            'icon' => '/icons/premium-apps.svg',
            'sort_order' => 1,
            'is_active' => true,
        ]);
    }
}
