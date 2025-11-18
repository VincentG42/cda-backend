<?php

namespace App\Http\Controllers\Api;

use App\Domain\Statistics\Services\PlayerStatsService;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class PlayerStatsController extends Controller
{
    public function __construct(
        private PlayerStatsService $playerStatsService
    ) {}

    public function getAverages(User $user): JsonResponse
    {
        $stats = $this->playerStatsService->getAverageStats($user);

        return response()->json($stats);
    }

    public function getHistorical(User $user, string $stat): JsonResponse
    {
        $allowedStats = ['points', 'rebounds', 'assists', 'steals', 'turnovers', 'fouls'];
        if (! in_array($stat, $allowedStats)) {
            return response()->json(['error' => 'Invalid stat requested.'], 400);
        }

        $historicalData = $this->playerStatsService->getHistoricalStats($user, $stat);

        return response()->json($historicalData);
    }

    public function getMatchStats(User $user, \App\Models\Encounter $encounter): JsonResponse
    {
        $matchStats = $this->playerStatsService->getMatchStatsByPeriod($user, $encounter);

        return response()->json($matchStats);
    }
}
