<?php

namespace App\Listeners;

use App\Domain\Order\Events\PaymentCompleted;

class UpdateProductSalesMetrics
{
    public function handle(PaymentCompleted $event): void
    {
        foreach ($event->order->items as $item) {
            $product = $item->product;
            
            $product->increment('sales_count', $item->quantity);
            
        }
    }
}