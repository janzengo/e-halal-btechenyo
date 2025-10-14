<?php

declare(strict_types=1);

namespace App\Models;

use App\Traits\Loggable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Partylist extends Model
{
    use Loggable;

    protected $table = 'partylists';

    protected $fillable = [
        'name',
        'color',
        'platform',
    ];

    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->name ?? 'Independent';
    }

    public function isIndependent(): bool
    {
        return empty($this->name) || strtolower($this->name) === 'independent';
    }
}
