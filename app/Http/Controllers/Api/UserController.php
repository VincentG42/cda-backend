<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
use App\DTOs\CreateUserDTO;
use App\DTOs\UpdateUserDTO;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $this->authorize('viewAny', User::class);
        return response()->json($this->userService->getAllUsers());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $this->authorize('create', User::class);
        $dto = CreateUserDTO::fromRequest($request);
        $user = $this->userService->createUser($dto);

        return response()->json($user, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $this->authorize('view', $user);

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $this->authorize('update', $user);

        $dto = UpdateUserDTO::fromRequest($request, $id);

        if (!$dto->hasData()) {
            return response()->json(['message' => 'Aucune donnée à mettre à jour'], 400);
        }

        $success = $this->userService->updateUser($id, $dto);

        if (!$success) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $user = $this->userService->getUserById($id);
        return response()->json($user);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $user = $this->userService->getUserById($id);

        if (!$user) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        $this->authorize('delete', $user);

        $success = $this->userService->deleteUser($id);

        if (!$success) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        return response()->json(['message' => 'Utilisateur supprimé']);
    }
}
