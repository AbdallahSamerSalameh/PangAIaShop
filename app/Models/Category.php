<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CascadeSoftDeletes;

class Category extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;

    protected $fillable = [
        'name',
        'parent_category_id',
        'image_url',
        'category_description',
        'created_by',
        'is_active',
        'display_order'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relations
    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_category_id');
    }

    public function children()
    {
        return $this->hasMany(Category::class, 'parent_category_id');
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_categories');
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    /**
     * Define which relationships should cascade on delete.
     *
     * @return array
     */
    public function getCascadeDeletes(): array
    {
        return [
            'children'
        ];
    }

    /*
    protected static function boot()
    {
        parent::boot();

        // When a category is soft deleted, cascade to related entities
        static::deleting(function($category) {
            // Only cascade if this is a soft delete operation
            if (!$category->isForceDeleting()) {
                // Cascade soft delete to child categories
                $category->children()->each(function($child) {
                    $child->delete();
                });
                
                // Soft delete the product_categories pivot records
                // This won't delete the products themselves, just the category association
                $category->products()->detach();
            }
        });

        // When a category is restored, cascade restore to related entities
        static::restored(function($category) {
            // Restore child categories that were soft deleted with this category
            $category->children()->onlyTrashed()->each(function($child) {
                $child->restore();
            });
            
            // Note: We don't automatically restore product associations
            // as that would require custom logic to reattach the products
        });
    }
    */

    /**
     * Check if this category is a leaf node (has no children categories)
     * 
     * @return bool
     */
    public function isLeafCategory()
    {
        return $this->children()->count() === 0;
    }

    /**
     * Get all leaf categories for a product (categories with no children)
     * 
     * @param  \App\Models\Product  $product
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getLeafCategoriesForProduct($product)
    {
        return $product->categories()->whereNotIn('id', function($query) {
            $query->select('parent_category_id')
                  ->from('categories')
                  ->whereNotNull('parent_category_id');
        })->get();
    }
}
