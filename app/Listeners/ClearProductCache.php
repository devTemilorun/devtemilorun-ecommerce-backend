<?php

namespace App\Listeners;

use App\Domain\Product\Events\ProductCreated;
use App\Domain\Product\Events\ProductUpdated;
use App\Domain\Product\Events\ProductDeleted;
use Illuminate\Support\Facades\Cache;

class ClearProductCache
{
    public function handle($event): void
    {
        Cache::forget('featured_products');
        Cache::forget('products_list');
        
        if (isset($event->product)) {
            Cache::forget("product_{$event->product->id}");
        }
    }
}