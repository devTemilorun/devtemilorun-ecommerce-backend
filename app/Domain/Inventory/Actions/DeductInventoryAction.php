<?php

namespace App\Domain\Inventory\Actions;

use App\Models\Product;
use App\Models\Order;
use App\Models\InventoryLog;

class DeductInventoryAction
{
    public function execute(Product $product, int $quantity, Order $order): void
    {
        $stockBefore = $product->stock;
        
        $product->decrement('stock', $quantity);
        
        InventoryLog::create([
            'product_id' => $product->id,
            'order_id' => $order->id,
            'type' => 'sale',
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $product->stock,
            'reason' => "Order #{$order->order_number} placed",
        ]);
        
        if ($product->stock <= $product->low_stock_threshold) {
            event(new \App\Domain\Inventory\Events\LowStockAlert($product));
        }
    }
}