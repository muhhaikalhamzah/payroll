<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    /**
     * Boot the auditable trait for a model.
     */
    public static function bootAuditable()
    {
        static::created(function (Model $model) {
            $model->audit('CREATED');
        });

        static::updated(function (Model $model) {
            $model->audit('UPDATED');
        });

        static::deleted(function (Model $model) {
            $model->audit('DELETED');
        });
        
        if (method_exists(static::class, 'restored')) {
            static::restored(function (Model $model) {
                $model->audit('RESTORED');
            });
        }
    }

    /**
     * Perform the audit logging.
     *
     * @param string $action
     */
    protected function audit($action)
    {
        $oldValues = [];
        $newValues = [];

        if ($action === 'CREATED') {
            $newValues = $this->getAttributes();
        } elseif ($action === 'UPDATED') {
            // Get original attributes before the change
            $oldValues = array_intersect_key($this->getOriginal(), $this->getChanges());
            $newValues = $this->getChanges();
        } elseif ($action === 'DELETED') {
            $oldValues = $this->getAttributes();
        } elseif ($action === 'RESTORED') {
            $newValues = $this->getAttributes();
        }

        // Don't log if there are no changes on update
        if ($action === 'UPDATED' && empty($newValues)) {
            return;
        }

        AuditLog::create([
            'user_id' => auth()->id(),
            'action' => $action,
            'auditable_type' => get_class($this),
            'auditable_id' => $this->getKey(),
            'old_values' => empty($oldValues) ? null : $oldValues,
            'new_values' => empty($newValues) ? null : $newValues,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'created_at' => now(),
        ]);
    }
}
