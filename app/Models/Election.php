<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Election extends Model
{
    use Loggable;

    protected $table = 'elections';

    protected $fillable = [
        'status',
        'election_name',
        'end_time',
        'last_status_change',
        'control_number',
    ];

    protected function casts(): array
    {
        return [
            'end_time' => 'datetime',
            'last_status_change' => 'datetime',
        ];
    }

    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class, 'election_id');
    }

    public function isSetup(): bool
    {
        return $this->status === 'setup';
    }

    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isPaused(): bool
    {
        return $this->status === 'paused';
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function canBeActivated(): bool
    {
        return in_array($this->status, ['setup', 'pending', 'paused']);
    }

    public function canBePaused(): bool
    {
        return $this->status === 'active';
    }

    public function canBeCompleted(): bool
    {
        return in_array($this->status, ['active', 'paused']);
    }
}
