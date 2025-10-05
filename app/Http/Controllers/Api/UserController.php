<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateUserDTO;
use App\DTOs\UpdateUserDTO;
use App\DTOs\UserFilterDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Services\UserService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $this->authorize('viewAny', User::class);

        $filterDto = UserFilterDTO::fromRequest($request);
        $users = $this->userService->getFilteredUsers($filterDto);

        // Ensure userType is loaded for resource expectations
        $users->load('userType', 'teams.category');

        return UserResource::collection($users);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $dto = CreateUserDTO::fromRequest($request);
        $user = $this->userService->createUser($dto);

        return new UserResource($user);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = $this->userService->getUserById($id);

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $this->authorize('view', $user);

        return new UserResource($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = $this->userService->getUserById($id);

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $this->authorize('update', $user);

        $dto = UpdateUserDTO::fromRequest($request, $id);

        if (! $dto->hasData()) {
            return response()->json(['message' => 'Aucune donnée à mettre à jour'], 400);
        }

        $success = $this->userService->updateUser($id, $dto);

        if (! $success) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $user = $this->userService->getUserById($id);

        return new UserResource($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = $this->userService->getUserById($id);

        if (! $user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $this->authorize('delete', $user);

        $success = $this->userService->deleteUser($id);

        if (! $success) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        return response()->json(['message' => 'Utilisateur supprimé']);
    }
}
