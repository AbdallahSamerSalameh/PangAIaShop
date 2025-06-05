<?php

namespace App\Http\Controllers\Frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Inventory;

class DiagnosticController extends Controller
{
    /**
     * Display diagnostic information about inventory.
     *
     * @return \Illuminate\Http\Response
     */
    public function checkInventory()
    {
        // Reset opcache to ensure our changes are loaded
        if (function_exists('opcache_reset')) {
            opcache_reset();
        }
        
        // Get products with inventory
        $products = Product::with('inventory')
                        ->where('status', 'active')
                        ->take(10)
                        ->get();
        
        $results = [];
          foreach ($products as $product) {
            $inventory = $product->inventory;
            $quantity = $inventory ? $inventory->quantity : null;
            
            $results[] = [
                'product_id' => $product->id,
                'product_name' => $product->name,
                'raw_quantity' => $quantity,
                'quantity_type' => gettype($quantity),
                'quantity_as_int' => (int)$quantity,                'in_stock_via_attribute' => $product->in_stock,
                'stock_qty_via_attribute' => $product->stock_qty,
                'manual_check' => ($quantity > 0) ? 'true' : 'false',
                'inventory_id' => $inventory ? $inventory->id : null,
                'inventory_exists' => $product->inventory ? 'true' : 'false'
            ];
        }
        
        return view('frontend.pages.diagnostic', [
            'results' => $results,
            'raw_data' => $products
        ]);
    }
}
