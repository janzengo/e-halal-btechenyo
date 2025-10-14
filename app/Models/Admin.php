<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Admin extends Authenticatable
{
    use Loggable, Notifiable;

    protected $table = 'admin';

    public $timestamps = false;

    protected $fillable = [
        'username',
        'email',
        'password',
        'firstname',
        'lastname',
        'photo',
        'created_on',
        'role',
        'gender',
    ];

    protected $hidden = [
        'password',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'created_on' => 'date',
        ];
    }

    public function otpRequests(): HasMany
    {
        return $this->hasMany(AdminOtpRequest::class, 'email', 'email');
    }

    public function passwordResetTokens(): HasMany
    {
        return $this->hasMany(\Illuminate\Auth\Passwords\PasswordResetToken::class, 'email', 'email');
    }

    public function isHead(): bool
    {
        return $this->role === 'head';
    }

    public function isOfficer(): bool
    {
        return $this->role === 'officer';
    }

    public function getFullNameAttribute(): string
    {
        return "{$this->firstname} {$this->lastname}";
    }
}
