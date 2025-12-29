<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Transportasi',
                'icon' => 'car',
                'description' => 'Biaya transportasi seperti taksi, ojol, bensin',
                'sort_order' => 1,
                'is_active' => true,
            ],
            [
                'name' => 'Makan',
                'icon' => 'utensils',
                'description' => 'Biaya makan dan minum',
                'sort_order' => 2,
                'is_active' => true,
            ],
            [
                'name' => 'Parkir',
                'icon' => 'parking',
                'description' => 'Biaya parkir kendaraan',
                'sort_order' => 3,
                'is_active' => true,
            ],
            [
                'name' => 'Tol',
                'icon' => 'road',
                'description' => 'Biaya tol',
                'sort_order' => 4,
                'is_active' => true,
            ],
            [
                'name' => 'Akomodasi',
                'icon' => 'hotel',
                'description' => 'Biaya hotel dan penginapan',
                'sort_order' => 5,
                'is_active' => true,
            ],
            [
                'name' => 'Supplies',
                'icon' => 'box',
                'description' => 'Pembelian ATK dan perlengkapan',
                'sort_order' => 6,
                'is_active' => true,
            ],
            [
                'name' => 'Komunikasi',
                'icon' => 'phone',
                'description' => 'Pulsa, internet, dan komunikasi',
                'sort_order' => 7,
                'is_active' => true,
            ],
            [
                'name' => 'Lainnya',
                'icon' => 'ellipsis-h',
                'description' => 'Biaya lain-lain',
                'sort_order' => 99,
                'is_active' => true,
            ],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name']],
                $category
            );
        }
    }
}
