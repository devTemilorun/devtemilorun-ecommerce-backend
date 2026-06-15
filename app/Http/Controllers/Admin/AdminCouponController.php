<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AdminCouponController extends Controller
{
    public function index()
    {
        // Return all coupons (you'll need to create a Coupon model)
        return response()->json([
            'data' => [],
            'message' => 'Coupons feature coming soon'
        ]);
    }
    
    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|unique:coupons',
            'type' => 'required|in:fixed,percentage',
            'value' => 'required|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);
        
        return response()->json([
            'message' => 'Coupon created successfully',
            'data' => $validated
        ], 201);
    }
    
    public function show($id)
    {
        return response()->json([
            'message' => 'Coupon details',
            'data' => ['id' => $id]
        ]);
    }
    
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'code' => 'sometimes|string|unique:coupons,code,' . $id,
            'type' => 'sometimes|in:fixed,percentage',
            'value' => 'sometimes|numeric|min:0',
            'min_amount' => 'nullable|numeric|min:0',
            'max_discount' => 'nullable|numeric|min:0',
            'starts_at' => 'nullable|date',
            'expires_at' => 'nullable|date|after:starts_at',
            'usage_limit' => 'nullable|integer|min:1',
            'is_active' => 'boolean',
        ]);
        
        return response()->json([
            'message' => 'Coupon updated successfully',
            'data' => $validated
        ]);
    }
    
    public function destroy($id)
    {
        return response()->json([
            'message' => 'Coupon deleted successfully'
        ]);
    }
    
    public function toggleStatus($id)
    {
        return response()->json([
            'message' => 'Coupon status toggled'
        ]);
    }
}
