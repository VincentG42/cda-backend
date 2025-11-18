<?php

namespace App\Domain\Statistics\Services;

use App\Domain\Statistics\DTOs\TeamStatsOverviewDTO;
use App\Models\Team;

class TeamStatsService
{
    public function getSeasonOverview(Team $team): TeamStatsOverviewDTO
    {
        $team->load('encounters');
        $playedEncounters = $team->encounters->whereNotNull('team_score');

        $matchesPlayed = $playedEncounters->count();
        $wins = $playedEncounters->where('is_victory', true)->count();
        $losses = $matchesPlayed - $wins;

        $winPercentage = $matchesPlayed > 0 ? round(($wins / $matchesPlayed) * 100, 1) : 0.0;

        $avgPointsFor = $playedEncounters->avg('team_score') ?? 0.0;
        $avgPointsAgainst = $playedEncounters->avg('opponent_score') ?? 0.0;

        return new TeamStatsOverviewDTO(
            matchesPlayed: $matchesPlayed,
            wins: $wins,
            losses: $losses,
            winPercentage: $winPercentage,
            avgPointsFor: round($avgPointsFor, 1),
            avgPointsAgainst: round($avgPointsAgainst, 1)
        );
    }

    public function getPointsConcededAnalysis(Team $team): array
    {
        $team->load('encounters');
        $playedEncounters = $team->encounters->whereNotNull('team_score');

        $avgInWins = $playedEncounters->where('is_victory', true)->avg('opponent_score');
        $avgInLosses = $playedEncounters->where('is_victory', false)->avg('opponent_score');

        return [
            'in_wins' => $avgInWins !== null ? round($avgInWins, 1) : null,
            'in_losses' => $avgInLosses !== null ? round($avgInLosses, 1) : null,
        ];
    }
}
