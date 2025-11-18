<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class UpdateEncounterDTO
{
    public function __construct(
        public readonly array $data
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'season_id' => 'sometimes|required|exists:seasons,id',
            'team_id' => 'sometimes|required|exists:teams,id',
            'opponent' => 'sometimes|required|string|max:255',
            'is_at_home' => 'sometimes|required|boolean',
            'happens_at' => 'sometimes|required|date',
            'is_victory' => 'nullable|boolean',
            'team_score' => 'nullable|integer',
            'opponent_score' => 'nullable|integer',
        ]);

        return new self($validated);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
