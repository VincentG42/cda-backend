<?php

namespace App\DTOs;

use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UpdateUserDTO
{
    public function __construct(
        public readonly ?string $email,
        public readonly ?string $password,
        public readonly ?int $userTypeId,
        public readonly ?string $lastname,
        public readonly ?string $firstname,
        public readonly ?string $licenceNumber,
        public readonly ?bool $hasToChangePassword
    ) {}

    public static function fromRequest(Request $request, int $userId): self
    {
        $validated = $request->validate([
            'email' => ['nullable', 'email', Rule::unique('users')->ignore($userId)],
            'password' => 'nullable|min:6',
            'user_type_id' => 'nullable|exists:user_types,id',
            'lastname' => 'nullable|string',
            'firstname' => 'nullable|string',
            'licence_number' => 'nullable|string',
            'has_to_change_password' => 'nullable|boolean',
        ]);

        return new self(
            email: $validated['email'] ?? null,
            password: $validated['password'] ?? null,
            userTypeId: $validated['user_type_id'] ?? null,
            lastname: $validated['lastname'] ?? null,
            firstname: $validated['firstname'] ?? null,
            licenceNumber: $validated['licence_number'] ?? null,
            hasToChangePassword: $validated['has_to_change_password'] ?? null
        );
    }

    public function toArray(): array
    {
        $data = [];

        if ($this->email !== null) $data['email'] = $this->email;
        if ($this->password !== null) $data['password'] = $this->password;
        if ($this->userTypeId !== null) $data['user_type_id'] = $this->userTypeId;
        if ($this->lastname !== null) $data['lastname'] = $this->lastname;
        if ($this->firstname !== null) $data['firstname'] = $this->firstname;
        if ($this->licenceNumber !== null) $data['licence_number'] = $this->licenceNumber;
        if ($this->hasToChangePassword !== null) $data['has_to_change_password'] = $this->hasToChangePassword;

        return $data;
    }

    public function hasData(): bool
    {
        return !empty($this->toArray());
    }
}
