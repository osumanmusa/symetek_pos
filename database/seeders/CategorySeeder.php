<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            ['name' => 'Electronics', 'description' => 'Electronic devices and accessories'],
            ['name' => 'Clothing', 'description' => 'Fashion and apparel'],
            ['name' => 'Groceries', 'description' => 'Food and household items'],
            ['name' => 'Books', 'description' => 'Books and magazines'],
            ['name' => 'Home & Garden', 'description' => 'Home improvement and gardening'],
            ['name' => 'Sports', 'description' => 'Sports equipment and accessories'],
            ['name' => 'Health & Beauty', 'description' => 'Health and beauty products'],
            ['name' => 'Toys & Games', 'description' => 'Toys and games for all ages'],
            ['name' => 'Automotive', 'description' => 'Car parts and accessories'],
            ['name' => 'Office Supplies', 'description' => 'Office and stationery items'],
        ];

        foreach ($categories as $category) {
            Category::create([
                'name' => $category['name'],
                'slug' => Str::slug($category['name']),
                'description' => $category['description'],
                'is_active' => true,
            ]);
        }
    }
}