<?php

namespace Tests\Feature;

use App\Models\Encounter;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EncounterFilteringTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected Team $team1;

    protected Team $team2;

    protected Season $season;

    protected function setUp(): void
    {
        parent::setUp();

        UserType::firstOrCreate(['name' => UserType::ADMIN]);
        $this->adminUser = User::factory()->create(['user_type_id' => UserType::where('name', UserType::ADMIN)->first()->id]);
        Sanctum::actingAs($this->adminUser);

        $this->season = Season::factory()->create();
        $this->team1 = Team::factory()->create();
        $this->team2 = Team::factory()->create();

        // Create encounters for team1
        Encounter::factory()->create([
            'team_id' => $this->team1->id,
            'season_id' => $this->season->id,
            'happens_at' => now()->subDays(5), // Past
        ]);
        Encounter::factory()->create([
            'team_id' => $this->team1->id,
            'season_id' => $this->season->id,
            'happens_at' => now()->addDays(5), // Upcoming
        ]);

        // Create encounters for team2
        Encounter::factory()->create([
            'team_id' => $this->team2->id,
            'season_id' => $this->season->id,
            'happens_at' => now()->subDays(10), // Past
        ]);
        Encounter::factory()->create([
            'team_id' => $this->team2->id,
            'season_id' => $this->season->id,
            'happens_at' => now()->addDays(10), // Upcoming
        ]);
    }

    /** @test */
    public function it_can_filter_encounters_by_team_id(): void
    {
        $response = $this->getJson("/api/encounters?team_id={$this->team1->id}&filter=all");
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['team' => ['id' => $this->team1->id]]);
        $response->assertJsonMissing(['team' => ['id' => $this->team2->id]]);
    }

    /** @test */
    public function it_can_filter_encounters_by_past_date(): void
    {
        $response = $this->getJson('/api/encounters?filter=past');
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data'); // 2 past encounters
        $response->assertJsonMissing(
            ['happens_at' => Encounter::where('happens_at', '>=', now())->first()->happens_at->toDateTimeString()]
        );
    }

    /** @test */
    public function it_can_filter_encounters_by_upcoming_date(): void
    {
        $response = $this->getJson('/api/encounters?filter=upcoming');
        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data'); // 2 upcoming encounters
        $response->assertJsonMissing(
            ['happens_at' => Encounter::where('happens_at', '<', now())->first()->happens_at->toDateTimeString()]
        );
    }

    /** @test */
    public function it_can_filter_encounters_by_all_dates(): void
    {
        $response = $this->getJson('/api/encounters?filter=all');
        $response->assertStatus(200);
        $response->assertJsonCount(4, 'data'); // 2 past + 2 upcoming
    }

    /** @test */
    public function it_can_filter_encounters_by_team_id_and_past_date(): void
    {
        $response = $this->getJson("/api/encounters?team_id={$this->team1->id}&filter=past");
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['team' => ['id' => $this->team1->id]]);
        $response->assertJsonMissing(['team' => ['id' => $this->team2->id]]);
        $response->assertJsonMissing(
            ['happens_at' => Encounter::where('team_id', $this->team1->id)->where('happens_at', '>=', now())->first()->happens_at->toDateTimeString()]
        );
    }

    /** @test */
    public function it_can_filter_encounters_by_team_id_and_upcoming_date(): void
    {
        $response = $this->getJson("/api/encounters?team_id={$this->team2->id}&filter=upcoming");
        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['team' => ['id' => $this->team2->id]]);
        $response->assertJsonMissing(['team' => ['id' => $this->team1->id]]);
        $response->assertJsonMissing(
            ['happens_at' => Encounter::where('team_id', $this->team2->id)->where('happens_at', '<', now())->first()->happens_at->toDateTimeString()]
        );
    }
}
