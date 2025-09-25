<?php

namespace Tests\Feature\Feature\Auth;

use App\Models\Team;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MyTeamsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Create all user types once for all tests
        UserType::firstOrCreate(['name' => UserType::ADMIN]);
        UserType::firstOrCreate(['name' => UserType::PLAYER]);
    }

    /** @test */
    public function authenticated_user_can_access_their_teams_composition(): void
    {
        $user = User::factory()->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create();
        $team1 = Team::factory()->create();
        $team2 = Team::factory()->create();

        $user->teams()->attach($team1);
        $user->teams()->attach($team2);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me/teams');

        $response->assertStatus(200);
        $response->assertJsonCount(2); // User is part of 2 teams
        $response->assertJsonFragment(['id' => $team1->id]);
        $response->assertJsonFragment(['id' => $team2->id]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_their_teams_composition(): void
    {
        $response = $this->getJson('/api/me/teams');

        $response->assertStatus(401);
    }
}
