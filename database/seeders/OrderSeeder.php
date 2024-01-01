<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $customers = User::where('role', 'customer')->get();
        $products  = Product::all();

        $statuses = ['pending_payment', 'paid', 'processing', 'shipped', 'delivered', 'cancelled'];

        $addresses = [
            [
                'street'  => '123 Main Street',
                'city'    => 'New York',
                'state'   => 'NY',
                'zip'     => '10001',
                'country' => 'US',
            ],
            [
                'street'  => '456 Oak Avenue',
                'city'    => 'Los Angeles',
                'state'   => 'CA',
                'zip'     => '90001',
                'country' => 'US',
            ],
            [
                'street'  => '789 Pine Road',
                'city'    => 'Chicago',
                'state'   => 'IL',
                'zip'     => '60601',
                'country' => 'US',
            ],
            [
                'street'  => '321 Elm Street',
                'city'    => 'Houston',
                'state'   => 'TX',
                'zip'     => '77001',
                'country' => 'US',
            ],
            [
                'street'  => '654 Maple Drive',
                'city'    => 'Phoenix',
                'state'   => 'AZ',
                'zip'     => '85001',
                'country' => 'US',
            ],
            [
                'street'  => '987 Cedar Lane',
                'city'    => 'Philadelphia',
                'state'   => 'PA',
                'zip'     => '19101',
                'country' => 'US',
            ],
            [
                'street'  => '147 Birch Court',
                'city'    => 'San Antonio',
                'state'   => 'TX',
                'zip'     => '78201',
                'country' => 'US',
            ],
            [
                'street'  => '258 Walnut Blvd',
                'city'    => 'San Diego',
                'state'   => 'CA',
                'zip'     => '92101',
                'country' => 'US',
            ],
        ];

        for ($i = 0; $i < 20; $i++) {
            $customer = $customers->random();
            $address  = $addresses[array_rand($addresses)];

            $orderProducts = $products->random(rand(1, 4));
            $subtotal = 0;
            $tax = 0;
            $shippingCost = 0;
            $discount = 0;

            $orderItems = [];
            foreach ($orderProducts as $product) {
                $quantity = rand(1, 3);
                $price = $product->price;
                $subtotal += $price * $quantity;

                $orderItems[] = [
                    'product_id' => $product->id,
                    'quantity'   => $quantity,
                    'price'      => $price,
                ];
            }

            $tax = round($subtotal * 0.10, 2);
            
            $shippingCost = 10.00;
            
            $discountPercent = [0, 0, 5, 10, 15][rand(0, 4)];
            $discount = round($subtotal * ($discountPercent / 100), 2);
            
            $total = $subtotal + $tax + $shippingCost - $discount;

            $status = $statuses[array_rand($statuses)];
            $createdAt = now()->subDays(rand(1, 90));

            $orderNumber = 'ORD-' . strtoupper(Str::random(8)) . '-' . date('Ymd');

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $customer->id,
                'status' => $status,
                'subtotal' => round($subtotal, 2),
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'discount' => $discount,
                'total' => round($total, 2),
                'shipping_address' => json_encode($address),
                'billing_address' => json_encode($address),
                'payment_method' => ['stripe', 'paystack', 'paypal'][rand(0, 2)],
                'payment_intent_id' => 'pi_' . Str::random(24),
                'paid_at' => in_array($status, ['paid', 'processing', 'shipped', 'delivered']) ? $createdAt->addHours(rand(1, 24)) : null,
                'shipped_at' => in_array($status, ['shipped', 'delivered']) ? $createdAt->addDays(rand(1, 3)) : null,
                'delivered_at' => $status === 'delivered' ? $createdAt->addDays(rand(4, 7)) : null,
                'notes' => rand(0, 1) ? null : 'Please leave at the door.',
                'created_at' => $createdAt,
                'updated_at' => $createdAt,
            ]);

            foreach ($orderItems as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'product_name' => Product::find($item['product_id'])->name,
                    'product_sku' => Product::find($item['product_id'])->sku,
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }
        }
    }
}