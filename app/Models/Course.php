<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Course extends Model
{
    use Loggable;

    protected $table = 'courses';

    protected $fillable = [
        'code',
        'description',
    ];

    public function voters(): HasMany
    {
        return $this->hasMany(Voter::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->description ?? 'Unknown Course';
    }
}
