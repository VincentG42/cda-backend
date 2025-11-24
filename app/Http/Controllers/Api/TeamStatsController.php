<?php

namespace App\Http\Controllers\Api;

use App\Domain\Statistics\Services\TeamStatsService;
use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\JsonResponse;

class TeamStatsController extends Controller
{
    protected $teamStatsService;

    public function __construct(TeamStatsService $teamStatsService)
    {
        $this->teamStatsService = $teamStatsService;
    }

    /**
     * @group Statistics
     *
     * @route GET /api/teams/{team}/stats/overview
     */
    public function getOverview(Team $team): JsonResponse
    {
        $stats = $this->teamStatsService->getSeasonOverview($team);

        return response()->json($stats);
    }

    public function getAnalysis(Team $team): JsonResponse
    {
        $analysis = $this->teamStatsService->getPointsConcededAnalysis($team);

        return response()->json($analysis);
    }

    public function getShooting(Team $team): JsonResponse
    {
        $shootingStats = $this->teamStatsService->getTeamShootingStats($team);

        return response()->json($shootingStats);
    }

    public function getPlayersStats(Team $team): JsonResponse
    {
        $stats = $this->teamStatsService->getTeamPlayersStats($team);

        return response()->json($stats);
    }

    public function getPeriodStats(Team $team): JsonResponse
    {
        $stats = $this->teamStatsService->getPeriodStats($team);

        return response()->json($stats);
    }
}
