<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class UpdateEventDTO
{
    public function __construct(
        public readonly array $data
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'start_at' => 'sometimes|required|date',
            'close_at' => 'nullable|date|after_or_equal:start_at',
            'place' => 'sometimes|required|string|max:255',
            'address' => 'sometimes|required|string|max:255',
            'additionnal_info' => 'nullable|string',
        ]);

        return new self($validated);
    }

    public function toArray(): array
    {
        return $this->data;
    }
}
