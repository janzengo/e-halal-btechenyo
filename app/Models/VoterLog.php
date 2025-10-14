<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VoterLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'voter_id',
        'action_type',
        'action_description',
        'metadata',
        'ip_address',
        'user_agent',
        'election_id',
    ];

    protected $casts = [
        'metadata' => 'array',
    ];

    /**
     * Get the voter that performed this action
     */
    public function voter(): BelongsTo
    {
        return $this->belongsTo(Voter::class, 'voter_id', 'student_number');
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
     * Scope to filter by voter
     */
    public function scopeByVoter($query, string $voterId)
    {
        return $query->where('voter_id', $voterId);
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
        return ucfirst(str_replace('_', ' ', $this->action_type));
    }
}
