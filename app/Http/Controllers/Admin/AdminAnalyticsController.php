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
        $validStatuses = ['paid', 'processing', 'shipped', 'delivered'];

        $totalRevenue = Order::whereIn('status', $validStatuses)->sum('total');
        $previousRevenue = Order::whereIn('status', $validStatuses)
            ->where('created_at', '<', now()->subDays(30))->sum('total');
        $revenueGrowth = $previousRevenue > 0
            ? round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100, 1) : 0;

        $totalOrders    = Order::count();
        $previousOrders = Order::where('created_at', '<', now()->subDays(30))->count();
        $ordersGrowth   = $previousOrders > 0
            ? round((($totalOrders - $previousOrders) / $previousOrders) * 100, 1) : 0;

        $totalCustomers    = User::where('role', 'customer')->count();
        $previousCustomers = User::where('role', 'customer')
            ->where('created_at', '<', now()->subDays(30))->count();
        $customersGrowth   = $previousCustomers > 0
            ? round((($totalCustomers - $previousCustomers) / $previousCustomers) * 100, 1) : 0;

        $totalProducts = Product::count();

        $revenueByDay = Order::where('created_at', '>=', now()->subDays(30))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date', 'asc')
            ->get();

        $ordersByStatus = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($r) => [$r->status => $r->count]);

        $categorySales = Category::select('categories.id', 'categories.name')
            ->selectRaw('COALESCE(SUM(products.sales_count), 0) as total_sales')
            ->leftJoin('products', 'categories.id', '=', 'products.category_id')
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_sales', 'desc')
            ->get();

        $topCategories = [];
        $otherSales    = 0;

        foreach ($categorySales as $i => $cat) {
            if ($i < 5) {
                $topCategories[] = [
                    'name'  => $cat->name,
                    'value' => (int) $cat->total_sales,
                ];
            } else {
                $otherSales += (int) $cat->total_sales;
            }
        }

        if ($otherSales > 0) {
            $topCategories[] = ['name' => 'Others', 'value' => $otherSales];
        }

        $allZero = collect($topCategories)->every(fn($c) => $c['value'] === 0);
        if ($allZero) {
            $topCategories = Category::withCount('products')
                ->orderBy('products_count', 'desc')
                ->limit(6)
                ->get()
                ->map(fn($c) => ['name' => $c->name, 'value' => $c->products_count])
                ->toArray();
        }

        $topProducts = Product::orderBy('sales_count', 'desc')
            ->limit(8)
            ->get(['id', 'name', 'price', 'stock', 'sales_count', 'category_id']);

        $recentOrders = Order::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(8)
            ->get(['id', 'order_number', 'user_id', 'status', 'total', 'created_at']);

        return response()->json([
            'total_revenue'    => (float) $totalRevenue,
            'revenue_growth'   => $revenueGrowth >= 0 ? "+{$revenueGrowth}%" : "{$revenueGrowth}%",
            'total_orders'     => $totalOrders,
            'orders_growth'    => $ordersGrowth >= 0 ? "+{$ordersGrowth}%" : "{$ordersGrowth}%",
            'total_customers'  => $totalCustomers,
            'customers_growth' => $customersGrowth >= 0 ? "+{$customersGrowth}%" : "{$customersGrowth}%",
            'total_products'   => $totalProducts,
            'products_growth'  => '+0%',
            'revenue_by_day'   => $revenueByDay,
            'orders_by_status' => $ordersByStatus,
            'categories_data'  => $topCategories,
            'top_products'     => $topProducts,
            'recent_orders'    => $recentOrders,
        ]);
    }

    public function revenue()
    {
        $totalRevenue       = Order::whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])->sum('total');
        $averageOrderValue  = Order::whereIn('status', ['paid', 'processing', 'shipped', 'delivered'])->avg('total');
        $totalOrders        = Order::count();

        $revenueByDay = Order::where('created_at', '>=', now()->subDays(30))
            ->select(DB::raw('DATE(created_at) as date'), DB::raw('SUM(total) as revenue'))
            ->groupBy('date')->orderBy('date', 'asc')->get();

        return response()->json([
            'total_revenue'       => $totalRevenue,
            'average_order_value' => $averageOrderValue,
            'total_orders'        => $totalOrders,
            'revenue_by_day'      => $revenueByDay,
        ]);
    }

    public function topProducts()
    {
        $products = Product::orderBy('sales_count', 'desc')
            ->limit(10)->get(['id', 'name', 'price', 'stock', 'sales_count']);
        return response()->json($products);
    }

    public function customerStats()
    {
        $totalCustomers = User::where('role', 'customer')->count();
        $newCustomers   = User::where('role', 'customer')
            ->where('created_at', '>=', now()->subDays(30))->count();
        $repeatCustomers = User::where('role', 'customer')
            ->whereHas('orders', fn($q) => $q->whereIn('status', ['paid', 'delivered']), '>=', 2)
            ->count();

        return response()->json([
            'total_customers'   => $totalCustomers,
            'new_customers'     => $newCustomers,
            'repeat_customers'  => $repeatCustomers,
        ]);
    }
}