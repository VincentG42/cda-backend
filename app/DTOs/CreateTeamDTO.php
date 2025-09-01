<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CreateTeamDTO
{
    public function __construct(
        public readonly string $name,
        public readonly int $categoryId,
        public readonly int $coachId,
        public readonly int $seasonId,
        public readonly ?string $gender
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'coach_id' => 'required|exists:users,id',
            'season_id' => 'required|exists:seasons,id',
            'gender' => 'nullable|string',
        ]);

        return new self(
            name: $validated['name'],
            categoryId: $validated['category_id'],
            coachId: $validated['coach_id'],
            seasonId: $validated['season_id'],
            gender: $validated['gender'] ?? null
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'category_id' => $this->categoryId,
            'coach_id' => $this->coachId,
            'season_id' => $this->seasonId,
            'gender' => $this->gender,
        ];
    }
}
