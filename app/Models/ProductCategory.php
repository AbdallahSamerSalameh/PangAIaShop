<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ProductCategory extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'product_categories';
    
    // Disable Laravel's automatic timestamps since this table uses 'added_at' instead
    public $timestamps = false;

    protected $fillable = [
        'product_id',
        'category_id',
        'is_primary_category',
        'added_by',
        'added_at'
    ];

    protected $casts = [
        'is_primary_category' => 'boolean',
        'added_at' => 'datetime'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }

    public function category()
    {
        return $this->belongsTo(Category::class)->withTrashed();
    }
}
