<?php

namespace App\Listeners;

use App\Domain\Order\Events\PaymentCompleted;
use App\Domain\Inventory\Actions\DeductInventoryAction;

class DeductInventory
{
    protected DeductInventoryAction $deductInventory;

    public function __construct(DeductInventoryAction $deductInventory)
    {
        $this->deductInventory = $deductInventory;
    }

    public function handle(PaymentCompleted $event): void
    {
        foreach ($event->order->items as $item) {
            $this->deductInventory->execute($item->product, $item->quantity, $event->order);
        }
    }
}