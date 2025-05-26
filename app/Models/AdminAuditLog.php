<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminAuditLog extends Model
{
    use HasFactory, SoftDeletes;
    
    // Disable timestamps since we only have created_at in the migration
    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'action',
        'resource',
        'resource_id',
        'previous_data',
        'new_data',
        'ip_address',
        'user_agent',
        'created_at'
    ];

    protected $casts = [
        'previous_data' => 'array',
        'new_data' => 'array',
        'created_at' => 'datetime',
        'deleted_at' => 'datetime'
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class);
    }
}
