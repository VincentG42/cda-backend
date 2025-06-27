<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EncounterStat extends Model
{
    use HasFactory;

    protected $fillable = ['encounter_id', 'json'];

    public function encounter(): BelongsTo
    {
        return $this->belongsTo(Encounter::class);
    }
}
