<?php

namespace App\DTOs;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CreateEventDTO
{
    public function __construct(
        public readonly string $title,
        public readonly string $start_at,
        public readonly string $place,
        public readonly string $address,
        public readonly int $author_id,
        public readonly ?string $close_at = null,
        public readonly ?string $additionnal_info = null
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'start_at' => 'required|date',
            'close_at' => 'nullable|date|after_or_equal:start_at',
            'place' => 'required|string|max:255',
            'address' => 'required|string|max:255',
            'additionnal_info' => 'nullable|string',
        ]);

        return new self(
            title: $validated['title'],
            start_at: $validated['start_at'],
            place: $validated['place'],
            address: $validated['address'],
            author_id: Auth::id(),
            close_at: $validated['close_at'] ?? null,
            additionnal_info: $validated['additionnal_info'] ?? null
        );
    }

    public function toArray(): array
    {
        return array_filter([
            'title' => $this->title,
            'start_at' => $this->start_at,
            'place' => $this->place,
            'address' => $this->address,
            'author_id' => $this->author_id,
            'close_at' => $this->close_at,
            'additionnal_info' => $this->additionnal_info,
        ], fn ($value) => $value !== null);
    }
}
