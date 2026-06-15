<?php

namespace App\Console\Commands;

use App\Models\Cart;
use Illuminate\Console\Command;

class CleanupExpiredCarts extends Command
{
    protected $signature = 'cart:cleanup';
    protected $description = 'Clean up expired shopping carts';

    public function handle()
    {
        $expiredCarts = Cart::where('updated_at', '<', now()->subDays(7))->delete();
        
        $this->info("Cleaned up {$expiredCarts} expired carts.");
    }
}