<?php

namespace App\Services;

use App\DTOs\CreateUserDTO;
use App\DTOs\LoginDTO;
use App\DTOs\UpdateUserDTO;
use App\DTOs\UserFilterDTO;
use App\Mail\WelcomeEmail;
use App\Models\User;
use App\Repositories\UserRepositoryInterface;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class UserService
{
    public function __construct(
        private UserRepositoryInterface $userRepository
    ) {}

    public function getAllUsers(): Collection
    {
        return $this->userRepository->all();
    }

    public function getFilteredUsers(UserFilterDTO $dto): LengthAwarePaginator
    {
        $query = $this->userRepository->query()->with('userType');

        $query->when($dto->name, function ($q) use ($dto) {
            $q->where(function ($subQuery) use ($dto) {
                $subQuery->where('firstname', 'like', '%'.$dto->name.'%')
                    ->orWhere('lastname', 'like', '%'.$dto->name.'%');
            });
        });

        $query->when($dto->team_id, function ($q) use ($dto) {
            $q->whereHas('teams', function ($subQuery) use ($dto) {
                $subQuery->where('teams.id', $dto->team_id);
            });
        });

        $query->when($dto->user_type_id, function ($q) use ($dto) {
            $q->where('user_type_id', $dto->user_type_id);
        });

        return $query->paginate(15);
    }

    public function getUserById(int $id): ?User
    {
        return $this->userRepository->findWithUserType($id);
    }

    public function createUser(CreateUserDTO $dto): User
    {
        // Generate a random password
        $password = Str::password(10);

        $user = User::create([
            'email' => $dto->email,
            'password' => Hash::make($password),
            'lastname' => $dto->lastname,
            'firstname' => $dto->firstname,
            'licence_number' => $dto->licenceNumber,
            'user_type_id' => $dto->userTypeId,
            'has_to_change_password' => true,
        ]);

        // Send welcome email
        try {
            // Assuming frontend URL is in .env or hardcoded for now
            $loginUrl = config('app.frontend_url', 'http://localhost:5173') . '/login';
            Mail::to($user->email)->send(new WelcomeEmail($user, $password, $loginUrl));
        } catch (\Exception $e) {
            Log::error('Failed to send welcome email: ' . $e->getMessage());
        }

        return $user;
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
