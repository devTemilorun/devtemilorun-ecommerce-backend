<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminAnalyticsController extends Controller
{
    public function dashboard()
    {
        // Total revenue
        $totalRevenue = Order::where('status', 'paid')->sum('total');
        $previousRevenue = Order::where('status', 'paid')
            ->where('created_at', '<', now()->subDays(30))
            ->sum('total');
        $revenueGrowth = $previousRevenue > 0 
            ? round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100, 1)
            : 0;
        
        // Total orders
        $totalOrders = Order::count();
        $previousOrders = Order::where('created_at', '<', now()->subDays(30))->count();
        $ordersGrowth = $previousOrders > 0 
            ? round((($totalOrders - $previousOrders) / $previousOrders) * 100, 1)
            : 0;
        
        // Total customers
        $totalCustomers = User::where('role', 'customer')->count();
        $previousCustomers = User::where('role', 'customer')
            ->where('created_at', '<', now()->subDays(30))
            ->count();
        $customersGrowth = $previousCustomers > 0 
            ? round((($totalCustomers - $previousCustomers) / $previousCustomers) * 100, 1)
            : 0;
        
        // Total products
        $totalProducts = Product::count();
        
        // Revenue by day for last 30 days
        $revenueByDay = Order::where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        // Category distribution - SORT BY SALES and LIMIT to top 5, rest as "Others"
        $categorySales = Category::select('categories.id', 'categories.name')
            ->selectRaw('COALESCE(SUM(products.sales_count), 0) as total_sales')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_sales', 'desc')
            ->get();
        
        // Prepare categories data with top 5 and others
        $topCategories = [];
        $otherSales = 0;
        $maxCategories = 5;
        
        foreach ($categorySales as $index => $category) {
            if ($index < $maxCategories) {
                $topCategories[] = [
                    'name' => $category->name,
                    'value' => (int)$category->total_sales,
                    'sales' => (int)$category->total_sales
                ];
            } else {
                $otherSales += (int)$category->total_sales;
            }
        }
        
        // Add "Others" category if there are more categories
        $categoriesData = $topCategories;
        if ($otherSales > 0) {
            $categoriesData[] = [
                'name' => 'Others',
                'value' => $otherSales,
                'sales' => $otherSales
            ];
        }
        
        // If no sales data, show top 5 categories by product count
        if (empty($categoriesData)) {
            $categoriesByProduct = Category::withCount('products')
                ->orderBy('products_count', 'desc')
                ->limit(6)
                ->get();
            
            $categoriesData = [];
            foreach ($categoriesByProduct as $index => $category) {
                if ($index < 5) {
                    $categoriesData[] = [
                        'name' => $category->name,
                        'value' => $category->products_count,
                        'sales' => $category->products_count
                    ];
                }
            }
        }
        
        // Top products by sales
        $topProducts = Product::orderBy('sales_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'price', 'stock', 'sales_count']);
        
        // Recent orders
        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
        
        return response()->json([
            'total_revenue' => $totalRevenue,
            'revenue_growth' => $revenueGrowth > 0 ? "+{$revenueGrowth}%" : "{$revenueGrowth}%",
            'total_orders' => $totalOrders,
            'orders_growth' => $ordersGrowth > 0 ? "+{$ordersGrowth}%" : "{$ordersGrowth}%",
            'total_customers' => $totalCustomers,
            'customers_growth' => $customersGrowth > 0 ? "+{$customersGrowth}%" : "{$customersGrowth}%",
            'total_products' => $totalProducts,
            'products_growth' => '+0%',
            'revenue_by_day' => $revenueByDay,
            'categories_data' => $categoriesData,
            'top_products' => $topProducts,
            'recent_orders' => $recentOrders,
        ]);
    }
    
    public function revenue()
    {
        $totalRevenue = Order::where('status', 'paid')->sum('total');
        $averageOrderValue = Order::where('status', 'paid')->avg('total');
        $totalOrders = Order::where('status', 'paid')->count();
        
        $revenueByDay = Order::where('status', 'paid')
            ->where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'))
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();
        
        return response()->json([
            'total_revenue' => $totalRevenue,
            'average_order_value' => $averageOrderValue,
            'total_orders' => $totalOrders,
            'revenue_by_day' => $revenueByDay,
        ]);
    }
    
    public function topProducts()
    {
        $products = Product::orderBy('sales_count', 'desc')
            ->limit(10)
            ->get(['id', 'name', 'price', 'stock', 'sales_count']);
        
        return response()->json($products);
    }
    
    public function customerStats()
    {
        $totalCustomers = User::where('role', 'customer')->count();
        $newCustomers = User::where('role', 'customer')
            ->where('created_at', '>=', now()->subDays(30))
            ->count();
        
        $repeatCustomers = User::where('role', 'customer')
            ->whereHas('orders', function ($query) {
                $query->where('status', 'paid');
            }, '>=', 2)
            ->count();
        
        return response()->json([
            'total_customers' => $totalCustomers,
            'new_customers' => $newCustomers,
            'repeat_customers' => $repeatCustomers,
        ]);
    }
}