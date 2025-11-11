<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class UpdateCategoryDTO
{
    public function __construct(
        public ?string $title = null,
        public ?string $gender = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'gender' => 'sometimes|string|in:M,F,X',
        ]);

        return new self(
            title: $validated['title'] ?? null,
            gender: $validated['gender'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'gender' => $this->gender,
        ], fn ($value) => $value !== null);
    }

    public function hasData(): bool
    {
        return $this->title !== null || $this->gender !== null;
    }
}
