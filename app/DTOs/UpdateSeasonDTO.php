<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class UpdateSeasonDTO
{
    public function __construct(
        public ?string $name = null,
        public ?string $start_date = null,
        public ?string $end_date = null,
        public ?bool $is_active = null,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'start_date' => 'sometimes|date',
            'end_date' => 'sometimes|date|after:start_date',
            'is_active' => 'sometimes|boolean',
        ]);

        return new self(
            name: $validated['name'] ?? null,
            start_date: $validated['start_date'] ?? null,
            end_date: $validated['end_date'] ?? null,
            is_active: $validated['is_active'] ?? null,
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
        ], fn ($value) => $value !== null);
    }

    public function hasData(): bool
    {
        return $this->name !== null || $this->start_date !== null || $this->end_date !== null || $this->is_active !== null;
    }
}
