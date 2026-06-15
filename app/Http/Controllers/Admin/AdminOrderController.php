<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with('user');
        
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }
        
        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        
        return response()->json($orders);
    }
    
    public function show($id)
    {
        $order = Order::with(['user', 'items'])->findOrFail($id);
        return response()->json($order);
    }
    
    public function updateStatus(Request $request, $id)
    {
        $request->validate([
            'status' => 'required|in:pending_payment,paid,processing,shipped,delivered,cancelled'
        ]);
        
        $order = Order::findOrFail($id);
        $order->status = $request->status;
        
        if ($request->status === 'paid' && !$order->paid_at) {
            $order->paid_at = now();
        }
        
        if ($request->status === 'shipped' && !$order->shipped_at) {
            $order->shipped_at = now();
        }
        
        if ($request->status === 'delivered' && !$order->delivered_at) {
            $order->delivered_at = now();
        }
        
        $order->save();
        
        return response()->json(['message' => 'Order status updated']);
    }
}