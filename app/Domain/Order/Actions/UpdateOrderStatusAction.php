<?php

namespace App\Domain\Order\Actions;

use App\Models\Order;
use App\Domain\Order\Events\OrderStatusChanged;
use App\Domain\Inventory\Actions\RestockInventoryAction;

class UpdateOrderStatusAction
{
    protected RestockInventoryAction $restockInventory;
    
    public function __construct(RestockInventoryAction $restockInventory)
    {
        $this->restockInventory = $restockInventory;
    }
    
    public function execute(Order $order, string $newStatus): void
    {
        $oldStatus = $order->status;
        
        if ($oldStatus === $newStatus) {
            return;
        }
        
        // Handle cancellation - restore inventory
        if ($newStatus === 'cancelled' && $order->status === 'paid') {
            foreach ($order->items as $item) {
                $this->restockInventory->execute(
                    $item->product,
                    $item->quantity,
                    $order,
                    "Order #{$order->order_number} cancelled"
                );
            }
        }
        
        $order->updateStatus($newStatus);
        
        event(new OrderStatusChanged($order, $oldStatus, $newStatus));
    }
}