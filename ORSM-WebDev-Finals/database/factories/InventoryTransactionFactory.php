<?php

namespace Database\Factories;

use App\Models\InventoryTransaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class InventoryTransactionFactory extends Factory
{
    protected $model = InventoryTransaction::class;

    public function definition(): array
    {
        return [
            'transaction_type' => $this->faker->randomElement(['in', 'out']),
            'reference_number' => $this->faker->optional()->bothify('REF-#####'),
            'remarks' => $this->faker->optional()->sentence(8),
        ];
    }
}
