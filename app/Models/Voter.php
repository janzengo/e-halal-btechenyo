<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voter extends Model
{
    use Loggable;

    protected $table = 'voters';

    protected $fillable = [
        'course_id',
        'student_number',
        'has_voted',
    ];

    protected $primaryKey = 'id';

    protected function casts(): array
    {
        return [
            'has_voted' => 'boolean',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function otpRequests(): HasMany
    {
        return $this->hasMany(OtpRequest::class, 'student_number', 'student_number');
    }

    public function hasVoted(): bool
    {
        return $this->has_voted;
    }

    public function markAsVoted(): void
    {
        $this->update(['has_voted' => true]);
    }

    public function markAsNotVoted(): void
    {
        $this->update(['has_voted' => false]);
    }

    public function getCourseNameAttribute(): string
    {
        return $this->course?->display_name ?? 'Unknown Course';
    }

    public function scopeVoted($query)
    {
        return $query->where('has_voted', true);
    }

    public function scopeNotVoted($query)
    {
        return $query->where('has_voted', false);
    }

    public function scopeByCourse($query, int $courseId)
    {
        return $query->where('course_id', $courseId);
    }
}
