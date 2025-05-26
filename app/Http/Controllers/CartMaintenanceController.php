<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cart;
use App\Models\CartItem;
use Illuminate\Support\Facades\DB;

class CartMaintenanceController extends Controller
{
    /**
     * Fix zero quantity cart items
     * 
     * @param Request $request
     * @return \Illuminate\Http\Response
     */
    public function fixZeroQuantityItems(Request $request)
    {
        // Check for maintenance token to ensure only authorized calls
        if ($request->token !== env('MAINTENANCE_TOKEN', 'secure_maintenance_token')) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized access'
            ], 401);
        }

        // Start fixing the cart issues
        $results = [
            'step1' => 0,
            'step2' => 0,
            'step3' => 0,
            'step4' => 0
        ];

        // Step 1: Find and remove any cart items with zero quantity
        $results['step1'] = CartItem::where('quantity', 0)->forceDelete();

        // Step 2: Find and mark as deleted any cart entries with zero quantity
        $results['step2'] = Cart::where('quantity', 0)->update(['deleted_at' => now()]);

        // Step 3: Specifically fix the H&M product (product_id = 1)
        $h_mCartItemsCount = CartItem::where('product_id', 1)->forceDelete();
        $h_mCartsCount = Cart::where('product_id', 1)->update(['deleted_at' => now()]);
        $results['step3'] = $h_mCartItemsCount + $h_mCartsCount;

        // Step 4: Fix any specific problematic entries that might still be lingering
        DB::statement("UPDATE cart_items SET deleted_at = NOW() WHERE quantity = 0 AND deleted_at IS NULL");
        DB::statement("UPDATE carts SET deleted_at = NOW() WHERE quantity = 0 AND deleted_at IS NULL");
        $results['step4'] = 1; // Can't count affected rows with DB::statement

        return response()->json([
            'success' => true,
            'message' => 'Zero quantity cart items fix completed successfully!',
            'results' => $results
        ]);
    }
}
