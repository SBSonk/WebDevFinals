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
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        // TODO: Remove this dummy customer once testing is done
        User::factory()->create([
            'name' => 'Dummy Customer',
            'email' => 'customer@example.com',
        ]);

        // Create 5 suppliers
        Supplier::factory(5)->create();

        // //Create 20 orders
        // Order::factory(5)->create();

        // Create inventory records for all products
        Product::all()->each(function ($product) {
            Inventory::factory()->create(['product_id' => $product->product_id]);
        });

        InventoryTransaction::factory()
            ->count(5)
            ->has(
                InventoryTransactionItem::factory()->count(3),
                'items'
            )
            ->create();

        Inventory::factory()
            ->count(10)
            ->create();
    }
}
