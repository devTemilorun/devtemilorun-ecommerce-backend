<?php

namespace App\Domain\Inventory\Actions;

use App\Models\Product;
use App\Models\Order;
use App\Models\InventoryLog;

class RestockInventoryAction
{
    public function execute(Product $product, int $quantity, ?Order $order = null, string $reason = 'Restock'): void
    {
        $stockBefore = $product->stock;
        
        // Add stock
        $product->increment('stock', $quantity);
        
        // Create inventory log
        InventoryLog::create([
            'product_id' => $product->id,
            'order_id' => $order?->id,
            'type' => 'return',
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $product->stock,
            'reason' => $reason,
        ]);
    }
}