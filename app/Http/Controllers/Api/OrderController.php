<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $orders = $request->user()->orders()->with('items')->orderBy('created_at', 'desc')->get();
        return response()->json($orders);
    }
    
    public function show($id, Request $request)
    {
        $order = Order::with('items')->where('user_id', $request->user()->id)->findOrFail($id);
        return response()->json($order);
    }
    
    public function store(Request $request)
    {
        Log::info('Order creation request received', [
            'user_id' => $request->user()?->id,
            'data' => $request->all()
        ]);
        
        try {
            $validated = $request->validate([
                'items' => 'required|array|min:1',
                'items.*.product_id' => 'required|exists:products,id',
                'items.*.quantity' => 'required|integer|min:1',
                'address' => 'required|array',
                'address.first_name' => 'required|string|max:255',
                'address.last_name' => 'required|string|max:255',
                'address.email' => 'required|email|max:255',
                'address.phone' => 'required|string|max:20',
                'address.address_line1' => 'required|string|max:255',
                'address.city' => 'required|string|max:100',
                'address.state' => 'required|string|max:100',
                'address.postal_code' => 'required|string|max:20',
                'address.country' => 'required|string|max:100',
            ]);
            
            DB::beginTransaction();
            
            $subtotal = 0;
            $orderItems = [];
            
            foreach ($request->items as $item) {
                $product = Product::find($item['product_id']);
                
                if (!$product) {
                    throw new \Exception("Product not found: {$item['product_id']}");
                }
                
                if ($product->stock < $item['quantity']) {
                    throw new \Exception("Insufficient stock for {$product->name}. Available: {$product->stock}, Requested: {$item['quantity']}");
                }
                
                $itemTotal = $product->price * $item['quantity'];
                $subtotal += $itemTotal;
                
                $orderItems[] = [
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'product_sku' => $product->sku,
                    'unit_price' => $product->price,
                    'quantity' => $item['quantity'],
                    'total' => $itemTotal,
                ];
            }
            
            $tax = $subtotal * 0.10;
            $shipping = $subtotal > 100 ? 0 : 10;
            $total = $subtotal + $tax + $shipping;
            
            $order = Order::create([
                'order_number' => 'ORD-' . strtoupper(Str::random(10)) . '-' . time(),
                'user_id' => $request->user()->id,
                'status' => 'pending_payment',
                'subtotal' => $subtotal,
                'tax' => $tax,
                'shipping_cost' => $shipping,
                'discount' => 0,
                'total' => $total,
                'shipping_address' => json_encode($request->address),
                'payment_method' => 'paystack',
            ]);
            
            foreach ($orderItems as $item) {
                $item['order_id'] = $order->id;
                OrderItem::create($item);
                
                // Deduct stock
                $product = Product::find($item['product_id']);
                $product->decrement('stock', $item['quantity']);
            }
            
            DB::commit();
            
            Log::info('Order created successfully', [
                'order_id' => $order->id, 
                'order_number' => $order->order_number,
                'total' => $total,
                'subtotal' => $subtotal
            ]);
            
            return response()->json([
                'success' => true,
                'order' => $order->load('items'),
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Order creation failed', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
    
    public function cancel($id, Request $request)
    {
        $order = Order::where('user_id', $request->user()->id)->findOrFail($id);
        
        if (!in_array($order->status, ['pending_payment', 'paid'])) {
            return response()->json(['message' => 'Order cannot be cancelled'], 400);
        }
        
        $order->update(['status' => 'cancelled']);
        
        // Restore stock
        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('stock', $item->quantity);
            }
        }
        
        return response()->json(['message' => 'Order cancelled successfully']);
    }
}