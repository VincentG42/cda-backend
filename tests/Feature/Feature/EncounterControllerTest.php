<?php

namespace Tests\Feature\Feature;

use App\Models\Encounter;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EncounterControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $privilegedUser; // Coach, Staff, President

    protected User $nonPrivilegedUser; // Player

    protected function setUp(): void
    {
        parent::setUp();

        // Create all user types
        UserType::firstOrCreate(['name' => UserType::ADMIN]);
        UserType::firstOrCreate(['name' => UserType::PRESIDENT]);
        UserType::firstOrCreate(['name' => UserType::STAFF]);
        UserType::firstOrCreate(['name' => UserType::COACH]);
        UserType::firstOrCreate(['name' => UserType::PLAYER]);

        // Create and authenticate an admin user
        $this->adminUser = User::factory()->create(['user_type_id' => UserType::where('name', UserType::ADMIN)->first()->id]);
        Sanctum::actingAs($this->adminUser);

        // Create a privileged user (e.g., Coach) for authorization tests
        $this->privilegedUser = User::factory()->create(['user_type_id' => UserType::where('name', UserType::COACH)->first()->id]);

        // Create a non-privileged user (Player)
        $this->nonPrivilegedUser = User::factory()->create(['user_type_id' => UserType::where('name', UserType::PLAYER)->first()->id]);
    }

    /** @test */
    public function admin_can_list_upcoming_encounters(): void
    {
        // Create a team for encounters
        $team = Team::factory()->create();

        // Create past encounter
        Encounter::factory()->create(['team_id' => $team->id, 'happens_at' => now()->subDays(2)]);
        // Create upcoming encounters
        Encounter::factory()->count(2)->create(['team_id' => $team->id, 'happens_at' => now()->addDays(1)]);

        $response = $this->getJson('/api/encounters');

        $response->assertStatus(200);
        $response->assertJsonCount(2); // Only upcoming encounters
    }

    /** @test */
    public function admin_can_create_encounter(): void
    {
        $season = Season::factory()->create();
        $team = Team::factory()->create();

        $encounterData = [
            'season_id' => $season->id,
            'team_id' => $team->id,
            'opponent' => 'Opponent Team',
            'is_at_home' => true,
            'happens_at' => now()->addDays(7)->toDateTimeString(),
            'is_victory' => null,
        ];

        $response = $this->postJson('/api/encounters', $encounterData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('encounters', ['opponent' => 'Opponent Team']);
    }

    /** @test */
    public function admin_can_update_encounter_details(): void
    {
        $encounter = Encounter::factory()->create();
        $newOpponent = 'New Opponent';

        $response = $this->putJson('/api/encounters/'.$encounter->id, ['opponent' => $newOpponent]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('encounters', ['id' => $encounter->id, 'opponent' => $newOpponent]);
    }

    /** @test */
    public function admin_can_update_encounter_result(): void
    {
        $encounter = Encounter::factory()->create(['is_victory' => null]);

        $response = $this->putJson('/api/encounters/'.$encounter->id, ['is_victory' => true]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('encounters', ['id' => $encounter->id, 'is_victory' => true]);
    }

    /** @test */
    public function admin_can_delete_encounter(): void
    {
        $encounter = Encounter::factory()->create();

        $response = $this->deleteJson('/api/encounters/'.$encounter->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('encounters', ['id' => $encounter->id]);
    }

    /** @test */
    public function non_privileged_user_cannot_create_encounter(): void
    {
        Sanctum::actingAs($this->nonPrivilegedUser);
        $season = Season::factory()->create();
        $team = Team::factory()->create();

        $encounterData = [
            'season_id' => $season->id,
            'team_id' => $team->id,
            'opponent' => 'Opponent Team',
            'is_at_home' => true,
            'happens_at' => now()->addDays(7)->toDateTimeString(),
            'is_victory' => null,
        ];

        $response = $this->postJson('/api/encounters', $encounterData);

        $response->assertStatus(403);
    }

    /** @test */
    public function non_privileged_user_cannot_update_encounter(): void
    {
        Sanctum::actingAs($this->nonPrivilegedUser);
        $encounter = Encounter::factory()->create();

        $response = $this->putJson('/api/encounters/'.$encounter->id, ['opponent' => 'New Opponent']);

        $response->assertStatus(403);
    }

    /** @test */
    public function non_privileged_user_cannot_delete_encounter(): void
    {
        Sanctum::actingAs($this->nonPrivilegedUser);
        $encounter = Encounter::factory()->create();

        $response = $this->deleteJson('/api/encounters/'.$encounter->id);

        $response->assertStatus(403);
    }
}
