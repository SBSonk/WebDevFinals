<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Order::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'customer_id' => User::factory(),
            'order_date' => $this->faker->dateTimeBetween('-1 years', 'now'),
            'order_status' => $this->faker->randomElement(['pending', 'processing', 'completed', 'cancelled']),
            'total_amount' => $this->faker->randomFloat(2, 1, 10000),
            'payment_status' => $this->faker->randomElement(['pending', 'paid', 'failed']),
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal', 'bank_transfer', 'cash_on_delivery']),
            'shipping_address' => $this->faker->address(),
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}
