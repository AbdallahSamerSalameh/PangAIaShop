<?php

namespace App\Traits;

trait CascadeSoftDeletes
{
    /**
     * Boot the trait.
     *
     * @return void
     */
    protected static function bootCascadeSoftDeletes()
    {
        static::deleting(function($model) {
            // Only proceed if this is a soft delete operation
            if (!$model->isForceDeleting()) {
                // Get the relationships that should cascade on delete
                $relationships = $model->getCascadeDeletes();
                
                // Cascade soft deletes to related models
                foreach ($relationships as $relationship) {
                    if ($model->{$relationship}) {
                        $relatedRecords = $model->{$relationship};
                        
                        // Check if it's a collection or a single model
                        if ($relatedRecords instanceof \Illuminate\Database\Eloquent\Collection) {
                            $relatedRecords->each(function($relatedRecord) {
                                $relatedRecord->delete();
                            });
                        } else {
                            // It's a single model
                            if ($relatedRecords) {
                                $relatedRecords->delete();
                            }
                        }
                    }
                }
            }
        });

        static::restored(function($model) {
            // Get the relationships that should cascade on restore
            $relationships = $model->getCascadeDeletes();
            
            // Cascade restore to related models
            foreach ($relationships as $relationship) {
                $relationMethod = $relationship;
                
                // For belongsTo relationships, we need to check if they're deleted
                if (method_exists($model->{$relationMethod}(), 'getParentKey')) {
                    $parentKey = $model->{$relationMethod}()->getParentKey();
                    $relatedClass = get_class($model->{$relationMethod}()->getRelated());
                    
                    if ($parentKey) {
                        $relatedClass::withTrashed()->find($parentKey)?->restore();
                    }
                }
                
                // For other relationships (hasMany, etc.), restore related models
                if (method_exists($model, $relationMethod)) {
                    $relationObj = $model->{$relationMethod}();
                    
                    if (method_exists($relationObj, 'onlyTrashed')) {
                        $model->{$relationMethod}()->onlyTrashed()->get()->each->restore();
                    }
                }
            }
        });
    }

    /**
     * Define which relationships should cascade on delete and restore.
     * This should be defined in each model that uses this trait.
     *
     * @return array
     */
    abstract public function getCascadeDeletes(): array;
}