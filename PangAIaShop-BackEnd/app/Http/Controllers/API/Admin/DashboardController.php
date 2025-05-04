<?php

namespace App\Http\Controllers\API\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Get dashboard statistics and data.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            // Calculate total sales (revenue)
            $totalSales = Order::where('status', '!=', 'cancelled')
                ->sum('total_amount');

            // Count total orders
            $totalOrders = Order::count();

            // Count products
            $totalProducts = Product::count();

            // Count customers (users)
            $totalCustomers = User::count();

            // Count categories
            $totalCategories = Category::count();

            // Get counts of orders by status
            $ordersByStatus = Order::select('status', DB::raw('count(*) as count'))
                ->groupBy('status')
                ->get()
                ->pluck('count', 'status')
                ->toArray();

            // Get the count of returned orders (case insensitive)
            $returnedOrdersCount = Order::whereRaw('LOWER(status) = ?', ['returned'])->count();
            
            // Format the response
            $stats = [
                'totalSales' => round($totalSales, 2),
                'totalOrders' => $totalOrders,
                'totalProducts' => $totalProducts,
                'totalCustomers' => $totalCustomers,
                'totalCategories' => $totalCategories,
                'pendingOrders' => $ordersByStatus['pending'] ?? 0,
                'processingOrders' => $ordersByStatus['processing'] ?? 0,
                'shippedOrders' => $ordersByStatus['shipped'] ?? 0,
                'deliveredOrders' => $ordersByStatus['delivered'] ?? 0,
                'cancelledOrders' => $ordersByStatus['cancelled'] ?? 0,
                'returnedOrders' => $returnedOrdersCount,
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load dashboard data',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get sales report data for charts.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function salesReport(Request $request)
    {
        try {
            // Get parameters
            $startDate = $request->input('start_date', Carbon::now()->subDays(30)->toDateString());
            $endDate = $request->input('end_date', Carbon::now()->toDateString());
            $groupBy = $request->input('group_by', 'day'); // day, week, month, year

            // Parse dates
            $startDate = Carbon::parse($startDate)->startOfDay();
            $endDate = Carbon::parse($endDate)->endOfDay();

            // Build query based on grouping
            switch ($groupBy) {
                case 'week':
                    $dateFormat = 'Week %W, %Y';
                    $selectRaw = "DATE_FORMAT(created_at, '%X-%V') as date_group";
                    break;
                case 'month':
                    $dateFormat = '%b %Y';
                    $selectRaw = "DATE_FORMAT(created_at, '%Y-%m') as date_group";
                    break;
                case 'year':
                    $dateFormat = '%Y';
                    $selectRaw = "DATE_FORMAT(created_at, '%Y') as date_group";
                    break;
                default: // day
                    $dateFormat = '%b %d, %Y';
                    $selectRaw = "DATE(created_at) as date_group";
                    break;
            }

            // Get sales data grouped by the specified interval
            $salesData = Order::select(DB::raw($selectRaw), DB::raw('SUM(total_amount) as total_sales'))
                ->where('status', '!=', 'cancelled')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('date_group')
                ->orderBy('date_group')
                ->get();

            // Format data for chart
            $labels = [];
            $data = [];

            // Create a period to iterate through for continuous dates
            $period = new \DatePeriod(
                $startDate,
                new \DateInterval($this->getDateIntervalByGroupBy($groupBy)),
                $endDate
            );

            // Initialize data with zeros
            foreach ($period as $date) {
                $formattedDate = $date->format($this->getDateFormatByGroupBy($groupBy));
                $labels[] = $formattedDate;
                $data[] = 0;
            }

            // Fill in actual data
            foreach ($salesData as $item) {
                $date = null;
                
                if ($groupBy === 'day') {
                    $date = Carbon::parse($item->date_group);
                } elseif ($groupBy === 'week') {
                    list($year, $week) = explode('-', $item->date_group);
                    $date = Carbon::now()->setISODate($year, $week);
                } elseif ($groupBy === 'month') {
                    list($year, $month) = explode('-', $item->date_group);
                    $date = Carbon::createFromDate($year, $month, 1);
                } elseif ($groupBy === 'year') {
                    $date = Carbon::createFromDate($item->date_group, 1, 1);
                }
                
                if ($date) {
                    $formattedDate = $date->format($this->getDateFormatByGroupBy($groupBy));
                    $index = array_search($formattedDate, $labels);
                    if ($index !== false) {
                        $data[$index] = round($item->total_sales, 2);
                    }
                }
            }

            return response()->json([
                'success' => true,
                'start_date' => $startDate->toDateString(),
                'end_date' => $endDate->toDateString(),
                'group_by' => $groupBy,
                'labels' => $labels,
                'data' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to generate sales report',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get recent orders for dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function getRecentOrders()
    {
        try {
            $orders = Order::with('user')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get()
                ->map(function ($order) {
                    return [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'customer_name' => $order->user ? $order->user->name : 'Guest',
                        'total_amount' => round($order->total_amount, 2),
                        'status' => $order->status,
                        'created_at' => $order->created_at
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $orders
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load recent orders',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get low stock products for dashboard.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function getLowStockProducts(Request $request)
    {
        try {
            $limit = $request->input('limit', 5);
            
            // Get products with low stock by joining with inventory table
            $products = Product::join('inventories', 'products.id', '=', 'inventories.product_id')
                ->where('inventories.quantity', '<', 5)
                ->where('products.status', 'active')
                ->orderBy('inventories.quantity', 'asc')
                ->limit($limit)
                ->select(
                    'products.id',
                    'products.name',
                    'products.price',
                    'inventories.quantity as stock_quantity',
                    'inventories.low_stock_threshold as stock_threshold'
                )
                ->get();
                
            // Attach additional information for display
            foreach ($products as $product) {
                // Get first category for display purposes
                $category = DB::table('product_categories')
                    ->join('categories', 'product_categories.category_id', '=', 'categories.id')
                    ->where('product_categories.product_id', $product->id)
                    ->select('categories.name')
                    ->first();
                    
                $product->category_name = $category ? $category->name : 'N/A';
            }

            return response()->json([
                'success' => true,
                'data' => $products
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load low stock products',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get date interval format based on grouping.
     *
     * @param string $groupBy
     * @return string
     */
    private function getDateIntervalByGroupBy($groupBy)
    {
        switch ($groupBy) {
            case 'week':
                return 'P1W';
            case 'month':
                return 'P1M';
            case 'year':
                return 'P1Y';
            default: // day
                return 'P1D';
        }
    }

    /**
     * Get date format based on grouping.
     *
     * @param string $groupBy
     * @return string
     */
    private function getDateFormatByGroupBy($groupBy)
    {
        switch ($groupBy) {
            case 'week':
                return 'W, Y';
            case 'month':
                return 'M Y';
            case 'year':
                return 'Y';
            default: // day
                return 'M d, Y';
        }
    }
}