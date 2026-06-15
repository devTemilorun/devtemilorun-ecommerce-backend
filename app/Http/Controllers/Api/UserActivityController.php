<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class UserActivityController extends Controller
{
    public function getActivity(Request $request)
    {
        $user = $request->user();
        
        // Get recent orders
        $recentOrders = Order::where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get(['id', 'order_number', 'status', 'total', 'created_at']);
        
        $activities = [];
        
        foreach ($recentOrders as $order) {
            $activities[] = [
                'id' => (string) $order->id,
                'type' => 'order',
                'action' => $this->getOrderActionMessage($order->status),
                'order_number' => $order->order_number,
                'total' => (float) $order->total,
                'status' => $order->status,
                'created_at' => $order->created_at->toISOString(),
                'time_ago' => $order->created_at->diffForHumans(),
            ];
        }
        
        // Add login activity if available
        if ($user->last_login_at) {
            $activities[] = [
                'id' => 'login_' . time(),
                'type' => 'login',
                'action' => 'Logged in to your account',
                'created_at' => $user->last_login_at->toISOString(),
                'time_ago' => $user->last_login_at->diffForHumans(),
            ];
        }
        
        // Sort by created_at descending
        $activities = collect($activities)
            ->sortByDesc('created_at')
            ->values()
            ->all();
        
        return response()->json([
            'success' => true,
            'data' => $activities
        ]);
    }
    
    private function getOrderActionMessage($status)
    {
        $messages = [
            'pending_payment' => 'Placed a new order',
            'paid' => 'Completed payment for order',
            'processing' => 'Order is being processed',
            'shipped' => 'Order has been shipped',
            'delivered' => 'Order was delivered',
            'cancelled' => 'Cancelled order',
        ];
        
        return $messages[$status] ?? 'Updated order';
    }
}