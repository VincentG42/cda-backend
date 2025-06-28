<?php

namespace App\DTOs;

use Illuminate\Http\Request;

class CreateUserDTO
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
        public readonly int $userTypeId,
        public readonly string $lastname,
        public readonly string $firstname,
        public readonly ?string $licenceNumber,
        public readonly bool $hasToChangePassword
    ) {}

    public static function fromRequest(Request $request): self
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'user_type_id' => 'required|exists:user_types,id',
            'lastname' => 'required|string',
            'firstname' => 'required|string',
            'licence_number' => 'nullable|string',
            'has_to_change_password' => 'boolean',
        ]);

        return new self(
            email: $validated['email'],
            password: $validated['password'],
            userTypeId: $validated['user_type_id'],
            lastname: $validated['lastname'],
            firstname: $validated['firstname'],
            licenceNumber: $validated['licence_number'] ?? null,
            hasToChangePassword: $validated['has_to_change_password'] ?? false
        );
    }

    public function toArray(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'user_type_id' => $this->userTypeId,
            'lastname' => $this->lastname,
            'firstname' => $this->firstname,
            'licence_number' => $this->licenceNumber,
            'has_to_change_password' => $this->hasToChangePassword,
        ];
    }
}
