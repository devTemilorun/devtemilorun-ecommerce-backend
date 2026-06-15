<?php

namespace App\Domain\Inventory\Actions;

use App\Models\Product;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;

class CheckInventoryAction
{
    public function execute(array $items): bool
    {
        foreach ($items as $item) {
            $product = Product::find($item['product_id']);
            
            if (!$product) {
                Log::error("Product not found: {$item['product_id']}");
                return false;
            }
            
            if ($product->stock < $item['quantity']) {
                Log::warning("Insufficient stock for product: {$product->name}", [
                    'requested' => $item['quantity'],
                    'available' => $product->stock
                ]);
                return false;
            }
        }
        
        return true;
    }
    
    public function checkForOrder(OrderItem $orderItem): bool
    {
        $product = $orderItem->product;
        
        if (!$product) {
            return false;
        }
        
        return $product->stock >= $orderItem->quantity;
    }
}