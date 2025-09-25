<?php

namespace App\Http\Controllers\Api;

use App\DTOs\LoginDTO;
use App\Http\Controllers\Controller;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

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

    public function myTeams(Request $request)
    {
        $user = $request->user();
        $user->load(['teams.users', 'teams.encounters']); // Load teams, their players, and their encounters

        return response()->json($user->teams);
    }

    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $status = Password::sendResetLink(
            $request->only('email')
        );

        if ($status === Password::RESET_LINK_SENT) {
            return response()->json(['message' => __($status)], 200);
        }

        return response()->json(['message' => __($status)], 500);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user) use ($request) {
                $user->forceFill([
                    'password' => bcrypt($request->password),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 200);
        }

        return response()->json(['message' => __($status)], 500);
    }
}
