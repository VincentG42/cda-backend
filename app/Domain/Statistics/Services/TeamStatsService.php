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

    public function getTeamShootingStats(Team $team): array
    {
        // Load all encounters for the team
        $team->load('encounters.individualStats');

        $totals = [
            'fgm' => 0, 'fga' => 0,
            '3pm' => 0, '3pa' => 0,
            'ftm' => 0, 'fta' => 0,
        ];

        foreach ($team->encounters as $encounter) {
            foreach ($encounter->individualStats as $statRecord) {
                // Ensure the stat belongs to a player of this team (though individualStats on encounter should be correct, 
                // but strictly speaking individualStats table links user and encounter. 
                // We assume encounter->individualStats contains stats for players in that encounter.
                // Since we are looking at team->encounters, these are encounters WHERE the team participated.
                // However, individual_stats table doesn't explicitly say which team the user belonged to at that time 
                // (though usually implied). 
                // For safety, we can check if the user is currently in the team, but players might have left.
                // A safer bet is to assume all individual stats linked to an encounter of this team 
                // ARE for this team's players if the app logic enforces it.
                // Let's proceed with parsing the JSON.

                $data = json_decode($statRecord->json, true);
                $events = $data['events'] ?? [];

                foreach ($events as $event) {
                    if ($event['type'] === 'shoot') {
                        $points = $event['points'];
                        $isSuccessful = $event['successful'];

                        if ($points === 1) {
                            $totals['fta']++;
                            if ($isSuccessful) $totals['ftm']++;
                        } elseif ($points === 2) {
                            $totals['fga']++;
                            if ($isSuccessful) $totals['fgm']++;
                        } elseif ($points === 3) {
                            $totals['fga']++;
                            $totals['3pa']++;
                            if ($isSuccessful) {
                                $totals['fgm']++;
                                $totals['3pm']++;
                            }
                        }
                    }
                }
            }
        }

        return [
            'fg_percentage' => $this->calculatePercentage($totals['fgm'], $totals['fga']),
            'three_pt_percentage' => $this->calculatePercentage($totals['3pm'], $totals['3pa']),
            'ft_percentage' => $this->calculatePercentage($totals['ftm'], $totals['fta']),
            'details' => $totals
        ];
    }

    private function calculatePercentage(int $made, int $attempted): float
    {
        if ($attempted === 0) {
            return 0.0;
        }

        return round(($made / $attempted) * 100, 1);
    }
}
