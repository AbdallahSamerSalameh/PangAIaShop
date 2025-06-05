<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventory extends Model
{
    use HasFactory, SoftDeletes;

    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'variant_id',
        'quantity',
        'reserved_quantity',
        'location',
        'last_restocked',
        'low_stock_threshold',
        'updated_by'
    ];

    protected $casts = [
        'quantity' => 'integer',
        'reserved_quantity' => 'integer',
        'low_stock_threshold' => 'integer',
        'last_restocked' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];
    
    /**
     * Get the quantity attribute.
     * Ensuring it's always returned as integer.
     *
     * @param mixed $value
     * @return int
     */
    public function getQuantityAttribute($value)
    {
        // Debugging the raw value
        \Log::debug('Getting quantity attribute', [
            'inventory_id' => $this->id ?? 'new',
            'raw_value' => $value,
            'value_type' => gettype($value),
            'is_numeric' => is_numeric($value)
        ]);
        
        // Ensure quantity is always returned as an integer
        $result = is_numeric($value) ? intval($value) : 0;
        
        \Log::debug('Quantity after conversion', [
            'inventory_id' => $this->id ?? 'new',
            'result' => $result
        ]);
        
        return $result;
    }
    
    /**
     * Cast the quantity to integer when setting it too
     * 
     * @param mixed $value
     * @return void
     */
    public function setQuantityAttribute($value)
    {
        $this->attributes['quantity'] = is_numeric($value) ? intval($value) : 0;
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    // Inventory is a leaf node in our model hierarchy, no cascade needed

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        // When inventory is saved/updated, update the product in_stock status
        static::saved(function($inventory) {
            if ($inventory->product) {
                $inventory->product->updateStockStatus();
            }
        });
    }
    
    /**
     * Get available quantity (total - reserved)
     */
    public function getAvailableQuantityAttribute()
    {
        return max(0, $this->quantity - ($this->reserved_quantity ?? 0));
    }

    /**
     * Reserve inventory for cart items
     * 
     * @param int $quantity
     * @return bool
     */
    public function reserveQuantity($quantity)
    {
        if ($this->available_quantity >= $quantity) {
            $this->increment('reserved_quantity', $quantity);
            return true;
        }
        return false;
    }

    /**
     * Release reserved inventory (when cart items are removed or cart expires)
     * 
     * @param int $quantity
     * @return bool
     */
    public function releaseReservedQuantity($quantity)
    {
        $currentReserved = $this->reserved_quantity ?? 0;
        $toRelease = min($quantity, $currentReserved);
        
        if ($toRelease > 0) {
            $this->decrement('reserved_quantity', $toRelease);
            return true;
        }
        return false;
    }

    /**
     * Convert reserved quantity to actual sold quantity (when order is completed)
     * 
     * @param int $quantity
     * @return bool
     */
    public function commitReservedQuantity($quantity)
    {
        $currentReserved = $this->reserved_quantity ?? 0;
        $toCommit = min($quantity, $currentReserved);
        
        if ($toCommit > 0 && $this->quantity >= $toCommit) {
            $this->decrement('quantity', $toCommit);
            $this->decrement('reserved_quantity', $toCommit);
            return true;
        }
        return false;
    }
}
