<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrderItem extends Model
{
    use HasFactory, SoftDeletes;
    
    // Disable timestamps as they don't exist in the table
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'variant_id',
        'quantity',
        'price',
        'tax_rate',
        'tax_amount',
        'tax_name',
        'tax_region',
        'discount_amount'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'tax_rate' => 'decimal:4',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'quantity' => 'integer',
        'deleted_at' => 'datetime'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
    
    public function variant()
    {
        return $this->belongsTo(ProductVariant::class);
    }

    // Since OrderItem is a leaf node in our model hierarchy
    // (doesn't have child entities), we don't need to implement
    // custom cascade delete or restore functionality
}
