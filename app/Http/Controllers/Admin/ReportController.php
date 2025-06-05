<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Traits\AuditLoggable;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    use AuditLoggable;
      /**
     * Display sales reports.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function sales(Request $request)
    {
        $period = $request->input('period', 'monthly');
        $startDate = $request->input('start_date', Carbon::now()->subMonths(6)->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
          // Log the report access
        $this->logCustomAction("view_sales_report", null, "Accessed sales report (period: {$period}, from: {$startDate} to: {$endDate})");
        
        $salesData = $this->getSalesData($period, $startDate, $endDate);
        $topProducts = $this->getTopSellingProducts($startDate, $endDate);
        $salesByCategory = $this->getSalesByCategory($startDate, $endDate);
        $paymentMethods = $this->getSalesByPaymentMethod($startDate, $endDate);
        
        return view('admin.reports.sales', compact(
            'period',
            'startDate',
            'endDate',
            'salesData',
            'topProducts',
            'salesByCategory',
            'paymentMethods'
        ));
    }
      /**
     * Display inventory reports.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function inventory(Request $request)
    {
        $stockStatus = $request->input('stock_status', 'all');
        
        // Log the report access
        $this->logCustomAction("view_inventory_report", null, "Accessed inventory report (stock status filter: {$stockStatus})");
        
        $inventoryData = Product::with('inventory', 'category')
            ->when($stockStatus === 'in_stock', function ($query) {
                return $query->whereHas('inventory', function($q) {
                    $q->where('quantity', '>', 0);
                });
            })
            ->when($stockStatus === 'out_of_stock', function ($query) {
                return $query->whereHas('inventory', function($q) {
                    $q->where('quantity', '<=', 0);
                })->orDoesntHave('inventory');
            })
            ->when($stockStatus === 'low_stock', function ($query) {
                return $query->whereHas('inventory', function($q) {
                    $q->whereColumn('quantity', '<=', 'low_stock_threshold')
                      ->where('quantity', '>', 0);
                });
            })
            ->get();
        
        $stockByCategory = $inventoryData->groupBy('category.name')
            ->map(function ($products) {
                return [
                    'total_products' => $products->count(),
                    'total_quantity' => $products->sum(function ($product) {
                        return optional($product->inventory)->quantity ?? 0;
                    }),
                    'out_of_stock' => $products->filter(function ($product) {
                        return optional($product->inventory)->quantity <= 0 || !$product->inventory;
                    })->count(),
                ];
            });
        
        return view('admin.reports.inventory', compact('inventoryData', 'stockByCategory', 'stockStatus'));
    }
      /**
     * Display customer reports.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\View\View
     */
    public function customers(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->subMonths(3)->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->format('Y-m-d'));
        
        // Log the report access
        $this->logCustomAction("view_customer_report", null, "Accessed customer report (from: {$startDate} to: {$endDate})");
        
        $topCustomers = User::withCount(['orders as total_orders' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }])
            ->withSum(['orders as total_spent' => function ($query) use ($startDate, $endDate) {
                $query->whereBetween('created_at', [$startDate, $endDate]);
            }], 'total_amount')
            ->having('total_orders', '>', 0)
            ->orderBy('total_spent', 'desc')
            ->take(10)
            ->get();
        
        $newCustomers = User::whereBetween('created_at', [$startDate, $endDate])
            ->count();
        
        $customerRetention = $this->getCustomerRetention($startDate, $endDate);
        
        $averageOrderValue = Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'Cancelled')
            ->avg('total_amount') ?? 0;
        
        return view('admin.reports.customers', compact(
            'topCustomers',
            'newCustomers',
            'customerRetention',
            'averageOrderValue',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Get sales data based on period
     *
     * @param  string  $period
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getSalesData($period, $startDate, $endDate)
    {
        $format = $period === 'daily' ? '%Y-%m-%d' : '%Y-%m';
        $groupByFormat = $period === 'daily' ? 'date' : 'month';

        return Order::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', '!=', 'Cancelled')
            ->select(
                DB::raw("DATE_FORMAT(created_at, '{$format}') as {$groupByFormat}"),
                DB::raw('SUM(total_amount) as revenue'),
                DB::raw('COUNT(*) as total_orders')
            )
            ->groupBy($groupByFormat)
            ->orderBy($groupByFormat)
            ->get();
    }

    /**
     * Get top selling products
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getTopSellingProducts($startDate, $endDate)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'Cancelled')
            ->select(
                'products.id',
                'products.name',
                DB::raw('SUM(order_items.quantity) as total_quantity'),
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue')
            )
            ->groupBy('products.id', 'products.name')
            ->orderBy('total_revenue', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * Get sales by category
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getSalesByCategory($startDate, $endDate)
    {
        return DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->join('product_category', 'products.id', '=', 'product_category.product_id')
            ->join('categories', 'product_category.category_id', '=', 'categories.id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'Cancelled')
            ->select(
                'categories.id',
                'categories.name',
                DB::raw('SUM(order_items.price * order_items.quantity) as total_revenue'),
                DB::raw('COUNT(DISTINCT orders.id) as order_count')
            )
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total_revenue', 'desc')
            ->get();
    }

    /**
     * Get sales by payment method
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return \Illuminate\Support\Collection
     */
    private function getSalesByPaymentMethod($startDate, $endDate)
    {
        return DB::table('orders')
            ->leftJoin('payments', 'orders.id', '=', 'payments.order_id')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->where('orders.status', '!=', 'Cancelled')
            ->select(
                'payments.payment_method',
                DB::raw('COUNT(*) as count'),
                DB::raw('SUM(orders.total_amount) as total_amount')
            )
            ->groupBy('payments.payment_method')
            ->get();
    }

    /**
     * Calculate customer retention rate
     *
     * @param  string  $startDate
     * @param  string  $endDate
     * @return array
     */
    private function getCustomerRetention($startDate, $endDate)
    {
        // Get customers who ordered in the period
        $customersInPeriod = Order::whereBetween('created_at', [$startDate, $endDate])
            ->select('user_id')
            ->distinct()
            ->pluck('user_id');
            
        // Get customers who ordered before the period
        $previousStartDate = Carbon::parse($startDate)->subMonths(3)->format('Y-m-d');
        $previousEndDate = Carbon::parse($startDate)->subDay()->format('Y-m-d');
        
        $customersInPreviousPeriod = Order::whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->select('user_id')
            ->distinct()
            ->pluck('user_id');
        
        // Calculate returning customers
        $returningCustomers = $customersInPeriod->intersect($customersInPreviousPeriod)->count();
        
        // Calculate retention rate
        $retentionRate = $customersInPreviousPeriod->count() > 0 
            ? ($returningCustomers / $customersInPreviousPeriod->count()) * 100 
            : 0;
            
        return [
            'previous_customers' => $customersInPreviousPeriod->count(),
            'returning_customers' => $returningCustomers,
            'retention_rate' => round($retentionRate, 2)
        ];
    }
}
