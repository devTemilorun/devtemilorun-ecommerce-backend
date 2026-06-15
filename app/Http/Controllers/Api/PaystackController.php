<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaystackController extends Controller
{
    protected $secretKey;

    public function __construct()
    {
        $this->secretKey = config('services.paystack.secret_key');
    }

    public function initialize(Request $request)
    {
        Log::info('Paystack initialize called', ['request_data' => $request->all()]);
        
        $user = $request->user();
        $orderId = $request->input('order_id');
        
        $order = Order::where('user_id', $user->id)
            ->where('id', $orderId)
            ->where('status', 'pending_payment')
            ->first();

        if (!$order) {
            return response()->json([
                'success' => false,
                'message' => 'No pending order found'
            ], 404);
        }

        $reference = 'ORDER_' . $order->order_number . '_' . time();

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
            ])
            ->post('https://api.paystack.co/transaction/initialize', [
                'amount' => (int)($order->total * 100),
                'email' => $user->email,
                'reference' => $reference,
                'callback_url' => config('app.frontend_url') . '/order-success',
                'metadata' => json_encode([
                    'order_id' => $order->id,
                    'order_number' => $order->order_number,
                ]),
            ]);

        Log::info('Paystack API response', ['response' => $response->json()]);

        if ($response->successful() && $response->json('status')) {
            $data = $response->json('data');
            return response()->json([
                'success' => true,
                'authorization_url' => $data['authorization_url'],
                'reference' => $reference,
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $response->json('message') ?? 'Failed to initialize payment'
        ], 500);
    }

    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->to(config('app.frontend_url') . '/order-failed');
        }

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
            ])
            ->get('https://api.paystack.co/transaction/verify/' . $reference);

        if ($response->successful() && $response->json('data.status') === 'success') {
            $data = $response->json('data');
            $metadata = json_decode($data['metadata'], true);

            $order = Order::find($metadata['order_id']);

            if ($order && $order->status === 'pending_payment') {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_intent_id' => $reference,
                ]);
            }

            // Redirect to order success page
            return redirect()->to(config('app.frontend_url') . '/order-success');
        }

        return redirect()->to(config('app.frontend_url') . '/order-failed');
    }

    public function webhook(Request $request)
    {
        $signature = $request->header('x-paystack-signature');

        if (!$signature) {
            return response()->json(['message' => 'Unauthorized'], 401);
        }

        $event = $request->json()->all();

        if ($event['event'] === 'charge.success') {
            $data = $event['data'];
            $reference = $data['reference'];
            $metadata = json_decode($data['metadata'], true);

            $order = Order::where('order_number', $metadata['order_number'] ?? '')->first();

            if ($order && $order->status === 'pending_payment') {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_intent_id' => $reference,
                ]);
            }
        }

        return response()->json(['status' => 'success']);
    }

    public function verify(Request $request)
    {
        $reference = $request->input('reference');

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
            ])
            ->get('https://api.paystack.co/transaction/verify/' . $reference);

        if ($response->successful() && $response->json('data.status') === 'success') {
            return response()->json(['status' => 'success']);
        }

        return response()->json([
            'status' => 'failed',
            'message' => $response->json('message')
        ], 400);
    }
}