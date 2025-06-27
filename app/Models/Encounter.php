<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Encounter extends Model
{
    use HasFactory;

    protected $fillable = [
        'season_id',
        'team_id',
        'opponent',
        'is_at_home',
        'happens_at',
        'is_victory'
    ];

    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function encounterStats(): HasMany
    {
        return $this->hasMany(EncounterStat::class);
    }

    public function individualStats(): HasMany
    {
        return $this->hasMany(IndividualStat::class);
    }
}
