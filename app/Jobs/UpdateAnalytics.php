<?php

namespace App\Jobs;

use App\Services\AnalyticsService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Cache;

class UpdateAnalytics implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(AnalyticsService $analyticsService): void
    {
        // Cache analytics data for faster access
        Cache::put('analytics_revenue', $analyticsService->getRevenueStats(), now()->addHours(6));
        Cache::put('analytics_top_products', $analyticsService->getTopProducts(), now()->addHours(6));
        Cache::put('analytics_customers', $analyticsService->getCustomerStats(), now()->addHours(6));
    }
}