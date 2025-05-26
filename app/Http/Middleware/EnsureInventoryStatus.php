<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureInventoryStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);
        
        // Only process view responses
        if (!$response instanceof \Illuminate\View\View) {
            return $response;
        }
        
        $data = $response->getData();
        
        // Process products in different formats that might be in the view data
        foreach ($data as $key => $value) {
            // Single product
            if ($key === 'product' && !empty($value) && method_exists($value, 'getAttribute')) {
                $value->in_stock = (bool)$value->in_stock;
                $value->stock_qty = (int)$value->stock_qty;
            }
            
            // Collections of products
            elseif (in_array($key, ['products', 'featuredProducts', 'newArrivals', 'bestSellers', 'relatedProducts']) && 
                   !empty($value) && method_exists($value, 'map')) {
                $value->map(function($product) {
                    if (method_exists($product, 'getAttribute')) {
                        $product->in_stock = (bool)$product->in_stock;
                        $product->stock_qty = (int)$product->stock_qty;
                    }
                    return $product;
                });
            }
        }
        
        return $response;
    }
}
