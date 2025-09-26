<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class UserFilterDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?int $team_id,
        public readonly ?int $user_type_id
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'team_id' => 'nullable|integer|exists:teams,id',
            'user_type_id' => 'nullable|integer|exists:user_types,id',
        ]);

        return new self(
            name: $validated['name'] ?? null,
            team_id: isset($validated['team_id']) ? (int) $validated['team_id'] : null,
            user_type_id: isset($validated['user_type_id']) ? (int) $validated['user_type_id'] : null,
        );
    }
}
