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
     * @route GET /api/teams/{team}/stats/overview
     * @param Team $team
     * @return JsonResponse
     */
    public function getOverview(Team $team): JsonResponse
    {
        $stats = $this->teamStatsService->getSeasonOverview($team);
        return response()->json($stats);
    }
}
