<?php

namespace App\Http\Controllers\Api;

use App\DTOs\LoginDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Http\Resources\UserResource;
use App\Models\UserType;
use App\Notifications\ResetPasswordNotification;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Notification;
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
            'user' => new UserResource($user),
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
        $team = $user->teams()->with(['users', 'coach.userType', 'category'])->first();

        if (! $team) {
            return response()->json(null, 200);
        }

        return new TeamResource($team);
    }

    public function myMatches(Request $request)
    {
        $user = $request->user();
        $user->load('teams.encounters.team'); // Load the user's team for each encounter

        $upcomingMatches = collect();
        foreach ($user->teams as $team) {
            $teamUpcomingMatches = $team->encounters->filter(function ($encounter) {
                return $encounter->happens_at > now();
            })->map(function ($encounter) use ($team) {
                $homeTeamName = $encounter->is_at_home ? $team->name : $encounter->opponent;
                $awayTeamName = $encounter->is_at_home ? $encounter->opponent : $team->name;

                return [
                    'id' => $encounter->id,
                    'home_team' => [
                        'name' => $homeTeamName,
                    ],
                    'away_team' => [
                        'name' => $awayTeamName,
                    ],
                    'happens_at' => $encounter->happens_at,
                    'is_home_team' => $encounter->is_at_home,
                    'location' => $encounter->location ?? 'N/A', // Assuming location field exists or default
                ];
            });
            $upcomingMatches = $upcomingMatches->concat($teamUpcomingMatches);
        }

        return response()->json($upcomingMatches->sortBy('happens_at')->values()->all());
    }

    public function myDashboard(Request $request)
    {
        $user = $request->user();

        // Determine if the user has an admin-level role
        $isAdminOrStaff = in_array($user->userType->name, [
            UserType::ADMIN,
            UserType::PRESIDENT,
            UserType::STAFF,
            UserType::COACH,
        ]);

        if ($isAdminOrStaff) {
            // Admin Dashboard Stats
            $totalUsers = \App\Models\User::count();
            $activeTeams = \App\Models\Team::count();
            $matchesThisMonth = \App\Models\Encounter::whereMonth('happens_at', now()->month)->count();

            $upcomingEvents = \App\Models\Event::where('start_at', '>=', now())
                ->orderBy('start_at')
                ->limit(5)
                ->get();

            return response()->json([
                'totalUsers' => $totalUsers,
                'activeTeams' => $activeTeams,
                'matchesThisMonth' => $matchesThisMonth,
                'upcomingEventsCount' => $upcomingEvents->count(),
                'upcomingEvents' => $upcomingEvents, // For admin dashboard events list
            ]);
        } else {
            // User Dashboard Stats
            $user->load('teams.encounters.team');

            $upcomingMatches = collect();
            foreach ($user->teams as $team) {
                $teamUpcomingMatches = $team->encounters->filter(function ($encounter) {
                    return $encounter->happens_at > now();
                })->map(function ($encounter) use ($team) {
                    $homeTeamName = $encounter->is_at_home ? $team->name : $encounter->opponent;
                    $awayTeamName = $encounter->is_at_home ? $encounter->opponent : $team->name;

                    return [
                        'id' => $encounter->id,
                        'home_team' => [
                            'name' => $homeTeamName,
                            'id' => $encounter->is_at_home ? $team->id : null,
                        ],
                        'away_team' => [
                            'name' => $awayTeamName,
                            'id' => $encounter->is_at_home ? null : $team->id,
                        ],
                        'happens_at' => $encounter->happens_at,
                        'is_home_team' => $encounter->is_at_home,
                        'location' => $encounter->location ?? 'N/A',
                    ];
                });
                $upcomingMatches = $upcomingMatches->concat($teamUpcomingMatches);
            }

            $upcomingMatches = $upcomingMatches->sortBy('happens_at')->take(5)->values()->all();

            // Placeholder for individual stats for now
            $matchesPlayed = 0;
            $wins = 0;
            $nextMatchDate = 'N/A';
            $avgPoints = 0;

            // Upcoming Events (global, as before)
            $upcomingEvents = \App\Models\Event::where('start_at', '>=', now())
                ->orderBy('start_at')
                ->limit(5)
                ->get();

            return response()->json([
                'matchesPlayed' => $matchesPlayed,
                'wins' => $wins,
                'nextMatchDate' => $nextMatchDate,
                'avgPoints' => $avgPoints,
                'upcomingMatches' => $upcomingMatches,
                'recentEvents' => $upcomingEvents,
            ]);
        }
    }

    public function forgotPassword(Request $request)
    {
        $validated = $request->validate(['email' => 'required|email|exists:users,email']);

        $user = \App\Models\User::where('email', $validated['email'])->first();
        if (! $user) {
            return response()->json(['message' => __('passwords.user')], 422);
        }

        $token = Password::createToken($user);
        Notification::send($user, new ResetPasswordNotification($token));

        return response()->json(['message' => __('passwords.sent')], 200);
    }

    public function resetPassword(Request $request)
    {
        $validated = $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:8',
        ]);

        $status = Password::reset(
            [
                'email' => $validated['email'],
                'password' => $validated['password'],
                'password_confirmation' => $request->input('password_confirmation'),
                'token' => $validated['token'],
            ],
            function ($user) use ($validated) {
                $user->forceFill([
                    'password' => bcrypt($validated['password']),
                ])->save();
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return response()->json(['message' => __($status)], 200);
        }

        return response()->json(['message' => __($status)], 422);
    }
}
