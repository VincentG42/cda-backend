<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CreateEncounterDTO
{
    public function __construct(
        public readonly int $season_id,
        public readonly int $team_id,
        public readonly string $opponent,
        public readonly bool $is_at_home,
        public readonly string $happens_at,
        public readonly ?bool $is_victory
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'season_id' => 'required|exists:seasons,id',
            'team_id' => 'required|exists:teams,id',
            'opponent' => 'required|string|max:255',
            'is_at_home' => 'required|boolean',
            'happens_at' => 'required|date',
            'is_victory' => 'nullable|boolean',
        ]);

        return new self(
            season_id: $validated['season_id'],
            team_id: $validated['team_id'],
            opponent: $validated['opponent'],
            is_at_home: $validated['is_at_home'],
            happens_at: $validated['happens_at'],
            is_victory: $validated['is_victory'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter((array) $this, fn ($value) => $value !== null);
    }
}
