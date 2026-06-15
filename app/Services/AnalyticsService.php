<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AnalyticsService
{
    public function getRevenueStats($startDate = null, $endDate = null)
    {
        $query = Order::where('status', 'paid');
        
        if ($startDate) {
            $query->where('paid_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('paid_at', '<=', $endDate);
        }
        
        return [
            'total_revenue' => $query->sum('total'),
            'average_order_value' => $query->avg('total'),
            'total_orders' => $query->count(),
            'revenue_by_day' => $this->getRevenueByDay($startDate, $endDate),
        ];
    }

    public function getRevenueByDay($startDate = null, $endDate = null)
    {
        $query = Order::where('status', 'paid')
            ->select(DB::raw('DATE(paid_at) as date'), DB::raw('SUM(total) as revenue'))
            ->groupBy('date');
        
        if ($startDate) {
            $query->where('paid_at', '>=', $startDate);
        }
        
        if ($endDate) {
            $query->where('paid_at', '<=', $endDate);
        }
        
        return $query->get();
    }

    public function getTopProducts($limit = 10)
    {
        return Product::orderBy('sales_count', 'desc')
            ->take($limit)
            ->get(['id', 'name', 'price', 'sales_count', 'stock']);
    }

    public function getCustomerStats()
    {
        return [
            'total_customers' => User::where('role', 'customer')->count(),
            'new_customers' => User::where('role', 'customer')
                ->where('created_at', '>=', Carbon::now()->subDays(30))
                ->count(),
            'repeat_customers' => $this->getRepeatCustomerCount(),
        ];
    }

    protected function getRepeatCustomerCount()
    {
        return User::where('role', 'customer')
            ->whereHas('orders', function ($query) {
                $query->where('status', 'paid');
            }, '>=', 2)
            ->count();
    }
}