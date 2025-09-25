<?php

namespace App\Services;

use App\DTOs\CreateUserDTO;
use App\DTOs\LoginDTO;
use App\DTOs\UpdateUserDTO;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
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

    public function getFilteredUsers(Request $request): Collection
    {
        $query = $this->userRepository->query();

        $query->when($request->has('name'), function ($q) use ($request) {
            $name = $request->input('name');
            $q->where(function ($subQuery) use ($name) {
                $subQuery->where('firstname', 'like', '%'.$name.'%')
                    ->orWhere('lastname', 'like', '%'.$name.'%');
            });
        });

        $query->when($request->has('team_id'), function ($q) use ($request) {
            $q->whereHas('teams', function ($subQuery) use ($request) {
                $subQuery->where('teams.id', $request->input('team_id'));
            });
        });

        $query->when($request->has('user_type_id'), function ($q) use ($request) {
            $q->where('user_type_id', $request->input('user_type_id'));
        });

        return $query->get();
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

        if (! $user || ! Hash::check($dto->password, $user->password)) {
            return null;
        }

        return $user;
    }
}
