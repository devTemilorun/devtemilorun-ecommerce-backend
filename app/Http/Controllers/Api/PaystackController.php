<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Product;
use App\Mail\OrderConfirmationMail;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Config;

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

    $callbackUrl = config('services.paystack.callback_url') ?: config('app.frontend_url', 'http://localhost:3000') . '/order-success';
    
    $reference = 'ORDER_' . $order->order_number . '_' . time();

    $headers = [
        'Authorization' => 'Bearer ' . $this->secretKey,
        'Content-Type' => 'application/json',
    ];

    $http = Http::withHeaders($headers);
    
    if (config('app.env') !== 'production') {
        $http = $http->withoutVerifying();
    }

    $response = $http->post('https://api.paystack.co/transaction/initialize', [
        'amount' => (int)($order->total * 100),
        'email' => $user->email,
        'reference' => $reference,
        'callback_url' => $callbackUrl,
        'metadata' => [
            'order_id' => $order->id,
            'order_number' => $order->order_number,
            'user_id' => $user->id,
            'custom_fields' => [
                [
                    'display_name' => 'Order Number',
                    'variable_name' => 'order_number',
                    'value' => $order->order_number
                ],
                [
                    'display_name' => 'Customer Name',
                    'variable_name' => 'customer_name',
                    'value' => $user->name
                ]
            ]
        ],
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

    Log::error('Paystack initialization failed', [
        'status' => $response->status(),
        'response' => $response->json()
    ]);

    return response()->json([
        'success' => false,
        'message' => $response->json('message') ?? 'Failed to initialize payment'
    ], 500);
}

    public function callback(Request $request)
    {
        $reference = $request->query('reference');

        if (!$reference) {
            return redirect()->to(config('app.frontend_url') . '/');
        }

        $response = Http::withoutVerifying()
            ->withHeaders([
                'Authorization' => 'Bearer ' . $this->secretKey,
            ])
            ->get('https://api.paystack.co/transaction/verify/' . $reference);

        if ($response->successful() && $response->json('data.status') === 'success') {
            $data = $response->json('data');
            $metadata = $data['metadata'] ?? [];

            if (is_string($metadata)) {
                $decodedMetadata = json_decode($metadata, true);
                $metadata = is_array($decodedMetadata) ? $decodedMetadata : [];
            }

            $order = Order::find($metadata['order_id'] ?? null);

            if ($order && $order->status === 'pending_payment') {
                $order->update([
                    'status' => 'paid',
                    'paid_at' => now(),
                    'payment_intent_id' => $reference,
                ]);
            }

            return redirect()->to(config('app.frontend_url') . '/order-success');
        }

        return redirect()->to(config('app.frontend_url') . '/');
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

    public function webhook(Request $request)
{
    $signature = $request->header('x-paystack-signature');
    
    if (!$signature) {
        Log::warning('Paystack webhook: Missing signature header');
        return response()->json(['message' => 'Unauthorized - Missing signature'], 401);
    }

    $payload = $request->getContent();
    $expectedSignature = hash_hmac('sha512', $payload, $this->secretKey);
    
    if (!hash_equals($expectedSignature, $signature)) {
        Log::warning('Paystack webhook: Invalid signature', [
            'received' => $signature,
            'expected' => $expectedSignature
        ]);
        return response()->json(['message' => 'Unauthorized - Invalid signature'], 401);
    }

    Log::info('Paystack webhook received', [
        'event' => $request->input('event'),
        'reference' => $request->input('data.reference')
    ]);

    $event = $request->json()->all();
    $eventType = $event['event'] ?? null;

    switch ($eventType) {
        case 'charge.success':
            return $this->handleChargeSuccess($event);
            
        case 'charge.failed':
            return $this->handleChargeFailed($event);
            
        case 'charge.dispute.create':
            return $this->handleDisputeCreated($event);
            
        default:
            Log::info('Paystack webhook: Unhandled event type', ['event' => $eventType]);
            return response()->json(['status' => 'ignored'], 200);
    }
}


protected function handleChargeSuccess(array $event)
{
    $data = $event['data'];
    $reference = $data['reference'];
    
    $metadata = $data['metadata'] ?? [];
    $orderNumber = $metadata['order_number'] ?? null;
    $orderId = $metadata['order_id'] ?? null;

    Log::info('Processing successful charge', [
        'reference' => $reference,
        'order_number' => $orderNumber,
        'order_id' => $orderId
    ]);

    $order = null;
    if ($orderNumber) {
        $order = Order::where('order_number', $orderNumber)->first();
    } elseif ($orderId) {
        $order = Order::find($orderId);
    }

    if (!$order) {
        Log::warning('Order not found for webhook', [
            'reference' => $reference,
            'order_number' => $orderNumber,
            'order_id' => $orderId
        ]);
        return response()->json(['status' => 'order_not_found'], 404);
    }

    if ($order->status === 'paid') {
        Log::info('Order already marked as paid', ['order_id' => $order->id]);
        return response()->json(['status' => 'already_paid'], 200);
    }

    DB::transaction(function () use ($order, $reference, $data) {
        $order->update([
            'status' => 'paid',
            'paid_at' => now(),
            'payment_intent_id' => $reference,
            'payment_method' => 'paystack',
        ]);

        $order->payment()->create([
            'payment_intent_id' => $reference,
            'amount' => $data['amount'] / 100, 
            'currency' => $data['currency'] ?? 'NGN',
            'status' => 'succeeded',
            'payment_method_type' => $data['channel'] ?? 'card',
            'payment_method_details' => json_encode([
                'card_type' => $data['card_type'] ?? null,
                'bank' => $data['bank'] ?? null,
                'country' => $data['country'] ?? null,
                'channel' => $data['channel'] ?? null,
            ]),
            'paid_at' => now(),
        ]);

        try {
            Mail::to($order->user->email)->queue(new OrderConfirmationMail($order));
            Log::info('Order confirmation email queued', ['order_id' => $order->id]);
        } catch (\Exception $e) {
            Log::error('Failed to queue confirmation email: ' . $e->getMessage());
        }
    });

    Log::info('Order payment processed successfully', [
        'order_id' => $order->id,
        'reference' => $reference
    ]);

    return response()->json(['status' => 'success'], 200);
}

protected function handleChargeFailed(array $event)
{
    $data = $event['data'];
    $reference = $data['reference'];
    $metadata = $data['metadata'] ?? [];

    Log::warning('Payment failed', [
        'reference' => $reference,
        'metadata' => $metadata
    ]);

    $order = Order::where('order_number', $metadata['order_number'] ?? '')->first();
    
    if ($order && $order->status === 'pending_payment') {
        $order->update([
            'status' => 'cancelled',
            'notes' => $order->notes . "\nPayment failed: " . ($data['gateway_response'] ?? 'Unknown error')
        ]);

        foreach ($order->items as $item) {
            $product = Product::find($item->product_id);
            if ($product) {
                $product->increment('stock', $item->quantity);
            }
        }
    }

    return response()->json(['status' => 'success'], 200);
}

protected function handleDisputeCreated(array $event)
{
    $data = $event['data'];
    $transaction = $data['transaction'] ?? [];
    $reference = $transaction['reference'] ?? null;

    Log::warning('Dispute created for transaction', [
        'reference' => $reference,
        'dispute_reason' => $data['reason'] ?? 'Unknown'
    ]);

    try {
        Mail::to(config('mail.admin_email', 'admin@modernstore.com'))
            ->queue(new \App\Mail\DisputeCreatedMail($data));
    } catch (\Exception $e) {
        Log::error('Failed to send dispute notification: ' . $e->getMessage());
    }

    return response()->json(['status' => 'success'], 200);
}





}