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