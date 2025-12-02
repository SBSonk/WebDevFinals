<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Inventory;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);
    
    // Create 5 categories
    Category::factory(5)->create();

    // Create 5 suppliers
    Supplier::factory(5)->create();

    // Create 20 products
    Product::factory(20)->create();

    // Create inventory records for all products
    Product::all()->each(function ($product) {
        Inventory::factory()->create(['product_id' => $product->product_id]);
    });

    }
}
