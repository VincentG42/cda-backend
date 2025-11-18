<?php

namespace App\Domain\Statistics\DTOs;

class TeamStatsOverviewDTO
{
    public function __construct(
        public readonly int $matchesPlayed,
        public readonly int $wins,
        public readonly int $losses,
        public readonly float $winPercentage,
        public readonly float $avgPointsFor,
        public readonly float $avgPointsAgainst,
    ) {}
}
