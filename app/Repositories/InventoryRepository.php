<?php

namespace App\Repositories;

use App\Models\InventoryLog;
use App\Models\Product;
use Illuminate\Support\Facades\DB;

class InventoryRepository
{
    public function logInventoryChange(
        Product $product,
        string $type,
        int $quantity,
        int $stockBefore,
        ?int $orderId = null,
        ?string $reason = null
    ): InventoryLog {
        return InventoryLog::create([
            'product_id' => $product->id,
            'order_id' => $orderId,
            'type' => $type,
            'quantity' => $quantity,
            'stock_before' => $stockBefore,
            'stock_after' => $product->stock,
            'reason' => $reason,
        ]);
    }

    public function getLowStockProducts(int $threshold = 5)
    {
        return Product::where('stock', '<=', $threshold)
            ->where('status', 'published')
            ->get();
    }

    public function getInventoryHistory(int $productId, int $limit = 50)
    {
        return InventoryLog::where('product_id', $productId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }
}