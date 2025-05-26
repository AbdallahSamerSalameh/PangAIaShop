<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\User;
use App\Models\Product;
use App\Models\Category;
use App\Models\Payment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStats()
    {
        // Calculate various statistics for the dashboard
        $totalSales = Payment::where('status', 'completed')->sum('amount');
        $totalOrders = Order::count();
        $totalCustomers = User::count();
        $totalProducts = Product::count();
        
        // Recent orders (last 7 days)
        $recentOrdersCount = Order::where('created_at', '>=', Carbon::now()->subDays(7))->count();
        
        // Monthly sales
        $monthlySales = Payment::where('status', 'completed')
            ->where('created_at', '>=', Carbon::now()->startOfMonth())
            ->sum('amount');
            
        // Calculate order fulfillment rate
        $completedOrders = Order::where('status', 'completed')->count();
        $fulfillmentRate = $totalOrders > 0 ? round(($completedOrders / $totalOrders) * 100, 2) : 0;
        
        return response()->json([
            'totalSales' => $totalSales,
            'totalOrders' => $totalOrders,
            'totalCustomers' => $totalCustomers,
            'totalProducts' => $totalProducts,
            'recentOrdersCount' => $recentOrdersCount,
            'monthlySales' => $monthlySales,
            'fulfillmentRate' => $fulfillmentRate
        ]);
    }
    
    /**
     * Get sales data for charts
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getSalesChart(Request $request)
    {
        $period = $request->input('period', 'weekly');
        $startDate = null;
        $endDate = Carbon::now();
        $format = '';
        $groupBy = '';
        
        switch($period) {
            case 'weekly':
                $startDate = Carbon::now()->subDays(7);
                $format = 'Y-m-d';
                $groupBy = 'date';
                break;
            case 'monthly':
                $startDate = Carbon::now()->subDays(30);
                $format = 'Y-m-d';
                $groupBy = 'date';
                break;
            case 'yearly':
                $startDate = Carbon::now()->subMonths(12);
                $format = 'Y-m';
                $groupBy = 'month';
                break;
            default:
                $startDate = Carbon::now()->subDays(7);
                $format = 'Y-m-d';
                $groupBy = 'date';
        }
        
        $salesData = Payment::where('status', 'completed')
            ->where('created_at', '>=', $startDate)
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$format}') as {$groupBy}"),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy($groupBy)
            ->orderBy($groupBy)
            ->get();
            
        return response()->json($salesData);
    }
    
    /**
     * Get top selling products
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTopProducts()
    {
        $topProducts = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->select(
                'products.id',
                'products.name',
                'products.image',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_sales')
            )
            ->groupBy('products.id', 'products.name', 'products.image')
            ->orderBy('total_quantity', 'desc')
            ->limit(5)
            ->get();
            
        return response()->json($topProducts);
    }
    
    /**
     * Get sales by category
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategorySales()
    {
        $categorySales = DB::table('order_items')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('categories', 'products.category_id', '=', 'categories.id')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_sales')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_sales', 'desc')
            ->get();
            
        return response()->json($categorySales);
    }
    
    /**
     * Get recent orders
     *
     * @return \Illuminate\Http\JsonResponse
     */    public function getRecentOrders()
    {
        $recentOrders = Order::with(['user', 'orderItems.product'])
            ->orderBy('order_date', 'desc')
            ->limit(10)
            ->get();
            
        return response()->json($recentOrders);
    }
    
    /**
     * Get order status distribution
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getOrderStatusDistribution()
    {
        $statusDistribution = Order::select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();
            
        return response()->json($statusDistribution);
    }
    
    /**
     * Get customer growth data
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCustomerGrowth()
    {
        $customerGrowth = User::select(
                DB::raw("DATE_FORMAT(created_at, '%Y-%m') as month"),
                DB::raw('COUNT(*) as new_customers')
            )
            ->where('created_at', '>=', Carbon::now()->subMonths(12))
            ->groupBy('month')
            ->orderBy('month')
            ->get();
            
        return response()->json($customerGrowth);
    }
}