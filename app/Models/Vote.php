<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Vote extends Model
{
    protected $table = 'votes';

    protected $fillable = [
        'election_id',
        'vote_ref',
        'votes_data',
    ];

    protected function casts(): array
    {
        return [
            'votes_data' => 'array',
        ];
    }

    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class, 'election_id');
    }

    public function getVoteDataAttribute(): array
    {
        return $this->votes_data ?? [];
    }

    public function setVoteDataAttribute(array $data): void
    {
        $this->votes_data = $data;
    }

    public function getVotesForPosition(int $positionId): array
    {
        return $this->vote_data[$positionId] ?? [];
    }

    public function hasVotesForPosition(int $positionId): bool
    {
        return isset($this->vote_data[$positionId]) && ! empty($this->vote_data[$positionId]);
    }

    public function getVoteReferenceAttribute(): string
    {
        return $this->vote_ref;
    }

    public function scopeByElection($query, int $electionId)
    {
        return $query->where('election_id', $electionId);
    }

    public function scopeByReference($query, string $voteRef)
    {
        return $query->where('vote_ref', $voteRef);
    }
}
