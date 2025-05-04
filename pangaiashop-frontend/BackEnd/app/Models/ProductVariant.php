<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CascadeSoftDeletes;

class ProductVariant extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $fillable = [
        'product_id',
        'sku',
        'name',
        'price_adjustment',
        'attributes',
        'image_url'
    ];

    protected $casts = [
        'attributes' => 'array',
        'price_adjustment' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }
    
    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }
    
    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }
    
    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }
    
    public function priceHistory()
    {
        return $this->hasMany(PriceHistory::class);
    }

    /**
     * Define which relationships should cascade on delete.
     *
     * @return array
     */
    public function getCascadeDeletes(): array
    {
        return [
            'inventory',
            'cartItems',
            'wishlistItems',
            'priceHistory'
        ];
    }

    /*
    protected static function boot()
    {
        parent::boot();

        // When a product variant is soft deleted, cascade to related entities
        static::deleting(function($variant) {
            // Only cascade if this is a soft delete operation
            if (!$variant->isForceDeleting()) {
                // Cascade soft delete to related entities
                if ($variant->inventory) {
                    $variant->inventory->delete();
                }
                
                $variant->cartItems()->each(function($cartItem) {
                    $cartItem->delete();
                });
                
                $variant->wishlistItems()->each(function($wishlistItem) {
                    $wishlistItem->delete();
                });
                
                $variant->priceHistory()->each(function($priceHistory) {
                    $priceHistory->delete();
                });
                
                // We don't delete order items as they represent completed orders
                // with financial implications
            }
        });

        // When a product variant is restored, cascade restore to related entities
        static::restored(function($variant) {
            // Restore related entities that were soft deleted with this variant
            if ($variant->inventory()->withTrashed()->exists()) {
                $variant->inventory()->withTrashed()->restore();
            }
            
            $variant->cartItems()->onlyTrashed()->each(function($cartItem) {
                $cartItem->restore();
            });
            
            $variant->wishlistItems()->onlyTrashed()->each(function($wishlistItem) {
                $wishlistItem->restore();
            });
            
            $variant->priceHistory()->onlyTrashed()->each(function($priceHistory) {
                $priceHistory->restore();
            });
        });
    }
    */
}
