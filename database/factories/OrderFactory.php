<?php

namespace Database\Factories;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $subtotal = $this->faker->randomFloat(2, 50, 500);
        $tax = $subtotal * 0.1;
        $shipping = $subtotal > 100 ? 0 : 10;
        $total = $subtotal + $tax + $shipping;

        return [
            'order_number' => 'ORD-' . strtoupper($this->faker->bothify('???-#####')),
            'user_id' => User::factory(),
            'status' => $this->faker->randomElement(['pending_payment', 'paid', 'processing', 'shipped', 'delivered']),
            'subtotal' => $subtotal,
            'tax' => $tax,
            'shipping_cost' => $shipping,
            'discount' => $this->faker->randomFloat(2, 0, 50),
            'total' => $total,
            'shipping_address' => json_encode([
                'first_name' => $this->faker->firstName(),
                'last_name' => $this->faker->lastName(),
                'address_line1' => $this->faker->streetAddress(),
                'city' => $this->faker->city(),
                'state' => $this->faker->state(),
                'postal_code' => $this->faker->postcode(),
                'country' => $this->faker->country(),
                'phone' => $this->faker->phoneNumber(),
                'email' => $this->faker->email(),
            ]),
            'payment_method' => 'stripe',
            'created_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
            'updated_at' => $this->faker->dateTimeBetween('-1 year', 'now'),
        ];
    }
}