<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CartItem extends Model
{
    use HasFactory, SoftDeletes;
    
    protected $fillable = [
        'cart_id',
        'product_id',
        'variant_id',
        'quantity',
        'unit_price'
    ];
    
    protected $casts = [
        'unit_price' => 'decimal:2',
        'quantity' => 'integer'
    ];    /**
     * The "booted" method of the model.
     *
     * @return void
     */    protected static function booted()
    {
        // Only handle zero quantity items when they are being updated, not created
        static::updating(function ($cartItem) {
            // Only act on items that already exist in the database and are being updated
            if ($cartItem->exists && $cartItem->isDirty('quantity') && $cartItem->quantity <= 0) {
                // Schedule for deletion but allow save to proceed
                $cartItem->forceDelete();
                return true;
            }
            return true;
        });
    }
    
    public function cart()
    {
        return $this->belongsTo(Cart::class)->withTrashed();
    }
    
    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
    
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }
      /**
     * Get the actual price to use for this cart item, considering sale prices
     * 
     * @return float
     */
    public function getPrice()
    {
        // If this is a variant, check variant price first
        if ($this->variant_id) {
            $variant = $this->variant;
            if ($variant && $variant->price) {
                // Even for variants, prefer the unit_price if it was specifically set at cart creation
                // This allows for sale prices to be honored even for variants
                if ($this->unit_price && $this->unit_price < $variant->price) {
                    return $this->unit_price;
                }
                
                return $variant->price;
            }
        }
        
        $product = $this->product;
        
        // If unit_price is already set and it's the sale price, use it
        if ($this->unit_price) {
            // If there's a sale price and the unit_price matches it, return unit_price
            if ($product->sale_price && $product->sale_price > 0 && $this->unit_price <= $product->sale_price) {
                return $this->unit_price;
            }
            // If there's no sale price, use the unit_price
            if (!$product->sale_price || $product->sale_price == 0) {
                return $this->unit_price;
            }
        }
        
        // Use product sale price if available, otherwise use regular price
        if ($product->sale_price && $product->sale_price > 0) {
            return $product->sale_price;
        }
        
        return $product->price;
    }
    
    /**
     * Calculate the subtotal for this cart item
     * 
     * @return float
     */
    public function getSubtotal()
    {
        return $this->quantity * $this->getPrice();
    }
}