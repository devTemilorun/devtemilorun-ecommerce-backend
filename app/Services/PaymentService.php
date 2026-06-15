<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Stripe\Webhook;
use App\Models\Order;
use App\Models\Payment;
use App\Domain\Order\Events\PaymentCompleted;
use App\Domain\Order\Events\PaymentFailed;

class PaymentService
{
    public function __construct()
    {
        Stripe::setApiKey(config('stripe.secret_key'));
    }

    public function createPaymentIntent(Order $order)
    {
        $paymentIntent = PaymentIntent::create([
            'amount' => (int) ($order->total * 100),
            'currency' => 'usd',
            'metadata' => [
                'order_id' => $order->id,
                'order_number' => $order->order_number,
            ],
        ]);

        // Create payment
        Payment::create([
            'order_id' => $order->id,
            'payment_intent_id' => $paymentIntent->id,
            'amount' => $order->total,
            'currency' => 'usd',
            'status' => 'pending',
            'payment_method_type' => 'card',
        ]);

        return $paymentIntent;
    }

    public function handleWebhook($payload, $sigHeader)
    {
        $event = Webhook::constructEvent(
            $payload,
            $sigHeader,
            config('stripe.webhook_secret')
        );

        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentSuccess($event->data->object);
                break;
                
            case 'payment_intent.payment_failed':
                $this->handlePaymentFailure($event->data->object);
                break;
                
            case 'charge.refunded':
                $this->handleRefund($event->data->object);
                break;
        }

        return $event;
    }

    protected function handlePaymentSuccess($paymentIntent)
    {
        $payment = Payment::where('payment_intent_id', $paymentIntent->id)->first();
        
        if (!$payment) {
            return;
        }
        
        $payment->update([
            'status' => 'succeeded',
            'payment_method_details' => $paymentIntent->payment_method_details,
            'paid_at' => now(),
        ]);
        
        event(new PaymentCompleted($payment->order));
    }

    protected function handlePaymentFailure($paymentIntent)
    {
        $payment = Payment::where('payment_intent_id', $paymentIntent->id)->first();
        
        if ($payment) {
            $payment->update([
                'status' => 'failed',
                'payment_method_details' => $paymentIntent->last_payment_error,
            ]);
            
            event(new PaymentFailed($payment->order));
        }
    }

    protected function handleRefund($charge)
    {
        $paymentIntentId = $charge->payment_intent;
        $payment = Payment::where('payment_intent_id', $paymentIntentId)->first();
        
        if ($payment) {
            $payment->update(['status' => 'refunded']);
            
            // Handle order refund
            $payment->order->updateStatus('refunded');
        }
    }
}