<?php

namespace App\Domain\Order\Actions;

use App\Models\Order;
use App\Models\OrderItem;
use App\Domain\Order\Events\OrderCreated;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

class CreateOrderAction
{
    public function execute(array $data): Order
    {
        DB::beginTransaction();
        
        try {
            // Generate order number
            $data['order_number'] = $this->generateOrderNumber();
            
            // Calculate totals
            $subtotal = 0;
            foreach ($data['items'] as $item) {
                $subtotal += $item['price'] * $item['quantity'];
            }
            
            $tax = $subtotal * 0.1; // 10% tax
            $shippingCost = $subtotal > 100 ? 0 : 10; // Free shipping over $100
            $total = $subtotal + $tax + $shippingCost;
            
            $data['subtotal'] = $subtotal;
            $data['tax'] = $tax;
            $data['shipping_cost'] = $shippingCost;
            $data['total'] = $total;
            
            // Create order
            $order = Order::create([
                'order_number' => $data['order_number'],
                'user_id' => $data['user_id'],
                'status' => 'pending_payment',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shippingCost,
                'total' => $total,
                'shipping_address' => $data['address'],
                'payment_method' => 'stripe',
            ]);
            
            // Create order items
            foreach ($data['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $item['product_id'],
                    'product_name' => $item['name'],
                    'product_sku' => $item['sku'] ?? '',
                    'unit_price' => $item['price'],
                    'quantity' => $item['quantity'],
                    'total' => $item['price'] * $item['quantity'],
                ]);
            }
            
            DB::commit();
            
            // Dispatch event
            event(new OrderCreated($order));
            
            return $order->load('items');
            
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    private function generateOrderNumber(): string
    {
        return 'ORD-' . strtoupper(Str::random(8)) . '-' . time();
    }
}