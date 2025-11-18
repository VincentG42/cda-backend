<?php

namespace App\Domain\Statistics\Services;

use App\Domain\Statistics\DTOs\PlayerStatsDTO;
use App\Models\User;
use Illuminate\Support\Collection;

class PlayerStatsService
{
    /**
     * Calculate the average performance statistics for a given player.
     */
    public function getAverageStats(User $player): PlayerStatsDTO
    {
        // Eager load individual stats to avoid N+1 queries
        $player->load('individualStats');
        $statsCollection = $player->individualStats;

        if ($statsCollection->isEmpty()) {
            return $this->emptyStats();
        }

        $matchesPlayed = $statsCollection->count();

        $totals = [
            'points' => 0,
            'rebounds' => 0,
            'assists' => 0,
            'steals' => 0,
            'turnovers' => 0,
            'fouls' => 0,
            'fgm' => 0, // Field Goals Made
            'fga' => 0, // Field Goals Attempted
            '3pm' => 0, // 3-Pointers Made
            '3pa' => 0, // 3-Pointers Attempted
            'ftm' => 0, // Free Throws Made
            'fta' => 0, // Free Throws Attempted
        ];

        foreach ($statsCollection as $statRecord) {
            $data = json_decode($statRecord->json, true);
            $events = $data['events'] ?? [];

            foreach ($events as $event) {
                switch ($event['type']) {
                    case 'rebound':
                        $totals['rebounds']++;
                        break;
                    case 'pass':
                        $totals['assists']++;
                        break;
                    case 'steal':
                        $totals['steals']++;
                        break;
                    case 'turnover':
                        $totals['turnovers']++;
                        break;
                    case 'foul':
                        $totals['fouls']++;
                        break;
                    case 'shoot':
                        $this->processShootEvent($event, $totals);
                        break;
                }
            }
        }

        return new PlayerStatsDTO(
            matchesPlayed: $matchesPlayed,
            avgPoints: $this->calculateAverage($totals['points'], $matchesPlayed),
            avgRebounds: $this->calculateAverage($totals['rebounds'], $matchesPlayed),
            avgAssists: $this->calculateAverage($totals['assists'], $matchesPlayed),
            avgSteals: $this->calculateAverage($totals['steals'], $matchesPlayed),
            avgTurnovers: $this->calculateAverage($totals['turnovers'], $matchesPlayed),
            avgFouls: $this->calculateAverage($totals['fouls'], $matchesPlayed),
            fgPercentage: $this->calculatePercentage($totals['fgm'], $totals['fga']),
            threePtPercentage: $this->calculatePercentage($totals['3pm'], $totals['3pa']),
            ftPercentage: $this->calculatePercentage($totals['ftm'], $totals['fta'])
        );
    }

    /**
     * Get historical data for a specific stat for a player.
     */
    public function getHistoricalStats(User $player, string $statName): Collection
    {
        $player->load('individualStats.encounter');

        return $player->individualStats
            ->sortBy(function ($statRecord) {
                return $statRecord->encounter->happens_at;
            })
            ->map(function ($statRecord) use ($statName) {
                $data = json_decode($statRecord->json, true);
                $events = collect($data['events'] ?? []);

                $value = match ($statName) {
                    'points' => $events->where('type', 'shoot')->where('successful', true)->sum('points'),
                    'rebounds' => $events->where('type', 'rebound')->count(),
                    'assists' => $events->where('type', 'pass')->count(),
                    'steals' => $events->where('type', 'steal')->count(),
                    'turnovers' => $events->where('type', 'turnover')->count(),
                    'fouls' => $events->where('type', 'foul')->count(),
                    default => 0,
                };

                return [
                    'match_date' => $statRecord->encounter->happens_at->format('Y-m-d'),
                    'value' => $value,
                ];
            })
            ->values();
    }

    public function getMatchStatsByPeriod(User $player, Encounter $encounter): array
    {
        $statRecord = $player->individualStats()->where('encounter_id', $encounter->id)->first();

        if (! $statRecord) {
            return [];
        }

        $data = json_decode($statRecord->json, true);
        $events = collect($data['events'] ?? []);

        $groupedByPeriod = $events->groupBy('period');

        $periodStats = [];
        for ($i = 1; $i <= 4; $i++) { // Assuming 4 periods
            $periodEvents = $groupedByPeriod->get($i, collect());
            $periodTotals = [
                'period' => $i,
                'points' => 0, 'rebounds' => 0, 'assists' => 0, 'steals' => 0, 'turnovers' => 0, 'fouls' => 0,
            ];

            foreach ($periodEvents as $event) {
                switch ($event['type']) {
                    case 'rebound':
                        $periodTotals['rebounds']++;
                        break;
                    case 'pass':
                        $periodTotals['assists']++;
                        break;
                    case 'steal':
                        $periodTotals['steals']++;
                        break;
                    case 'turnover':
                        $periodTotals['turnovers']++;
                        break;
                    case 'foul':
                        $periodTotals['fouls']++;
                        break;
                    case 'shoot':
                        if ($event['successful']) {
                            $periodTotals['points'] += $event['points'];
                        }
                        break;
                }
            }
            $periodStats[] = $periodTotals;
        }

        return $periodStats;
    }

    /**
     * Helper to process a 'shoot' event and update totals.
     */
    private function processShootEvent(array $event, array &$totals): void
    {
        $points = $event['points'];
        $isSuccessful = $event['successful'];

        if ($points === 1) {
            $totals['fta']++;
            if ($isSuccessful) {
                $totals['ftm']++;
                $totals['points']++;
            }
        } elseif ($points === 2) {
            $totals['fga']++;
            if ($isSuccessful) {
                $totals['fgm']++;
                $totals['points'] += 2;
            }
        } elseif ($points === 3) {
            $totals['fga']++;
            $totals['3pa']++;
            if ($isSuccessful) {
                $totals['fgm']++;
                $totals['3pm']++;
                $totals['points'] += 3;
            }
        }
    }

    /**
     * Calculate an average, avoiding division by zero.
     */
    private function calculateAverage(int $total, int $count): float
    {
        if ($count === 0) {
            return 0.0;
        }

        return round($total / $count, 1);
    }

    /**
     * Calculate a percentage, avoiding division by zero.
     */
    private function calculatePercentage(int $made, int $attempted): float
    {
        if ($attempted === 0) {
            return 0.0;
        }

        return round(($made / $attempted) * 100, 1);
    }

    /**
     * Return a DTO with zeroed stats for players with no data.
     */
    private function emptyStats(): PlayerStatsDTO
    {
        return new PlayerStatsDTO(0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0, 0.0);
    }
}
