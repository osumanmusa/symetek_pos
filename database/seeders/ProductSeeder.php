<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use App\Models\Category;

class ProductSeeder extends Seeder
{
    public function run()
    {
        $categories = Category::all();
        
        if ($categories->isEmpty()) {
            $this->call(CategorySeeder::class);
            $categories = Category::all();
        }
        
        $products = [
            [
                'name' => 'iPhone 14 Pro',
                'description' => 'Latest Apple smartphone with advanced camera',
                'cost_price' => 899.99,
                'selling_price' => 1099.99,
                'stock_quantity' => 50,
                'sku' => 'APP-IP14P-128',
                'barcode' => '123456789012',
                'unit' => 'pcs',
                'is_taxable' => true,
            ],
            [
                'name' => 'Samsung Galaxy S23',
                'description' => 'Android flagship phone',
                'cost_price' => 749.99,
                'selling_price' => 899.99,
                'stock_quantity' => 30,
                'sku' => 'SAM-GS23-256',
                'barcode' => '234567890123',
                'unit' => 'pcs',
                'is_taxable' => true,
            ],
            [
                'name' => 'Nike Air Max',
                'description' => 'Running shoes',
                'cost_price' => 89.99,
                'selling_price' => 129.99,
                'stock_quantity' => 100,
                'sku' => 'NK-AMAX-10',
                'barcode' => '345678901234',
                'unit' => 'pair',
                'is_taxable' => true,
            ],
            [
                'name' => 'Coca-Cola 2L',
                'description' => 'Carbonated soft drink',
                'cost_price' => 1.50,
                'selling_price' => 2.99,
                'stock_quantity' => 200,
                'sku' => 'CC-2LTR',
                'barcode' => '456789012345',
                'unit' => 'bottle',
                'is_taxable' => true,
            ],
            [
                'name' => 'HP LaserJet Printer',
                'description' => 'Office laser printer',
                'cost_price' => 199.99,
                'selling_price' => 299.99,
                'stock_quantity' => 25,
                'sku' => 'HP-LJPRO',
                'barcode' => '567890123456',
                'unit' => 'pcs',
                'is_taxable' => true,
            ],
        ];

        foreach ($products as $index => $productData) {
            $category = $categories[$index % count($categories)];
            
            Product::create([
                ...$productData,
                'category_id' => $category->id,
                'low_stock_threshold' => 10,
                'is_active' => true,
            ]);
        }
    }
}