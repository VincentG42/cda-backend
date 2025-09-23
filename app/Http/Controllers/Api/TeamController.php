<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateTeamDTO;
use App\DTOs\UpdateTeamDTO;
use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        $teams = Team::with(['category', 'coach', 'season'])->get();

        return response()->json($teams);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        $dto = CreateTeamDTO::fromRequest($request);
        $team = Team::create($dto->toArray());

        return response()->json($team, 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Team $team): JsonResponse
    {
        $team->load(['category', 'coach', 'season', 'users']); // Changed to 'users'

        return response()->json($team);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Team $team): JsonResponse
    {
        $dto = UpdateTeamDTO::fromRequest($request);
        $team->update($dto->toArray());

        return response()->json($team);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Team $team): JsonResponse
    {
        $team->delete();

        return response()->json(null, 204);
    }

    /**
     * Add a player to the team.
     */
    public function addPlayer(Request $request, Team $team): JsonResponse
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['user_id']);

        if ($team->users()->where('user_id', $user->id)->exists()) { // Changed to 'users'
            return response()->json(['message' => 'Player is already in the team.'], 409);
        }

        $team->users()->attach($user->id); // Changed to 'users'

        return response()->json($team->load('users')); // Changed to 'users'
    }

    /**
     * Remove a player from the team.
     */
    public function removePlayer(Team $team, User $player): JsonResponse
    {
        if (! $team->users()->where('user_id', $player->id)->exists()) { // Changed to 'users'
            return response()->json(['message' => 'Player not found in team.'], 404);
        }

        $team->users()->detach($player->id); // Changed to 'users'

        return response()->json(null, 204);
    }
}
