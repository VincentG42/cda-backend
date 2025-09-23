<?php

namespace App\Http\Controllers\Api;

use App\DTOs\LoginDTO;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;

class AuthController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    public function login(Request $request)
    {
        $dto = LoginDTO::fromRequest($request);
        $user = $this->userService->authenticateUser($dto);

        if (! $user) {
            return response()->json([
                'message' => 'Identifiants invalides',
            ], 401);
        }

        $token = $user->createToken('auth-token')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Déconnexion réussie',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
