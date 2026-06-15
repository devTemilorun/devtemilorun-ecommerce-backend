<?php

namespace App\Console\Commands;

use App\Services\AnalyticsService;
use Illuminate\Console\Command;

class SetupAnalytics extends Command
{
    protected $signature = 'analytics:setup';
    protected $description = 'Setup analytics tables and initial data';

    public function handle(AnalyticsService $analyticsService)
    {
        $this->info('Setting up analytics...');
        
        // Create analytics tables if not exists
        \DB::statement('
            CREATE TABLE IF NOT EXISTS analytics_daily (
                id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                date DATE NOT NULL,
                total_revenue DECIMAL(12,2) DEFAULT 0,
                total_orders INT DEFAULT 0,
                total_customers INT DEFAULT 0,
                avg_order_value DECIMAL(10,2) DEFAULT 0,
                created_at TIMESTAMP NULL,
                updated_at TIMESTAMP NULL,
                UNIQUE KEY unique_date (date)
            )
        ');
        
        $this->info('Analytics setup completed!');
    }
}