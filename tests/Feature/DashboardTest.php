<?php

namespace Tests\Feature;

use App\Models\Encounter;
use App\Models\Event;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected Team $userTeam;

    protected Season $season;

    protected function setUp(): void
    {
        parent::setUp();

        UserType::firstOrCreate(['name' => UserType::PLAYER]);
        UserType::firstOrCreate(['name' => UserType::ADMIN]);

        $this->season = Season::factory()->create();
        $this->userTeam = Team::factory()->create(['season_id' => $this->season->id]);
        $this->user = User::factory()->create(['user_type_id' => UserType::where('name', UserType::PLAYER)->first()->id]);
        $this->user->teams()->attach($this->userTeam); // Attach user to the team

        Sanctum::actingAs($this->user);
    }

    /** @test */
    public function authenticated_user_can_access_their_dashboard(): void
    {
        $response = $this->getJson('/api/me/dashboard');
        $response->assertStatus(200);
    }

    /** @test */
    public function dashboard_returns_upcoming_encounters_for_users_team(): void
    {
        // Create past encounter for user's team
        Encounter::factory()->create([
            'team_id' => $this->userTeam->id,
            'season_id' => $this->season->id,
            'happens_at' => now()->subDays(2),
        ]);
        // Create upcoming encounter for user's team
        $upcomingEncounter = Encounter::factory()->create([
            'team_id' => $this->userTeam->id,
            'season_id' => $this->season->id,
            'happens_at' => now()->addDays(2),
        ]);
        // Create upcoming encounter for another team
        Encounter::factory()->create([
            'team_id' => Team::factory()->create()->id,
            'season_id' => $this->season->id,
            'happens_at' => now()->addDays(3),
        ]);

        $response = $this->getJson('/api/me/dashboard');
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $upcomingEncounter->id]);
        $data = $response->json();
        $filteredData = collect($data['upcomingMatches'])->filter(function ($item) use ($upcomingEncounter) {
            return $item['id'] === $upcomingEncounter->id && isset($item['happens_at']);
        });
        $this->assertCount(1, $filteredData);
    }

    /** @test */
    public function dashboard_returns_general_upcoming_events(): void
    {
        // Create past event
        Event::factory()->create([
            'author_id' => User::factory()->create()->id,
            'start_at' => now()->subDays(2),
            'close_at' => now()->subDays(1),
        ]);
        // Create upcoming event
        $upcomingEvent = Event::factory()->create([
            'author_id' => User::factory()->create()->id,
            'start_at' => now()->addDays(1),
            'close_at' => now()->addDays(2),
        ]);

        $response = $this->getJson('/api/me/dashboard');
        $response->assertStatus(200);
        $response->assertJsonFragment(['id' => $upcomingEvent->id]);
        $data = $response->json();
        $filteredData = collect($data['recentEvents'])->filter(function ($item) use ($upcomingEvent) {
            return $item['id'] === $upcomingEvent->id && isset($item['start_at']);
        });
        $this->assertCount(1, $filteredData);
    }

    /** @test */
    public function dashboard_activities_are_sorted_by_date(): void
    {
        // Create upcoming encounter for user's team
        $encounter = Encounter::factory()->create([
            'team_id' => $this->userTeam->id,
            'season_id' => $this->season->id,
            'happens_at' => now()->addDays(3),
        ]);
        // Create upcoming event
        $event = Event::factory()->create([
            'author_id' => User::factory()->create()->id,
            'start_at' => now()->addDays(1),
            'close_at' => now()->addDays(2),
        ]);

        $response = $this->getJson('/api/me/dashboard');
        $response->assertStatus(200);

        $data = $response->json();

        $combinedActivities = collect($data['upcomingMatches'])
            ->concat($data['recentEvents'])
            ->sortBy(function ($item) {
                return $item['happens_at'] ?? $item['start_at'];
            })->values(); // Re-index the collection

        $this->assertCount(2, $combinedActivities);
        $this->assertEquals($event->id, $combinedActivities[0]['id']); // Event is earlier
        $this->assertEquals($encounter->id, $combinedActivities[1]['id']); // Encounter is later
    }
}
