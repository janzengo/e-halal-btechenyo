<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'role',
        'action_type',
        'model_type',
        'model_id',
        'action_description',
        'old_values',
        'new_values',
        'metadata',
        'ip_address',
        'user_agent',
        'election_id',
    ];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'metadata' => 'array',
    ];

    /**
     * Get the admin user that performed this action
     */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'user_id', 'id');
    }

    /**
     * Get the election this log belongs to
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(ElectionStatus::class, 'election_id', 'id');
    }

    /**
     * Scope to filter by action type
     */
    public function scopeActionType($query, string $actionType)
    {
        return $query->where('action_type', $actionType);
    }

    /**
     * Scope to filter by model type
     */
    public function scopeModelType($query, string $modelType)
    {
        return $query->where('model_type', $modelType);
    }

    /**
     * Scope to filter by user
     */
    public function scopeByUser($query, string $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Scope to filter by date range
     */
    public function scopeDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }

    /**
     * Get formatted action description
     */
    public function getFormattedActionAttribute(): string
    {
        $action = ucfirst(str_replace('_', ' ', $this->action_type));
        $model = $this->model_type ? class_basename($this->model_type) : '';

        if ($model) {
            return "{$action} {$model}";
        }

        return $action;
    }
}
