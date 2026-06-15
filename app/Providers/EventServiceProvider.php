<?php

namespace App\Providers;

use App\Domain\Order\Events\OrderCreated;
use App\Domain\Order\Events\PaymentCompleted;
use App\Domain\Product\Events\ProductCreated;
use App\Domain\Product\Events\ProductUpdated;
use App\Domain\Product\Events\ProductDeleted;
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
        ProductCreated::class => [
            ClearProductCache::class,
        ],
        ProductUpdated::class => [
            ClearProductCache::class,
        ],
        ProductDeleted::class => [
            ClearProductCache::class,
        ],
    ];

    public function boot(): void
    {
        //
    }
}