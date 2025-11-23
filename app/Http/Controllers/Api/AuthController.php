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

        $user->load('teams');

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
        $user = $request->user();
        $user->load('teams');
        return response()->json($user);
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

        $allMatches = collect();
        foreach ($user->teams as $team) {
            $teamMatches = $team->encounters->map(function ($encounter) use ($team) {
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
                    'date' => $encounter->happens_at, // Keep original for sorting
                    'time' => \Carbon\Carbon::parse($encounter->happens_at)->format('H:i'),
                    'is_home_team' => $encounter->is_at_home,
                    'location' => $encounter->location ?? 'N/A',
                    'team_score' => $encounter->team_score,
                    'opponent_score' => $encounter->opponent_score,
                    'is_victory' => $encounter->is_victory === null ? null : (bool) $encounter->is_victory,
                ];
            });
            $allMatches = $allMatches->concat($teamMatches);
        }

        return response()->json($allMatches->sortBy('date')->values()->all());
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
            $user->load('teams.encounters');

            $allEncounters = $user->teams->flatMap(function ($team) {
                return $team->encounters;
            });

            $pastEncounters = $allEncounters->where('happens_at', '<', now())
                ->whereNotNull('team_score');

            $upcomingEncounters = $allEncounters->where('happens_at', '>=', now());

            $matchesPlayed = $pastEncounters->count();
            $wins = $pastEncounters->where('is_victory', true)->count();

            $nextMatch = $upcomingEncounters->sortBy('happens_at')->first();
            $nextMatchDate = $nextMatch ? $nextMatch->happens_at->format('d/m/Y') : 'N/A';

            // Placeholder for avgPoints as it requires deeper JSON processing
            $avgPoints = 0;

            // Upcoming Events (global)
            $upcomingEvents = \App\Models\Event::where('start_at', '>=', now())
                ->orderBy('start_at')
                ->limit(5)
                ->get();

            return response()->json([
                'matchesPlayed' => $matchesPlayed,
                'wins' => $wins,
                'nextMatchDate' => $nextMatchDate,
                'avgPoints' => $avgPoints,
                'upcomingMatches' => \App\Http\Resources\EncounterResource::collection($upcomingEncounters->take(5)),
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
