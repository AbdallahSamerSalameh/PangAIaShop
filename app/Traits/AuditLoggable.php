<?php

namespace App\Traits;

use App\Models\AdminAuditLog;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;

trait AuditLoggable
{
    /**
     * Log admin activity
     *
     * @param string $action
     * @param string $resource
     * @param int|null $resourceId
     * @param array|null $previousData
     * @param array|null $newData
     * @param string|null $description
     * @return AdminAuditLog
     */
    protected function logActivity(
        string $action, 
        string $resource, 
        ?int $resourceId = null, 
        ?array $previousData = null, 
        ?array $newData = null,
        ?string $description = null
    ): AdminAuditLog {
        return AdminAuditLog::create([
            'admin_id' => Auth::guard('admin')->id(),
            'action' => $action,
            'resource' => $resource,
            'resource_id' => $resourceId,
            'previous_data' => $previousData,
            'new_data' => $newData,
            'description' => $description,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Log model creation
     *
     * @param Model $model
     * @param string|null $description
     * @return AdminAuditLog
     */
    protected function logCreate(Model $model, ?string $description = null): AdminAuditLog
    {
        $resourceName = $this->getResourceName($model);
        $description = $description ?: "Created {$resourceName}: " . $this->getModelIdentifier($model);
        
        return $this->logActivity(
            'create',
            $resourceName,
            $model->id,
            null,
            $model->toArray(),
            $description
        );
    }

    /**
     * Log model update
     *
     * @param Model $model
     * @param array $originalData
     * @param string|null $description
     * @return AdminAuditLog
     */
    protected function logUpdate(Model $model, array $originalData, ?string $description = null): AdminAuditLog
    {
        $resourceName = $this->getResourceName($model);
        $description = $description ?: "Updated {$resourceName}: " . $this->getModelIdentifier($model);
        
        return $this->logActivity(
            'update',
            $resourceName,
            $model->id,
            $originalData,
            $model->toArray(),
            $description
        );
    }

    /**
     * Log model deletion
     *
     * @param Model $model
     * @param string|null $description
     * @return AdminAuditLog
     */
    protected function logDelete(Model $model, ?string $description = null): AdminAuditLog
    {
        $resourceName = $this->getResourceName($model);
        $description = $description ?: "Deleted {$resourceName}: " . $this->getModelIdentifier($model);
        
        return $this->logActivity(
            'delete',
            $resourceName,
            $model->id,
            $model->toArray(),
            null,
            $description
        );
    }

    /**
     * Log status toggle
     *
     * @param Model $model
     * @param string $field
     * @param mixed $oldValue
     * @param mixed $newValue
     * @param string|null $description
     * @return AdminAuditLog
     */
    protected function logToggle(Model $model, string $field, $oldValue, $newValue, ?string $description = null): AdminAuditLog
    {
        $resourceName = $this->getResourceName($model);
        $description = $description ?: "Toggled {$field} for {$resourceName}: " . $this->getModelIdentifier($model) . " from {$oldValue} to {$newValue}";
        
        return $this->logActivity(
            'toggle',
            $resourceName,
            $model->id,
            [$field => $oldValue],
            [$field => $newValue],
            $description
        );
    }

    /**
     * Log custom action
     *
     * @param string $action
     * @param Model|null $model
     * @param string|null $description
     * @param array|null $additionalData
     * @return AdminAuditLog
     */
    protected function logCustomAction(string $action, ?Model $model = null, ?string $description = null, ?array $additionalData = null): AdminAuditLog
    {
        $resourceName = $model ? $this->getResourceName($model) : 'system';
        $resourceId = $model ? $model->id : null;
        
        return $this->logActivity(
            $action,
            $resourceName,
            $resourceId,
            null,
            $additionalData,
            $description
        );
    }

    /**
     * Get resource name from model
     *
     * @param Model $model
     * @return string
     */
    private function getResourceName(Model $model): string
    {
        $className = class_basename($model);
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $className));
    }

    /**
     * Get model identifier for logging
     *
     * @param Model $model
     * @return string
     */
    private function getModelIdentifier(Model $model): string
    {
        // Try common identifier fields
        if (isset($model->name)) {
            return $model->name;
        }
        if (isset($model->title)) {
            return $model->title;
        }
        if (isset($model->code)) {
            return $model->code;
        }
        if (isset($model->username)) {
            return $model->username;
        }
        if (isset($model->email)) {
            return $model->email;
        }
        
        return "ID #{$model->id}";
    }
}
