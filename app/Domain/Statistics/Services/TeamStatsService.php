<?php

namespace App\Domain\Statistics\Services;

use App\Domain\Statistics\DTOs\TeamStatsOverviewDTO;
use App\Models\Team;

class TeamStatsService
{
    public function __construct(
        private PlayerStatsService $playerStatsService
    ) {}

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
                            if ($isSuccessful) {
                                $totals['ftm']++;
                            }
                        } elseif ($points === 2) {
                            $totals['fga']++;
                            if ($isSuccessful) {
                                $totals['fgm']++;
                            }
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
            'details' => $totals,
        ];
    }

    private function calculatePercentage(int $made, int $attempted): float
    {
        if ($attempted === 0) {
            return 0.0;
        }

        return round(($made / $attempted) * 100, 1);
    }

    public function getTeamPlayersStats(Team $team): array
    {
        $team->load('users');
        $playersStats = [];

        foreach ($team->users as $user) {
            $statsDTO = $this->playerStatsService->getAverageStats($user);

            $playersStats[] = [
                'id' => $user->id,
                'firstname' => $user->firstname,
                'lastname' => $user->lastname,
                'matchesPlayed' => $statsDTO->matchesPlayed,
                'avgPoints' => $statsDTO->avgPoints,
                'avgRebounds' => $statsDTO->avgRebounds,
                'avgAssists' => $statsDTO->avgAssists,
                'avgSteals' => $statsDTO->avgSteals,
                'avgTurnovers' => $statsDTO->avgTurnovers,
                'avgFouls' => $statsDTO->avgFouls,
                'fgPercentage' => $statsDTO->fgPercentage,
                'threePtPercentage' => $statsDTO->threePtPercentage,
                'ftPercentage' => $statsDTO->ftPercentage,
            ];
        }

        // Sort by average points descending
        usort($playersStats, function ($a, $b) {
            return $b['avgPoints'] <=> $a['avgPoints'];
        });

        return $playersStats;
    }

    public function getPeriodStats(Team $team): array
    {
        $team->load('encounters.encounterStats');

        $periodTotals = [
            1 => ['team' => 0, 'opponent' => 0],
            2 => ['team' => 0, 'opponent' => 0],
            3 => ['team' => 0, 'opponent' => 0],
            4 => ['team' => 0, 'opponent' => 0],
        ];

        $matchCount = 0;

        foreach ($team->encounters as $encounter) {
            // Only process matches with stats
            if ($encounter->encounterStats->isEmpty()) {
                continue;
            }

            $statRecord = $encounter->encounterStats->first();
            $data = json_decode($statRecord->json, true);
            $events = $data['events'] ?? [];

            // We need to identify team players to distinguish team points from opponent points
            // The JSON usually contains 'playerId'. We can check if this ID maps to a user in our team.
            // However, the JSON might not have mapped IDs yet if it's raw.
            // But wait, the 'events' in EncounterStat are usually the raw events from the app.
            // In the app, 'team' events usually have a specific flag or we check the player list.
            // Let's assume for now that we can sum up points based on the 'team' vs 'opponent' logic found in the import service.
            // Actually, the import service calculates total score but doesn't explicitly tag every event as 'team' or 'opponent' in the DB
            // other than by player association.

            // A simpler approach for the 'Team' score is to sum points of players who are in the team.
            // But we might not have the historical roster.
            // Let's look at the JSON structure again. Usually, there is a 'teamId' or similar in events,
            // or we have a list of players in the JSON with their team association.

            // Let's try to infer from 'players' array in JSON if available
            $jsonPlayers = collect($data['players'] ?? []);
            // In the standard JSON format from the app, players usually belong to the main team being tracked.
            // Opponent points might be recorded differently or as 'opponent' events.

            // If the app only tracks OUR team's stats in detail (which is common for these apps),
            // we might only have detailed period stats for the TEAM.
            // Opponent score is often just a global counter or period counter if the app supports it.

            // Let's check if 'events' have a 'period' field. Yes, PlayerStatsService uses it.
            // Let's assume all 'shoot' events in the JSON are for the TEAM.
            // Does the JSON contain opponent scores?
            // Often there are 'opponent_score' events or similar.

            $hasData = false;

            $currentPeriod = 1;

            foreach ($events as $event) {
                // Update current period if present in the event
                if (isset($event['period']) && $event['period'] >= 1 && $event['period'] <= 4) {
                    $currentPeriod = $event['period'];
                }

                // Use event period or fallback to current tracked period
                $period = $event['period'] ?? $currentPeriod;

                if ($period > 4) {
                    continue;
                }

                if ($event['type'] === 'shoot' && ($event['successful'] ?? false)) {
                    $points = $event['points'];
                    $periodTotals[$period]['team'] += $points;
                    $hasData = true;
                }

                // Handle opponent scores
                // Based on logs, type is 'opponent_score' and it has 'points'
                // It might lack 'period', so we use $currentPeriod
                if (($event['type'] === 'opponent_score' || $event['type'] === 'opponent_shoot') && ($event['successful'] ?? true)) {
                    $points = $event['points'] ?? $event['value'] ?? 0;
                    $periodTotals[$period]['opponent'] += $points;
                }
            }

            if ($hasData) {
                $matchCount++;
            }
        }

        // Calculate averages
        $averages = [];
        $cumulativeTeam = 0;
        $cumulativeOpponent = 0;
        $evolution = [];

        foreach ($periodTotals as $period => $totals) {
            $avgTeam = $matchCount > 0 ? round($totals['team'] / $matchCount, 1) : 0;
            $avgOpponent = $matchCount > 0 ? round($totals['opponent'] / $matchCount, 1) : 0;

            $averages[$period] = [
                'team' => $avgTeam,
                'opponent' => $avgOpponent,
            ];

            $cumulativeTeam += $avgTeam;
            $cumulativeOpponent += $avgOpponent;

            $evolution[] = [
                'period' => 'Q'.$period,
                'team_score' => $cumulativeTeam,
                'opponent_score' => $cumulativeOpponent,
                'diff' => round($cumulativeTeam - $cumulativeOpponent, 1),
            ];
        }

        return [
            'per_period' => $averages,
            'evolution' => $evolution,
            'matches_analyzed' => $matchCount,
        ];
    }
}
