<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\CascadeSoftDeletes;

class Order extends Model
{
    use HasFactory, SoftDeletes, CascadeSoftDeletes;
    
    // Turn off Laravel's automatic timestamps since our table only has order_date
    public $timestamps = false;
    
    // Define order status constants
    const STATUS_PENDING = 'pending';
    const STATUS_PROCESSING = 'processing';
    const STATUS_SHIPPED = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_COMPLETED = 'completed';
    const STATUS_CANCELLED = 'cancelled';
    const STATUS_REFUNDED = 'refunded';
    const STATUS_ON_HOLD = 'on_hold';

    protected $fillable = [
        'user_id',
        'order_number',
        'shipping_street',
        'shipping_city',
        'shipping_state',
        'shipping_postal_code',
        'shipping_country',
        'billing_street',
        'billing_city',
        'billing_state',
        'billing_postal_code',
        'billing_country',
        'total_amount',
        'subtotal',
        'shipping',
        'order_date',
        'status',
        'discount',
        'promo_code_id',
        'expected_delivery_date',
        'notes',
        'admin_notes',
        'handled_by',
        'cancelled_at',
        'cancellation_reason',
        'notes'
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'discount' => 'decimal:2',
        'order_date' => 'datetime',
        'expected_delivery_date' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    // Relations
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(Payment::class);
    }
    
    public function payment()
    {
        return $this->hasOne(Payment::class)->latest();
    }

    public function shipments()
    {
        return $this->hasMany(Shipment::class);
    }

    public function promoCode()
    {
        return $this->belongsTo(PromoCode::class);
    }

    public function handledBy()
    {
        return $this->belongsTo(Admin::class, 'handled_by');
    }

    public function supportTickets()
    {
        return $this->hasMany(SupportTicket::class);
    }

    /**
     * Define which relationships should cascade on delete.
     *
     * @return array
     */
    public function getCascadeDeletes(): array
    {
        return [
            'items',
            'payments',
            'shipments',
            'supportTickets'
        ];
    }

    /*
    protected static function boot()
    {
        parent::boot();

        // When an order is soft deleted, cascade to related entities
        static::deleting(function($order) {
            // Only cascade if this is a soft delete operation
            if (!$order->isForceDeleting()) {
                // Cascade soft delete to related entities
                $order->items()->each(function($item) {
                    $item->delete();
                });
                
                $order->payments()->each(function($payment) {
                    $payment->delete();
                });
                
                $order->shipments()->each(function($shipment) {
                    $shipment->delete();
                });
                
                $order->supportTickets()->each(function($ticket) {
                    $ticket->delete();
                });
            }
        });

        // When an order is restored, cascade restore to related entities
        static::restored(function($order) {
            // Restore related entities that were soft deleted with this order
            $order->items()->onlyTrashed()->each(function($item) {
                $item->restore();
            });
            
            $order->payments()->onlyTrashed()->each(function($payment) {
                $payment->restore();
            });
            
            $order->shipments()->onlyTrashed()->each(function($shipment) {
                $shipment->restore();
            });
            
            $order->supportTickets()->onlyTrashed()->each(function($ticket) {
                $ticket->restore();
            });
        });
    }
    */
}
