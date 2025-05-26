<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Inventory;

class MacroServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
      /**
     * Bootstrap services.
     *
     * @return void
     */    public function boot()
    {
        // Use regular functions instead of closures to avoid IDE issues with $this context
        $safeQuantityFunc = function() {
            // No need for PHPDoc, PHP will handle $this correctly
            // If collection is empty, return 0
            if (!$this || !method_exists($this, 'isEmpty') || $this->isEmpty()) {
                return 0;
            }
            
            // Get first inventory item
            $item = method_exists($this, 'first') ? $this->first() : null;
            
            // If item exists and has quantity property
            if ($item instanceof \App\Models\Inventory && property_exists($item, 'quantity')) {
                $value = $item->quantity;
                // Ensure we get an integer
                return is_numeric($value) ? (int)$value : 0;
            } elseif ($item && isset($item->quantity)) {
                $value = $item->quantity;
                // Ensure we get an integer
                return is_numeric($value) ? (int)$value : 0;
            }
            
            return 0;
        };
        
        // Add the macro with our function
        \Illuminate\Database\Eloquent\Collection::macro('safeQuantity', $safeQuantityFunc);
        
        // Function for checking if in stock
        $isInStockFunc = function() {
            // If collection is empty, not in stock
            if (!$this || !method_exists($this, 'isEmpty') || $this->isEmpty()) {
                return false;
            }
            
            // Direct implementation, don't rely on safeQuantity to avoid circular reference
            $item = method_exists($this, 'first') ? $this->first() : null;
            
            if ($item && isset($item->quantity)) {
                return (int)$item->quantity > 0;
            }
            
            return false;
        };
        
        // Add the macro with our function
        \Illuminate\Database\Eloquent\Collection::macro('isInStock', $isInStockFunc);
    }
}
