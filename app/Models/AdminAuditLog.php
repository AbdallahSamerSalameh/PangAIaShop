<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AdminAuditLog extends Model
{
    use HasFactory, SoftDeletes;
    
    // Enable timestamps and handle created_at properly
    public $timestamps = true;
    
    // Override updated_at since we only use created_at
    const UPDATED_AT = null;

    protected $fillable = [
        'admin_id',
        'action',
        'resource',
        'resource_id',
        'previous_data',
        'new_data',
        'description',
        'ip_address',
        'user_agent'
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

    /**
     * Get the changes as a JSON string for display
     */
    public function getChangesAttribute()
    {
        $changes = [];
        
        if ($this->previous_data && $this->new_data) {
            $changes = [
                'previous' => $this->previous_data,
                'new' => $this->new_data
            ];
        } elseif ($this->new_data) {
            $changes = ['data' => $this->new_data];
        } elseif ($this->previous_data) {
            $changes = ['data' => $this->previous_data];
        }
        
        return !empty($changes) ? json_encode($changes) : null;
    }

    /**
     * Get the model attribute (alias for resource)
     */
    public function getModelAttribute()
    {
        return $this->resource;
    }

    /**
     * Get the model_id attribute (alias for resource_id)
     */
    public function getModelIdAttribute()
    {
        return $this->resource_id;
    }

    /**
     * Get the appropriate Bootstrap color class for the action
     */
    public function getActionColor()
    {
        $actionColors = [
            'create' => 'success',
            'created' => 'success',
            'update' => 'primary',
            'updated' => 'primary',
            'edit' => 'primary',
            'delete' => 'danger',
            'deleted' => 'danger',
            'destroy' => 'danger',
            'toggle' => 'warning',
            'toggled' => 'warning',
            'view' => 'info',
            'viewed' => 'info',
            'access' => 'info',
            'accessed' => 'info',
            'login' => 'success',
            'logout' => 'secondary',
            'failed_login' => 'danger',
            'password_reset' => 'warning',
            'export' => 'info',
            'import' => 'primary',
            'bulk' => 'warning',
        ];

        $action = strtolower($this->action);
        
        // Check for exact matches first
        if (isset($actionColors[$action])) {
            return $actionColors[$action];
        }
        
        // Check for partial matches
        foreach ($actionColors as $key => $color) {
            if (str_contains($action, $key)) {
                return $color;
            }
        }
        
        // Default color
        return 'secondary';
    }
}
