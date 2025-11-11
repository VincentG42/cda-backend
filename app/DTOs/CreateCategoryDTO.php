<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CreateCategoryDTO
{
    public function __construct(
        public string $title,
        public string $gender,
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'gender' => 'required|string|in:M,F,X',
        ]);

        return new self(
            title: $validated['title'],
            gender: $validated['gender'],
        );
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'gender' => $this->gender,
        ];
    }
}
