<?php

namespace Tests\Unit;

use App\Domain\Statistics\Services\PlayerStatsService;
use App\Domain\Statistics\Services\TeamStatsService;
use App\Models\Encounter;
use App\Models\EncounterStat;
use App\Models\IndividualStat;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TeamStatsServiceTest extends TestCase
{
    use RefreshDatabase;

    private TeamStatsService $teamStatsService;

    protected function setUp(): void
    {
        parent::setUp();
        // We can mock PlayerStatsService if needed, but for now we can use the real one
        // or just let the container resolve it.
        $this->teamStatsService = app(TeamStatsService::class);
    }

    /** @test */
    public function it_calculates_season_overview_correctly()
    {
        $team = Team::factory()->create();

        // Win: 80 - 70
        Encounter::factory()->create([
            'team_id' => $team->id,
            'team_score' => 80,
            'opponent_score' => 70,
            'is_victory' => true,
        ]);

        // Loss: 60 - 70
        Encounter::factory()->create([
            'team_id' => $team->id,
            'team_score' => 60,
            'opponent_score' => 70,
            'is_victory' => false,
        ]);

        $overview = $this->teamStatsService->getSeasonOverview($team);

        $this->assertEquals(2, $overview->matchesPlayed);
        $this->assertEquals(1, $overview->wins);
        $this->assertEquals(1, $overview->losses);
        $this->assertEquals(50.0, $overview->winPercentage);
        $this->assertEquals(70.0, $overview->avgPointsFor); // (80+60)/2
        $this->assertEquals(70.0, $overview->avgPointsAgainst); // (70+70)/2
    }

    /** @test */
    public function it_calculates_team_shooting_stats_correctly()
    {
        $team = Team::factory()->create();
        $encounter = Encounter::factory()->create(['team_id' => $team->id]);
        $user = User::factory()->create();

        // Create individual stats with shooting events
        // 1x 2pt made
        // 1x 2pt missed
        // 1x 3pt made
        // 1x 1pt made (FT)
        $events = [
            ['type' => 'shoot', 'points' => 2, 'successful' => true],
            ['type' => 'shoot', 'points' => 2, 'successful' => false],
            ['type' => 'shoot', 'points' => 3, 'successful' => true],
            ['type' => 'shoot', 'points' => 1, 'successful' => true],
        ];

        IndividualStat::factory()->create([
            'encounter_id' => $encounter->id,
            'user_id' => $user->id,
            'json' => json_encode(['events' => $events]),
        ]);

        // We need to attach the user to the team if the service logic relies on it,
        // but looking at getTeamShootingStats, it iterates over team->encounters->individualStats.
        // It doesn't strictly check if the user is currently in the team, just that the stat is linked to the encounter.

        $stats = $this->teamStatsService->getTeamShootingStats($team);

        // FGM: 1 (2pt) + 1 (3pt) = 2
        // FGA: 2 (2pt) + 1 (3pt) = 3
        // 3PM: 1
        // 3PA: 1
        // FTM: 1
        // FTA: 1

        $this->assertEquals(2, $stats['details']['fgm']);
        $this->assertEquals(3, $stats['details']['fga']);
        $this->assertEquals(66.7, $stats['fg_percentage']); // 2/3 * 100

        $this->assertEquals(1, $stats['details']['3pm']);
        $this->assertEquals(1, $stats['details']['3pa']);
        $this->assertEquals(100.0, $stats['three_pt_percentage']);

        $this->assertEquals(1, $stats['details']['ftm']);
        $this->assertEquals(1, $stats['details']['fta']);
        $this->assertEquals(100.0, $stats['ft_percentage']);
    }

    /** @test */
    public function it_calculates_period_stats_correctly()
    {
        $team = Team::factory()->create();
        $encounter = Encounter::factory()->create(['team_id' => $team->id]);

        // Period 1: Team scores 10, Opponent scores 5
        // Period 2: Team scores 5, Opponent scores 10
        $events = [
            // Q1 Team
            ['type' => 'shoot', 'points' => 2, 'successful' => true, 'period' => 1],
            ['type' => 'shoot', 'points' => 3, 'successful' => true, 'period' => 1],
            ['type' => 'shoot', 'points' => 2, 'successful' => true, 'period' => 1],
            ['type' => 'shoot', 'points' => 3, 'successful' => true, 'period' => 1], // Total 10

            // Q1 Opponent
            ['type' => 'opponent_score', 'points' => 5, 'successful' => true, 'period' => 1],

            // Q2 Team
            ['type' => 'shoot', 'points' => 2, 'successful' => true, 'period' => 2],
            ['type' => 'shoot', 'points' => 3, 'successful' => true, 'period' => 2], // Total 5

            // Q2 Opponent
            ['type' => 'opponent_score', 'points' => 10, 'successful' => true, 'period' => 2],
        ];

        EncounterStat::factory()->create([
            'encounter_id' => $encounter->id,
            'json' => json_encode(['events' => $events]),
        ]);

        $stats = $this->teamStatsService->getPeriodStats($team);

        // Check Q1
        $this->assertEquals(10, $stats['per_period'][1]['team']);
        $this->assertEquals(5, $stats['per_period'][1]['opponent']);

        // Check Q2
        $this->assertEquals(5, $stats['per_period'][2]['team']);
        $this->assertEquals(10, $stats['per_period'][2]['opponent']);

        // Check Evolution (Cumulative)
        // Q1: 10 - 5
        $this->assertEquals(10, $stats['evolution'][0]['team_score']);
        $this->assertEquals(5, $stats['evolution'][0]['opponent_score']);

        // Q2: 15 - 15
        $this->assertEquals(15, $stats['evolution'][1]['team_score']);
        $this->assertEquals(15, $stats['evolution'][1]['opponent_score']);
    }
}
