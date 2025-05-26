<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shipment extends Model
{
    use HasFactory, SoftDeletes;
    
    // Disable Laravel's automatic timestamps since our table only has updated_at
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'tracking_number',
        'origin_country',
        'destination_country',
        'destination_region',
        'destination_zip',
        'weight',
        'shipping_zone',
        'status',
        'actual_cost',
        'shipping_method',
        'service_level',
        'base_cost',
        'per_item_cost',
        'per_weight_unit_cost',
        'delivery_time_days',
        'shipped_at',
        'delivered_at',
        'created_by',
        'updated_by',
        'updated_at'
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'actual_cost' => 'decimal:2',
        'base_cost' => 'decimal:2',
        'per_item_cost' => 'decimal:2',
        'per_weight_unit_cost' => 'decimal:2',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function createdBy()
    {
        return $this->belongsTo(Admin::class, 'created_by');
    }

    public function updatedBy()
    {
        return $this->belongsTo(Admin::class, 'updated_by');
    }
}
