<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AdminOtpRequest extends Model
{
    protected $table = 'admin_otp_requests';

    protected $fillable = [
        'email',
        'otp',
        'attempts',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'attempts' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

    public function admin(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'email', 'email');
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function isValid(): bool
    {
        return ! $this->isExpired() && $this->attempts < 5;
    }

    public function incrementAttempts(): void
    {
        $this->increment('attempts');
    }

    public function hasExceededMaxAttempts(): bool
    {
        return $this->attempts >= 5;
    }

    public function scopeValid($query)
    {
        return $query->where('expires_at', '>', now())->where('attempts', '<', 5);
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<=', now());
    }

    public function scopeByEmail($query, string $email)
    {
        return $query->where('email', $email);
    }
}
