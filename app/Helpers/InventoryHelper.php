<?php

namespace App\Helpers;

class InventoryHelper
{
    /**
     * Safely check if a product is in stock
     *
     * @param \App\Models\Product $product
     * @return bool
     */
    public static function isInStock($product)
    {
        if (!$product || !isset($product->inventory)) {
            return false;
        }
        
        // If the inventory relation is already loaded
        if ($product->relationLoaded('inventory')) {
            return $product->inventory->isInStock();
        }
        
        // Load the inventory relation if not loaded
        return $product->inventory()->exists() && 
               $product->inventory()->sum('quantity') > 0;
    }
    
    /**
     * Get the stock quantity of a product
     *
     * @param \App\Models\Product $product
     * @return int
     */
    public static function getStockQuantity($product)
    {
        if (!$product || !isset($product->inventory)) {
            return 0;
        }
        
        // If the inventory relation is already loaded
        if ($product->relationLoaded('inventory')) {
            return $product->inventory->safeQuantity();
        }
        
        // Load and get the quantity
        return (int) $product->inventory()->sum('quantity');
    }
    
    /**
     * Update product with stock information
     *
     * @param \App\Models\Product $product
     * @return \App\Models\Product
     */
    public static function updateProductStockInfo($product)
    {
        if (!$product) {
            return $product;
        }
        
        // Set stock info on the product
        $product->in_stock = self::isInStock($product);
        $product->stock_qty = self::getStockQuantity($product);
        
        return $product;
    }
}
