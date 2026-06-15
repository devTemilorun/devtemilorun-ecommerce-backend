<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AdminSettingsController extends Controller
{
    public function index()
    {
        return response()->json([
            'store_name' => config('app.name'),
            'store_email' => config('mail.from.address'),
            'currency' => 'USD',
        ]);
    }
    
    public function update(Request $request)
    {
        // Update settings logic here
        return response()->json(['message' => 'Settings updated']);
    }
    
    public function storeSettings()
    {
        return response()->json(['store_name' => 'ModernStore']);
    }
    
    public function updateStoreSettings(Request $request)
    {
        return response()->json(['message' => 'Store settings updated']);
    }
    
    public function paymentSettings()
    {
        return response()->json(['stripe_enabled' => true]);
    }
    
    public function updatePaymentSettings(Request $request)
    {
        return response()->json(['message' => 'Payment settings updated']);
    }
    
    public function emailSettings()
    {
        return response()->json(['smtp_host' => 'smtp.mailtrap.io']);
    }
    
    public function updateEmailSettings(Request $request)
    {
        return response()->json(['message' => 'Email settings updated']);
    }
    
    public function shippingSettings()
    {
        return response()->json(['free_shipping_threshold' => 100]);
    }
    
    public function updateShippingSettings(Request $request)
    {
        return response()->json(['message' => 'Shipping settings updated']);
    }
    
    public function taxSettings()
    {
        return response()->json(['tax_rate' => 10]);
    }
    
    public function updateTaxSettings(Request $request)
    {
        return response()->json(['message' => 'Tax settings updated']);
    }
}