<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Supplier;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProductFactory extends Factory
{
    protected $model = \App\Models\Product::class;

    public function definition(): array
    {
        return [
            'product_name' => $this->faker->unique()->words(2, true),
            'description' => $this->faker->sentence(10),
            'category_id' => Category::factory(), // creates or references a category
            'supplier_id' => Supplier::factory(), // creates or references a supplier
            'unit_price' => $this->faker->randomFloat(2, 50, 1000),
            'cost_price' => $this->faker->randomFloat(2, 30, 900),
            'is_active' => $this->faker->boolean(95),
        ];
    }
}
