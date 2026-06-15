<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessStripeWebhook;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class WebhookController extends Controller
{
    public function handleStripe(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        // Process webhook asynchronously
        dispatch(new ProcessStripeWebhook($payload, $sigHeader));

        return response()->json(['received' => true], Response::HTTP_OK);
    }
}