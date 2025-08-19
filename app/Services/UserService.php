<?php

namespace App\Services;

use App\Repositories\UserRepositoryInterface;
use App\Models\User;
use App\DTOs\CreateUserDTO;
use App\DTOs\UpdateUserDTO;
use App\DTOs\LoginDTO;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getAllUsers(): Collection
    {
        return $this->userRepository->all();
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findWithUserType($id);
    }

    public function createUser(CreateUserDTO $dto): User
    {
        $data = $dto->toArray();

        // Logique métier : hash du mot de passe
        $data['password'] = Hash::make($data['password']);

        return $this->userRepository->create($data);
    }

    public function updateUser(int $id, UpdateUserDTO $dto): bool
    {
        $data = $dto->toArray();

        // Logique métier : hash du mot de passe si fourni
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        return $this->userRepository->update($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->userRepository->delete($id);
    }

    public function authenticateUser(LoginDTO $dto): ?User
    {
        $user = $this->userRepository->findByEmail($dto->email);

        if (!$user || !Hash::check($dto->password, $user->password)) {
            return null;
        }

        return $user;
    }
}
