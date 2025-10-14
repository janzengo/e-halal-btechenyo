<?php

namespace App\Traits;

use App\Services\LoggingService;
use Illuminate\Database\Eloquent\Model;

trait Loggable
{
    /**
     * Boot the trait and set up model event listeners
     */
    protected static function bootLoggable(): void
    {
        static::created(function (Model $model) {
            if (self::shouldLog('create')) {
                LoggingService::logCreate($model);
            }
        });

        static::updated(function (Model $model) {
            if (self::shouldLog('update')) {
                $oldValues = $model->getOriginal();
                LoggingService::logUpdate($model, $oldValues);
            }
        });

        static::deleted(function (Model $model) {
            if (self::shouldLog('delete')) {
                LoggingService::logDelete($model);
            }
        });
    }

    /**
     * Determine if the action should be logged
     */
    protected static function shouldLog(string $action): bool
    {
        // Skip logging if we're in a console command or if logging is disabled
        if (app()->runningInConsole() || config('logging.disable_model_logging', false)) {
            return false;
        }

        // Skip logging for certain models or actions
        $skipModels = config('logging.skip_models', []);
        $skipActions = config('logging.skip_actions', []);

        if (in_array(static::class, $skipModels) || in_array($action, $skipActions)) {
            return false;
        }

        return true;
    }

    /**
     * Log a custom action for this model
     */
    public function logAction(string $actionType, string $description, ?array $metadata = null): void
    {
        LoggingService::logAdminAction(
            actionType: $actionType,
            description: $description,
            modelType: get_class($this),
            modelId: $this->id,
            metadata: $metadata
        );
    }

    /**
     * Get the logs for this model
     */
    public function getLogs(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(\App\Models\AdminLog::class, 'model_id')
            ->where('model_type', get_class($this))
            ->orderBy('created_at', 'desc');
    }
}
