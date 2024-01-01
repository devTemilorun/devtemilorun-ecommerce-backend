<?php

namespace App\Providers;

use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\PaymentCompleted;
use App\Listeners\SendOrderEmail;
use App\Listeners\DeductInventory;
use App\Listeners\UpdateProductSalesMetrics;
use App\Listeners\ClearProductCache;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        OrderCreated::class => [
            SendOrderEmail::class,
        ],
        PaymentCompleted::class => [
            DeductInventory::class,
            UpdateProductSalesMetrics::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}