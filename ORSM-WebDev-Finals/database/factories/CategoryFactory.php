<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class CategoryFactory extends Factory
{
    protected $model = \App\Models\Category::class;

    public function definition(): array
    {
        return [
            'category_name' => $this->faker->unique()->word(),
            'is_active' => $this->faker->boolean(90), // 90% chance of being active
        ];
    }
}
