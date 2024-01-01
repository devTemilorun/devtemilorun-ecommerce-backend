<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\OrderStatusUpdateMail;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class AdminOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']);

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
            'status' => 'required|in:pending_payment,paid,processing,shipped,delivered,cancelled,refunded',
            'tracking_number' => 'nullable|string|max:255', 
        ]);

        $order = Order::with('user')->findOrFail($id);
        $previousStatus = $order->status;

        if ($previousStatus === $request->status) {
            return response()->json(['message' => 'Order status unchanged']);
        }

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

        if ($request->has('tracking_number')) {
            $order->tracking_number = $request->tracking_number;
        }

        $order->save();

        try {
            Mail::to($order->user->email)
                ->queue(new OrderStatusUpdateMail($order, $previousStatus));
            Log::info('Order status update email queued', [
                'order_id' => $order->id,
                'previous_status' => $previousStatus,
                'new_status' => $order->status,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to queue order status update email: ' . $e->getMessage());
        }

        return response()->json([
            'message' => 'Order status updated',
            'order' => $order,
        ]);
    }
}