<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{    /**
     * Show the admin dashboard
     */
    public function index()
    {        // Get latest orders
        $latestOrders = Order::with('user')
            ->withCount('items as items_count')
            ->orderBy('order_date', 'desc')
            ->take(5)
            ->get()
            ->map(function ($order) {
                // Add status color for badges - updated with better styling
                $statusColors = [
                    'pending' => 'warning',
                    'processing' => 'info',
                    'shipped' => 'purple',
                    'delivered' => 'success',
                    'cancelled' => 'danger',
                    'refunded' => 'secondary'
                ];
                $normalizedStatus = strtolower($order->status);
                $order->status_color = $statusColors[$normalizedStatus] ?? 'secondary';
                return $order;
            });// Get sales by category with revenue-based percentages
        $categoryRevenue = DB::table('orders')
            ->join('order_items', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('product_categories', 'products.id', '=', 'product_categories.product_id')
            ->join('categories', 'product_categories.category_id', '=', 'categories.id')
            ->where('orders.status', '!=', 'Cancelled')
            ->where('orders.status', '!=', 'Refunded')
            ->select('categories.name', DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'))
            ->groupBy('categories.name')
            ->get();

        // Calculate total revenue for percentage calculation
        $totalRevenue = $categoryRevenue->sum('total_revenue');        // Group categories and calculate revenue totals first
        $groupedRevenue = [];
        foreach ($categoryRevenue as $item) {
            $key = strtolower($item->name);
            if (str_contains($key, 'cloth') || str_contains($key, 'shirt') || str_contains($key, 'pant') || str_contains($key, 'dress') || str_contains($key, 'fashion') || str_contains($key, 'suit')) {
                $category = 'clothing';
            } elseif (str_contains($key, 'access') || str_contains($key, 'jewel') || str_contains($key, 'watch') || str_contains($key, 'bag') || str_contains($key, 'belt')) {
                $category = 'accessories';
            } elseif (str_contains($key, 'shoe') || str_contains($key, 'footwear') || str_contains($key, 'boot') || str_contains($key, 'sandal') || str_contains($key, 'sneaker')) {
                $category = 'footwear';
            } elseif (str_contains($key, 'electron') || str_contains($key, 'phone') || str_contains($key, 'computer') || str_contains($key, 'gadget') || str_contains($key, 'laptop')) {
                $category = 'electronics';
            } elseif (str_contains($key, 'home') || str_contains($key, 'decor') || str_contains($key, 'furniture') || str_contains($key, 'decoration')) {
                $category = 'home & decor';
            } elseif (str_contains($key, 'toy') || str_contains($key, 'game') || str_contains($key, 'children')) {
                $category = 'toys & games';
            } elseif (str_contains($key, 'book') || str_contains($key, 'education') || str_contains($key, 'learn')) {
                $category = 'books & education';
            } elseif (str_contains($key, 'pet') || str_contains($key, 'animal')) {
                $category = 'pet supplies';
            } else {
                $category = 'other';
            }
            
            if (!isset($groupedRevenue[$category])) {
                $groupedRevenue[$category] = 0;
            }
            $groupedRevenue[$category] += $item->total_revenue;
        }

        // Calculate percentages from grouped totals
        $salesByCategory = [];
        if ($totalRevenue > 0) {
            foreach ($groupedRevenue as $category => $revenue) {
                $percentage = ($revenue / $totalRevenue) * 100;
                $salesByCategory[$category] = round($percentage, 1);
            }
            
            // Ensure percentages add up to 100% by adjusting the largest category
            $totalPercentage = array_sum($salesByCategory);
            if ($totalPercentage != 100) {
                $adjustment = 100 - $totalPercentage;
                $largestCategory = array_search(max($salesByCategory), $salesByCategory);
                $salesByCategory[$largestCategory] += $adjustment;
                $salesByCategory[$largestCategory] = round($salesByCategory[$largestCategory], 1);
            }
            
            // Sort by percentage (highest first)
            arsort($salesByCategory);
        }// Monthly revenue
        $startOfYear = Carbon::now()->startOfYear();
        $endOfYear = Carbon::now()->endOfYear();
        
        $revenueByMonth = DB::table('orders')
            ->where('status', '!=', 'Cancelled')
            ->where('status', '!=', 'Refunded')
            ->whereBetween('order_date', [$startOfYear, $endOfYear])
            ->select(DB::raw('MONTH(order_date) as month'), DB::raw('SUM(total_amount) as revenue'))
            ->groupBy('month')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->month => $item->revenue];
            })
            ->toArray();
        
        // Fill in missing months
        for ($i = 1; $i <= 12; $i++) {
            if (!isset($revenueByMonth[$i])) {
                $revenueByMonth[$i] = 0;
            }
        }
        ksort($revenueByMonth);        // Calculate total and change statistics
        $totalSales = Order::where('status', '!=', 'Cancelled')
            ->where('status', '!=', 'Refunded')
            ->sum('total_amount');
            
        $lastMonthRevenue = Order::where('status', '!=', 'Cancelled')
            ->where('status', '!=', 'Refunded')
            ->whereBetween('order_date', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
            ->sum('total_amount');
        
        $prevMonthRevenue = Order::where('status', '!=', 'Cancelled')
            ->where('status', '!=', 'Refunded')
            ->whereBetween('order_date', [Carbon::now()->subMonths(2)->startOfMonth(), Carbon::now()->subMonths(2)->endOfMonth()])
            ->sum('total_amount');
        
        $salesGrowth = $prevMonthRevenue > 0 
            ? round((($lastMonthRevenue - $prevMonthRevenue) / $prevMonthRevenue) * 100, 2)
            : 0;

        // Order statistics
        $totalOrders = Order::count();
          $lastMonthOrders = Order::whereBetween('order_date', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
            ->count();
        
        $prevMonthOrders = Order::whereBetween('order_date', [Carbon::now()->subMonths(2)->startOfMonth(), Carbon::now()->subMonths(2)->endOfMonth()])
            ->count();
        
        $ordersChange = $prevMonthOrders > 0 
            ? round((($lastMonthOrders - $prevMonthOrders) / $prevMonthOrders) * 100, 2) 
            : 0;

        // Customer statistics
        $totalCustomers = User::count();
        
        $lastMonthCustomers = User::whereBetween('created_at', [Carbon::now()->subMonth()->startOfMonth(), Carbon::now()->subMonth()->endOfMonth()])
            ->count();
        
        $prevMonthCustomers = User::whereBetween('created_at', [Carbon::now()->subMonths(2)->startOfMonth(), Carbon::now()->subMonths(2)->endOfMonth()])
            ->count();
        
        $customersChange = $prevMonthCustomers > 0 
            ? round((($lastMonthCustomers - $prevMonthCustomers) / $prevMonthCustomers) * 100, 2) 
            : 0;        // Product statistics
        $totalProducts = Product::count();
        $lowStockCount = DB::table('products')
            ->join('inventories', 'products.id', '=', 'inventories.product_id')
            ->where('inventories.quantity', '<', 5)
            ->where('products.status', '=', 'active')
            ->count();
            
        // Get low stock products for the table display
        $lowStockProducts = DB::table('products')
            ->join('inventories', 'products.id', '=', 'inventories.product_id')
            ->select(
                'products.id',
                'products.name',
                'inventories.quantity as stock_quantity',
                'inventories.low_stock_threshold as stock_threshold'
            )
            ->where('inventories.quantity', '<', 5)
            ->where('products.status', '=', 'active')
            ->orderBy('inventories.quantity')
            ->limit(10)
            ->get();
              // Get orders by status
        $ordersByStatus = DB::table('orders')
            ->select('status', DB::raw('count(*) as count'))
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->get();

        // Get top selling products
        $topProducts = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->where('orders.status', '!=', 'Cancelled')
            ->where('orders.status', '!=', 'Refunded')
            ->select(
                'products.id',
                'products.name',
                'products.price',
                DB::raw('SUM(order_items.quantity) as sales_count'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue')
            )
            ->groupBy('products.id', 'products.name', 'products.price')
            ->orderBy('total_revenue', 'desc')
            ->limit(5)
            ->get();// Assign variable names for the view
        $ordersGrowth = $ordersChange;
        $customersGrowth = $customersChange;
        $recentOrders = $latestOrders;  // Make the variable available with both names
          // Prepare chart data
        $salesChartData = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'data' => array_values($revenueByMonth)
        ];
          // Prepare category chart data with fallback for empty data
        if (empty($salesByCategory) || array_sum($salesByCategory) == 0) {
            $categoriesChartData = [
                'labels' => ['No Sales Data'],
                'data' => [100]
            ];
        } else {
            $categoriesChartData = [
                'labels' => array_keys($salesByCategory),
                'data' => array_values($salesByCategory)
            ];
        }return view('admin.dashboard.index', compact(
            'latestOrders',
            'recentOrders',  // Pass both variables for flexibility
            'salesByCategory',
            'revenueByMonth',
            'totalSales',
            'salesGrowth',
            'totalOrders',
            'ordersGrowth',
            'totalCustomers',
            'customersGrowth',
            'totalProducts',
            'lowStockCount',
            'lowStockProducts',  // Add the low stock products for the table
            'ordersByStatus',
            'salesChartData',
            'categoriesChartData',
            'topProducts'  // Add top products data
        ))->with([
            'totalRevenue' => $totalSales,  // Pass total sales as total revenue
            'activeCustomers' => $totalCustomers  // Pass total customers as active customers for now
        ]);
    }
}
