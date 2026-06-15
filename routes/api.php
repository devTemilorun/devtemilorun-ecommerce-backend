<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\PaystackController;
use App\Http\Controllers\Api\AnalyticsController;
use App\Http\Controllers\Api\UserActivityController;
use App\Http\Controllers\Admin\AdminAnalyticsController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminUserController;
use App\Http\Controllers\Admin\AdminCouponController;
use App\Http\Controllers\Admin\AdminSettingsController;
use App\Http\Controllers\Admin\AdminSystemController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ContactController;

// Public routes
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/auth/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/auth/refresh', [AuthController::class, 'refresh'])->middleware('auth:sanctum');

// Products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/featured', [ProductController::class, 'featured']);
Route::get('/products/{id}', [ProductController::class, 'show']);
Route::get('/categories', [CategoryController::class, 'index']);

// Paystack
Route::post('/paystack/initialize', [PaystackController::class, 'initialize'])->middleware('auth:sanctum');
Route::get('/paystack/callback', [PaystackController::class, 'callback']);
Route::post('/paystack/webhook', [PaystackController::class, 'webhook']);
Route::post('/paystack/verify', [PaystackController::class, 'verify'])->middleware('auth:sanctum');

// Protected user routes
Route::middleware(['auth:sanctum'])->group(function () {
    Route::get('/user', [AuthController::class, 'user']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/orders', [OrderController::class, 'index']);
    Route::get('/orders/{id}', [OrderController::class, 'show']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::post('/orders/{id}/cancel', [OrderController::class, 'cancel']);
    
    // User Analytics
    Route::get('/analytics/user', [AnalyticsController::class, 'userStats']);
    
    // User Activity
    Route::get('/user/activity', [UserActivityController::class, 'getActivity']);

    // User Profile Edit
    Route::put('/user', [UserController::class, 'update']);
    Route::post('/user/avatar', [UserController::class, 'uploadAvatar']);

});

// Admin routes
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    // Dashboard & Analytics
    Route::get('/analytics/dashboard', [AdminAnalyticsController::class, 'dashboard']);
    Route::get('/analytics/revenue', [AdminAnalyticsController::class, 'revenue']);
    Route::get('/analytics/products', [AdminAnalyticsController::class, 'topProducts']);
    Route::get('/analytics/customers', [AdminAnalyticsController::class, 'customerStats']);
    
    // Products
    Route::get('/products', [AdminProductController::class, 'index']);
    Route::post('/products', [AdminProductController::class, 'store']);
    Route::get('/products/{id}', [AdminProductController::class, 'show']);
    Route::put('/products/{id}', [AdminProductController::class, 'update']);
    Route::delete('/products/{id}', [AdminProductController::class, 'destroy']);
    Route::post('/products/{id}/featured', [AdminProductController::class, 'toggleFeatured']);
    
    // Orders
    Route::get('/orders', [AdminOrderController::class, 'index']);
    Route::get('/orders/{id}', [AdminOrderController::class, 'show']);
    Route::put('/orders/{id}/status', [AdminOrderController::class, 'updateStatus']);
    
    // Users/Customers
    Route::get('/users', [AdminUserController::class, 'index']);
    Route::get('/users/{id}', [AdminUserController::class, 'show']);
    Route::put('/users/{id}', [AdminUserController::class, 'update']);
    Route::delete('/users/{id}', [AdminUserController::class, 'destroy']);
    
    // Coupons
    Route::get('/coupons', [AdminCouponController::class, 'index']);
    Route::post('/coupons', [AdminCouponController::class, 'store']);
    Route::get('/coupons/{id}', [AdminCouponController::class, 'show']);
    Route::put('/coupons/{id}', [AdminCouponController::class, 'update']);
    Route::delete('/coupons/{id}', [AdminCouponController::class, 'destroy']);
    Route::post('/coupons/{id}/toggle', [AdminCouponController::class, 'toggleStatus']);
    
    // Settings
    Route::get('/settings', [AdminSettingsController::class, 'index']);
    Route::post('/settings', [AdminSettingsController::class, 'update']);
    
    // System
    Route::post('/system/clear-cache', [AdminSystemController::class, 'clearCache']);
    Route::post('/system/backup', [AdminSystemController::class, 'createBackup']);
    Route::get('/system/backups', [AdminSystemController::class, 'listBackups']);
    Route::post('/system/optimize', [AdminSystemController::class, 'optimize']);
});

// Contact Routes
Route::post('/contact/send', [ContactController::class, 'send']);
Route::middleware(['auth:sanctum', 'admin'])->prefix('admin')->group(function () {
    Route::get('/contact/messages', [ContactController::class, 'getMessages']);
    Route::get('/contact/messages/{id}', [ContactController::class, 'getMessage']);
    Route::post('/contact/messages/{id}/reply', [ContactController::class, 'reply']);
    Route::post('/contact/messages/{id}/read', [ContactController::class, 'markAsRead']);
    Route::delete('/contact/messages/{id}', [ContactController::class, 'delete']);
});