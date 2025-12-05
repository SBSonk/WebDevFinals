<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\Product;
use App\Models\Order;
use App\Models\Inventory;
use App\Models\InventoryTransaction;
use App\Models\InventoryTransactionItem;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // ---- Users ----
        // Default customer for quick testing
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // Admin user
        $this->call(\Database\Seeders\AdminSeeder::class);

        // Another customer convenience account
        User::factory()->create([
            'name' => 'Dummy Customer',
            'email' => 'customer@example.com',
        ]);

        // ---- Catalog ----
        // Categories & Suppliers
        Category::factory(5)->create();
        Supplier::factory(5)->create();

        // Create a fixed number of products linked to existing categories/suppliers
        // so that we can create a 1:1 inventory row per product.
        $productCount = 20;
        \App\Models\Product::factory()
            ->count($productCount)
            ->state(function (array $attributes) {
                // Attach to existing category/supplier to avoid creating extras
                $categoryId = Category::inRandomOrder()->value('category_id');
                $supplierId = Supplier::inRandomOrder()->value('supplier_id');
                return [
                    'category_id' => $categoryId,
                    'supplier_id' => $supplierId,
                ];
            })
            ->create();

        // Ensure exactly one inventory record per product (no extras)
        Product::all()->each(function ($product) {
            Inventory::firstOrCreate(
                ['product_id' => $product->product_id],
                [
                    'stock_quantity' => rand(5, 50),
                    'reorder_level' => 10,
                    'max_stock_level' => 100,
                    'last_restocked' => now(),
                ]
            );
        });

        // ---- Inventory Movements (sample data) ----
        InventoryTransaction::factory()
            ->count(5)
            ->has(
                InventoryTransactionItem::factory()->count(3),
                'items'
            )
            ->create();

        // ---- Orders ----
        $this->call(\Database\Seeders\OrdersSeeder::class);
    }
}
