<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CreateSeasonDTO
{
    public function __construct(
        public string $name,
        public string $start_date,
        public string $end_date,
        public bool $is_active = false,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'is_active' => 'sometimes|boolean',
        ]);

        return new self(
            name: $validated['name'],
            start_date: $validated['start_date'],
            end_date: $validated['end_date'],
            is_active: $validated['is_active'] ?? false,
        );
    }

    public function toArray(): array
    {
        return [
            'name' => $this->name,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'is_active' => $this->is_active,
        ];
    }
}
