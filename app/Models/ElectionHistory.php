<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ElectionHistory extends Model
{
    protected $table = 'election_history';

    protected $fillable = [
        'election_name',
        'status',
        'end_time',
        'last_status_change',
        'details_pdf',
        'results_pdf',
        'control_number',
    ];

    protected function casts(): array
    {
        return [
            'end_time' => 'datetime',
            'last_status_change' => 'datetime',
        ];
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

    public function hasDetailsPdf(): bool
    {
        return ! empty($this->details_pdf);
    }

    public function hasResultsPdf(): bool
    {
        return ! empty($this->results_pdf);
    }
}
