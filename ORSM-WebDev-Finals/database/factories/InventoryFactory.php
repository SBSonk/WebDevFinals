<?php

namespace Database\Factories;

use App\Models\Product;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryFactory extends Factory
{
    protected $model = \App\Models\Inventory::class;

    public function definition(): array
    {
        return [
            'product_id' => Product::factory(), // creates or references a product
            'stock_quantity' => $this->faker->numberBetween(0, 500),
            'reorder_level' => $this->faker->numberBetween(10, 50),
            'max_stock_level' => $this->faker->numberBetween(100, 1000),
            'last_restocked' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}
