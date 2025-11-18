<?php

namespace App\Domain\Statistics\DTOs;

class PlayerStatsDTO
{
    public function __construct(
        public readonly int $matchesPlayed,
        public readonly float $avgPoints,
        public readonly float $avgRebounds,
        public readonly float $avgAssists,
        public readonly float $avgSteals,
        public readonly float $avgTurnovers,
        public readonly float $avgFouls,
        public readonly float $fgPercentage,
        public readonly float $threePtPercentage,
        public readonly float $ftPercentage,
    ) {}
}
