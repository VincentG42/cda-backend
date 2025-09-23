<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class UpdateTeamDTO
{
    public function __construct(
        public readonly ?string $name,
        public readonly ?int $categoryId,
        public readonly ?int $coachId,
        public readonly ?int $seasonId,
        public readonly ?string $gender
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'category_id' => 'sometimes|required|exists:categories,id',
            'coach_id' => 'sometimes|required|exists:users,id',
            'season_id' => 'sometimes|required|exists:seasons,id',
            'gender' => 'sometimes|nullable|string',
        ]);

        return new self(
            name: $validated['name'] ?? null,
            categoryId: $validated['category_id'] ?? null,
            coachId: $validated['coach_id'] ?? null,
            seasonId: $validated['season_id'] ?? null,
            gender: $validated['gender'] ?? null
        );
    }

    public function toArray(): array
    {
        // Filter out null values so we only update what's provided
        return array_filter([
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'coach_id' => $this->coachId,
            'season_id' => $this->seasonId,
            'gender' => $this->gender,
        ], fn ($value) => $value !== null);
    }
}
