<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class EncounterFilterDTO
{
    public function __construct(
        public readonly ?int $team_id,
        public readonly string $filter
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'team_id' => 'nullable|integer|exists:teams,id',
            'filter' => 'nullable|string|in:past,upcoming,all',
        ]);

        return new self(
            team_id: $validated['team_id'] ?? null,
            filter: $validated['filter'] ?? 'upcoming'
        );
    }
}
