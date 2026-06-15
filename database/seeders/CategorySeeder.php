<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Electronics', 'slug' => 'electronics', 'description' => 'Electronic devices and gadgets'],
            ['name' => 'Clothing', 'slug' => 'clothing', 'description' => 'Fashion and apparel'],
            ['name' => 'Home & Garden', 'slug' => 'home-garden', 'description' => 'Home decor and gardening'],
            ['name' => 'Books', 'slug' => 'books', 'description' => 'Books and publications'],
            ['name' => 'Sports', 'slug' => 'sports', 'description' => 'Sports equipment and gear'],
            ['name' => 'Toys', 'slug' => 'toys', 'description' => 'Toys and games'],
            ['name' => 'Beauty', 'slug' => 'beauty', 'description' => 'Beauty and personal care'],
            ['name' => 'Automotive', 'slug' => 'automotive', 'description' => 'Auto parts and accessories'],
        ];

        foreach ($categories as $category) {
            Category::create($category);
        }
    }
}