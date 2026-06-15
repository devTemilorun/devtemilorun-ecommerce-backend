<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function userStats(Request $request)
    {
        $user = $request->user();
        
        $orders = Order::where('user_id', $user->id);
        
        $totalOrders = (clone $orders)->count();
        
        $totalSpent = (clone $orders)
            ->whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])
            ->sum('total');
        
        $pendingOrders = (clone $orders)
            ->where('status', 'pending_payment')
            ->count();
        
        $processingOrders = (clone $orders)
            ->whereIn('status', ['paid', 'processing', 'shipped'])
            ->count();
        
        $completedOrders = (clone $orders)
            ->where('status', 'delivered')
            ->count();
        
        $avgOrderValue = $totalOrders > 0 ? round($totalSpent / $totalOrders, 2) : 0;
        
        $recentOrders = (clone $orders)
            ->latest()
            ->take(5)
            ->get(['id', 'order_number', 'status', 'total', 'created_at']);
        
        return response()->json([
            'success' => true,
            'data' => [
                'total_orders' => $totalOrders,
                'total_spent' => (float) $totalSpent,
                'avg_order_value' => (float) $avgOrderValue,
                'pending_orders' => $pendingOrders,
                'processing_orders' => $processingOrders,
                'completed_orders' => $completedOrders,
                'recent_orders' => $recentOrders,
            ]
        ]);
    }
}
