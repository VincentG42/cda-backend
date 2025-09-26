<?php

namespace App\Http\Controllers\Api;

use App\DTOs\CreateTeamDTO;
use App\DTOs\UpdateTeamDTO;
use App\Http\Controllers\Controller;
use App\Http\Resources\TeamResource;
use App\Models\Team;
use App\Models\User;
use App\Services\TeamService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use InvalidArgumentException;

class TeamController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private TeamService $teamService) {}

    public function index(): AnonymousResourceCollection
    {
        $this->authorize('viewAny', Team::class);
        $teams = $this->teamService->getAllTeams();

        return TeamResource::collection($teams);
    }

    public function store(Request $request): TeamResource
    {
        $this->authorize('create', Team::class);
        $dto = CreateTeamDTO::fromRequest($request);
        $team = $this->teamService->createTeam($dto);

        return new TeamResource($team);
    }

    public function show(Team $team): TeamResource
    {
        $this->authorize('view', $team);
        $team->load(['category', 'coach', 'season', 'users']);

        return new TeamResource($team);
    }

    public function update(Request $request, Team $team): TeamResource
    {
        $this->authorize('update', $team);
        $dto = UpdateTeamDTO::fromRequest($request);
        $this->teamService->updateTeam($team, $dto);

        return new TeamResource($team->fresh());
    }

    public function destroy(Team $team): JsonResponse
    {
        $this->authorize('delete', $team);
        $this->teamService->deleteTeam($team);

        return response()->json(null, 204);
    }

    public function addPlayer(Request $request, Team $team): JsonResponse|TeamResource
    {
        $this->authorize('update', $team);
        $validated = $request->validate(['user_id' => 'required|exists:users,id']);
        $user = User::findOrFail($validated['user_id']);

        try {
            $team = $this->teamService->addPlayer($team, $user);

            return new TeamResource($team);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function removePlayer(Team $team, User $player): JsonResponse
    {
        $this->authorize('update', $team);
        try {
            $this->teamService->removePlayer($team, $player);

            return response()->json(null, 204);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }

    public function assignCoach(Request $request, Team $team): JsonResponse|TeamResource
    {
        $this->authorize('update', $team);
        $validated = $request->validate(['user_id' => ['required', 'exists:users,id']]);
        $coach = User::findOrFail($validated['user_id']);

        try {
            $team = $this->teamService->assignCoach($team, $coach);

            return new TeamResource($team);
        } catch (InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], $e->getCode());
        }
    }
}
