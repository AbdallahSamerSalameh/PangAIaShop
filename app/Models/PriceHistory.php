<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PriceHistory extends Model
{
    use HasFactory, SoftDeletes;

    // Disable the standard timestamps (created_at and updated_at)
    public $timestamps = false;
    
    // Define the updated_at column to be used for timestamp updates
    const UPDATED_AT = 'updated_at';

    protected $fillable = [
        'product_id',
        'variant_id',
        'previous_price',
        'new_price',
        'changed_by',
        'reason'
    ];

    protected $casts = [
        'previous_price' => 'decimal:2',
        'new_price' => 'decimal:2'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class)->withTrashed();
    }
    
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class, 'variant_id')->withTrashed();
    }
    
    public function admin()
    {
        return $this->belongsTo(Admin::class, 'changed_by')->withTrashed();
    }
}
