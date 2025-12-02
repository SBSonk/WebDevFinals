<?php

namespace Database\Factories;

use App\Models\InventoryTransactionItem;
use App\Models\InventoryTransaction;
use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryTransactionItemFactory extends Factory
{
    protected $model = InventoryTransactionItem::class;

    public function definition(): array
    {
        return [
            'transaction_id' => InventoryTransaction::factory(), // Creates a transaction if none exists
            'product_id' => Product::factory(), // Creates a product if none exists
            'quantity' => $this->faker->numberBetween(1, 50),
        ];
    }
}
