<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Season;
use App\Models\Team;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TeamControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected function setUp(): void
    {
        parent::setUp();

        // Create user types
        $adminUserType = UserType::factory()->create(['name' => 'admin']);
        UserType::factory()->create(['name' => 'Player']);

        // Create and authenticate an admin user
        $this->adminUser = User::factory()->create(['user_type_id' => $adminUserType->id]);
        Sanctum::actingAs($this->adminUser);
    }

    public function test_can_list_teams(): void
    {
        Team::factory()->count(3)->create();

        $response = $this->getJson('/api/teams');

        $response->assertStatus(200)
            ->assertJsonCount(3);
    }

    public function test_can_create_team(): void
    {
        $category = Category::factory()->create();
        $coach = User::factory()->create();
        $season = Season::factory()->create();

        $teamData = [
            'name' => 'My Awesome Team',
            'category_id' => $category->id,
            'coach_id' => $coach->id,
            'season_id' => $season->id,
            'gender' => 'Male',
        ];

        $response = $this->postJson('/api/teams', $teamData);

        $response->assertStatus(201)
            ->assertJsonFragment(['name' => 'My Awesome Team']);

        $this->assertDatabaseHas('teams', ['name' => 'My Awesome Team']);
    }

    public function test_create_team_fails_with_invalid_data(): void
    {
        $response = $this->postJson('/api/teams', ['name' => '']); // Missing required fields

        $response->assertStatus(422) // Validation error
            ->assertJsonValidationErrors(['category_id', 'coach_id', 'season_id']);
    }

    public function test_can_show_team(): void
    {
        $team = Team::factory()->create();

        $response = $this->getJson("/api/teams/{$team->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $team->id]);
    }

    public function test_can_update_team(): void
    {
        $team = Team::factory()->create();
        $newName = 'Updated Team Name';

        $response = $this->putJson("/api/teams/{$team->id}", ['name' => $newName]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => $newName]);

        $this->assertDatabaseHas('teams', ['id' => $team->id, 'name' => $newName]);
    }

    public function test_can_delete_team(): void
    {
        $team = Team::factory()->create();

        $response = $this->deleteJson("/api/teams/{$team->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('teams', ['id' => $team->id]);
    }

    public function test_can_add_player_to_team(): void
    {
        $team = Team::factory()->create();
        $player = User::factory()->create();

        $response = $this->postJson("/api/teams/{$team->id}/players", ['user_id' => $player->id]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('team_user', [
            'team_id' => $team->id,
            'user_id' => $player->id,
        ]);
    }

    public function test_cannot_add_same_player_twice_to_team(): void
    {
        $team = Team::factory()->create();
        $player = User::factory()->create();
        $team->users()->attach($player);

        $response = $this->postJson("/api/teams/{$team->id}/players", ['user_id' => $player->id]);

        $response->assertStatus(409); // Conflict
    }

    public function test_can_remove_player_from_team(): void
    {
        $team = Team::factory()->create();
        $player = User::factory()->create();
        $team->users()->attach($player);

        $response = $this->deleteJson("/api/teams/{$team->id}/players/{$player->id}");

        $response->assertStatus(204);

        $this->assertDatabaseMissing('team_user', [
            'team_id' => $team->id,
            'user_id' => $player->id,
        ]);
    }
}
