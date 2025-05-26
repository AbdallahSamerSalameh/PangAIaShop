<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CascadeSoftDeletes;

class Product extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $fillable = [
        'name',
        'description',
        'price',
        'sale_price',
        'sku',
        'vendor_id',
        'created_by',
        'updated_by',
        'status',
        'weight',
        'dimensions',
        'warranty_info',
        'return_policy',
        'view_count'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'sale_price' => 'decimal:2',
        'weight' => 'decimal:2',
        'view_count' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relations
    public function vendor()
    {
        return $this->belongsTo(Vendor::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class, 'product_categories')
                    ->withPivot('is_primary_category', 'added_by', 'added_at')
                    ->orderByPivot('is_primary_category', 'desc'); // Primary categories first
    }

    public function variants()
    {
        return $this->hasMany(ProductVariant::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class);
    }

    public function inventory()
    {
        return $this->hasOne(Inventory::class);
    }

    /**
     * Update the in_stock status based on inventory quantity
     */
    public function updateStockStatus()
    {
        $inventory = $this->inventory;
        if ($inventory) {
            $this->in_stock = ($inventory->quantity > 0);
            $this->save();
        }
        return $this;
    }

    /**
     * Boot the model
     */
    protected static function boot()
    {
        parent::boot();
        
        // When inventory is updated, update the product in_stock status
        static::created(function($product) {
            $product->updateStockStatus();
        });
    }
    
    /**
     * Get the in_stock attribute.
     * This will ensure the in_stock flag is always correctly calculated.
     *
     * @return bool
     */
    public function getInStockAttribute()
    {
        if ($this->relationLoaded('inventory')) {
            $productInventory = $this->inventory->first();
            $quantity = $productInventory ? (int)$productInventory->quantity : 0;
            return $quantity > 0;
        }
        
        // Eager load inventory if not already loaded
        $productInventory = $this->inventory()->first();
        $quantity = $productInventory ? (int)$productInventory->quantity : 0;
        return $quantity > 0;
    }
    
    /**
     * Get the stock_qty attribute.
     * This will ensure the stock quantity is always correctly calculated.
     *
     * @return int
     */
    public function getStockQtyAttribute()
    {
        if ($this->relationLoaded('inventory')) {
            $productInventory = $this->inventory->first();
            return $productInventory ? (int)$productInventory->quantity : 0;
        }
        
        // Eager load inventory if not already loaded
        $productInventory = $this->inventory()->first();
        return $productInventory ? (int)$productInventory->quantity : 0;
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function priceHistory()
    {
        return $this->hasMany(PriceHistory::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function wishlistItems()
    {
        return $this->hasMany(WishlistItem::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }

    public function cartItems()
    {
        return $this->hasMany(Cart::class);
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }
    
    /**
     * Get the review count for this product (approved reviews only).
     */
    public function getReviewCountAttribute()
    {
        if ($this->relationLoaded('reviews')) {
            return $this->reviews->count();
        }
        return $this->reviews()->where('moderation_status', 'approved')->count();
    }
    
    /**
     * Get the average rating for this product (approved reviews only).
     */
    public function getAvgRatingAttribute()
    {
        if ($this->relationLoaded('reviews')) {
            return $this->reviews->avg('rating') ?: 0;
        }
        $avgRating = $this->reviews()->where('moderation_status', 'approved')->avg('rating');
        return $avgRating ?: 0;
    }

    /**
     * Define which relationships should cascade on delete.
     *
     * @return array
     */
    public function getCascadeDeletes(): array
    {
        return [
            'variants',
            'images',
            'inventory',
            'priceHistory',
            'cartItems',
            'wishlistItems'
        ];
    }

    /*
    protected static function boot()
    {
        parent::boot();

        // When a product is soft deleted, cascade to related entities
        static::deleting(function($product) {
            // Only cascade if this is a soft delete operation
            if (!$product->isForceDeleting()) {
                // Cascade soft delete to related entities
                $product->variants()->each(function($variant) {
                    $variant->delete();
                });
                
                $product->images()->each(function($image) {
                    $image->delete();
                });
                
                $product->inventory()->each(function($inventory) {
                    $inventory->delete();
                });
                
                $product->priceHistory()->each(function($priceHistory) {
                    $priceHistory->delete();
                });
                
                $product->cartItems()->each(function($cartItem) {
                    $cartItem->delete();
                });
                
                $product->wishlistItems()->each(function($wishlistItem) {
                    $wishlistItem->delete();
                });
                
                // We don't delete reviews when products are deleted
                // to preserve customer feedback history
                
                // We don't delete order items as they represent completed orders
                // with financial implications
            }
        });

        // When a product is restored, cascade restore to related entities
        static::restored(function($product) {
            // Restore related entities that were soft deleted with this product
            $product->variants()->onlyTrashed()->each(function($variant) {
                $variant->restore();
            });
            
            $product->images()->onlyTrashed()->each(function($image) {
                $image->restore();
            });
            
            $product->inventory()->onlyTrashed()->each(function($inventory) {
                $inventory->restore();
            });
            
            $product->priceHistory()->onlyTrashed()->each(function($priceHistory) {
                $priceHistory->restore();
            });
            
            $product->cartItems()->onlyTrashed()->each(function($cartItem) {
                $cartItem->restore();
            });
            
            $product->wishlistItems()->onlyTrashed()->each(function($wishlistItem) {
                $wishlistItem->restore();
            });
        });
    }
    */
}
