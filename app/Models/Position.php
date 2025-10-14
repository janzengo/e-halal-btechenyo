<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Position extends Model
{
    use Loggable;

    protected $table = 'positions';

    protected $fillable = [
        'description',
        'max_vote',
        'priority',
    ];

    protected function casts(): array
    {
        return [
            'max_vote' => 'integer',
            'priority' => 'integer',
        ];
    }

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->description ?? 'Unknown Position';
    }

    public function scopeOrderedByPriority($query)
    {
        return $query->orderBy('priority');
    }
}
