<?php

namespace Tests\Unit;

use App\Domain\Statistics\Services\PlayerStatsService;
use App\Models\Encounter;
use App\Models\IndividualStat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    private PlayerStatsService $playerStatsService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->playerStatsService = app(PlayerStatsService::class);
    }

    /** @test */
    public function it_calculates_average_stats_correctly()
    {
        $user = User::factory()->create();
        $encounter1 = Encounter::factory()->create();
        $encounter2 = Encounter::factory()->create();

        // Match 1: 10 points, 5 rebounds
        $events1 = [
            ['type' => 'shoot', 'points' => 2, 'successful' => true],
            ['type' => 'shoot', 'points' => 2, 'successful' => true],
            ['type' => 'shoot', 'points' => 3, 'successful' => true],
            ['type' => 'shoot', 'points' => 3, 'successful' => true], // 10 pts
            ['type' => 'rebound'],
            ['type' => 'rebound'],
            ['type' => 'rebound'],
            ['type' => 'rebound'],
            ['type' => 'rebound'], // 5 rebs
        ];

        IndividualStat::factory()->create([
            'user_id' => $user->id,
            'encounter_id' => $encounter1->id,
            'json' => json_encode(['events' => $events1]),
        ]);

        // Match 2: 20 points, 1 rebound
        $events2 = [
            ['type' => 'shoot', 'points' => 2, 'successful' => true], // We'll just assume 10x 2pts for simplicity in logic if needed, but let's be explicit
            // Actually let's just put enough events
            // 10 baskets of 2pts
            ['type' => 'shoot', 'points' => 2, 'successful' => true],
            ['type' => 'shoot', 'points' => 2, 'successful' => true],
            ['type' => 'shoot', 'points' => 2, 'successful' => true],
            ['type' => 'shoot', 'points' => 2, 'successful' => true],
            ['type' => 'shoot', 'points' => 2, 'successful' => true],
            ['type' => 'shoot', 'points' => 2, 'successful' => true],
            ['type' => 'shoot', 'points' => 2, 'successful' => true],
            ['type' => 'shoot', 'points' => 2, 'successful' => true],
            ['type' => 'shoot', 'points' => 2, 'successful' => true], // 20 pts (10x 2pts)
            ['type' => 'rebound'], // 1 reb
        ];

        IndividualStat::factory()->create([
            'user_id' => $user->id,
            'encounter_id' => $encounter2->id,
            'json' => json_encode(['events' => $events2]),
        ]);

        $stats = $this->playerStatsService->getAverageStats($user);

        $this->assertEquals(2, $stats->matchesPlayed);

        // Points: (10 + 20) / 2 = 15
        $this->assertEquals(15.0, $stats->avgPoints);

        // Rebounds: (5 + 1) / 2 = 3
        $this->assertEquals(3.0, $stats->avgRebounds);
    }

    /** @test */
    public function it_returns_empty_stats_when_no_matches_played()
    {
        $user = User::factory()->create();

        $stats = $this->playerStatsService->getAverageStats($user);

        $this->assertEquals(0, $stats->matchesPlayed);
        $this->assertEquals(0.0, $stats->avgPoints);
    }
}
