<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Candidate extends Model
{
    use Loggable;

    protected $table = 'candidates';

    protected $fillable = [
        'position_id',
        'firstname',
        'lastname',
        'partylist_id',
        'photo',
        'platform',
        'votes',
    ];

    protected function casts(): array
    {
        return [
            'votes' => 'integer',
        ];
    }

    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    public function partylist(): BelongsTo
    {
        return $this->belongsTo(Partylist::class);
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }

    public function getDisplayNameAttribute(): string
    {
        $name = $this->full_name;
        if ($this->partylist && ! $this->partylist->isIndependent()) {
            $name .= " ({$this->partylist->name})";
        }

        return $name;
    }

    public function isIndependent(): bool
    {
        return ! $this->partylist_id || $this->partylist?->isIndependent();
    }

    public function incrementVotes(int $count = 1): void
    {
        $this->increment('votes', $count);
    }

    public function decrementVotes(int $count = 1): void
    {
        $this->decrement('votes', $count);
    }
}
