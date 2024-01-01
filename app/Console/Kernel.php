<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected $commands = [
        Commands\SetupAnalytics::class,
        Commands\CleanupExpiredCarts::class,
    ];

    protected function schedule(Schedule $schedule): void
    {
        // Run analytics updates every hour
        $schedule->command('analytics:update')->hourly();
        
        // Cleanup expired carts every 6 hours
        $schedule->command('cart:cleanup')->everySixHours();
        
        // Send low stock alerts daily
        $schedule->command('inventory:low-stock')->daily();
        
        // Generate sales reports weekly
        $schedule->command('reports:sales')->weekly();
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}