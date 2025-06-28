<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\UserService;
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
        return response()->json($this->userService->getAllUsers());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'user_type_id' => 'required|exists:user_types,id',
            'lastname' => 'required',
            'firstname' => 'required',
            'licence_number' => 'nullable',
            'has_to_change_password' => 'boolean',
        ]);
        $validated['password'] = Hash::make($validated['password']);
        $user = $this->userService->createUser($validated);
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

        return response()->json($user);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'email' => ['email', Rule::unique('users')->ignore($id)],
            'password' => 'nullable|min:6',
            'user_type_id' => 'exists:user_types,id',
            'lastname' => 'sometimes|required',
            'firstname' => 'sometimes|required',
            'licence_number' => 'nullable',
            'has_to_change_password' => 'boolean',
        ]);

        $success = $this->userService->updateUser($id, $validated);

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
        $success = $this->userService->deleteUser($id);

        if (!$success) {
            return response()->json(['message' => 'Utilisateur non trouvé'], 404);
        }

        return response()->json(['message' => 'Utilisateur supprimé']);
    }
}
