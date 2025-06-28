<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class LoginDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string'
        ]);

        return new self(
            email: $validated['email'],
            password: $validated['password']
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password
        ];
    }
}
