<?php

namespace Tests\Feature\User;

use App\Models\Team;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Testing\Fluent\AssertableJson; // Import AssertableJson

class ListUsersTest extends TestCase
{
    use RefreshDatabase, WithFaker;

    protected function setUp(): void
    {
        parent::setUp();
        // Create all user types once for all tests
        UserType::firstOrCreate(['name' => UserType::ADMIN]);
        UserType::firstOrCreate(['name' => UserType::PRESIDENT]);
        UserType::firstOrCreate(['name' => UserType::STAFF]);
        UserType::firstOrCreate(['name' => UserType::COACH]);
        UserType::firstOrCreate(['name' => UserType::PLAYER]);
    }

    /** @test */
    public function unauthenticated_user_cannot_list_users(): void
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(401);
    }

    /** @test */
    public function player_cannot_list_users(): void
    {
        $playerUser = User::factory()->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create();
        $response = $this->actingAs($playerUser)->getJson('/api/users');
        $response->assertStatus(403);
    }

    /** @test */
    public function admin_user_can_list_users(): void
    {
        $adminUser = User::factory()->for(UserType::where('name', UserType::ADMIN)->first(), 'userType')->create();
        User::factory()->count(5)->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create(['firstname' => $this->faker->firstName, 'lastname' => $this->faker->lastName]);

        $response = $this->actingAs($adminUser)->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonCount(6, 'data');
    }

    /** @test */
    public function president_can_list_users(): void
    {
        $presidentUser = User::factory()->for(UserType::where('name', UserType::PRESIDENT)->first(), 'userType')->create();
        User::factory()->count(5)->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create(['firstname' => $this->faker->firstName, 'lastname' => $this->faker->lastName]);

        $response = $this->actingAs($presidentUser)->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonCount(6, 'data');
    }

    /** @test */
    public function staff_can_list_users(): void
    {
        $staffUser = User::factory()->for(UserType::where('name', UserType::STAFF)->first(), 'userType')->create();
        User::factory()->count(5)->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create(['firstname' => $this->faker->firstName, 'lastname' => $this->faker->lastName]);

        $response = $this->actingAs($staffUser)->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonCount(6, 'data');
    }

    /** @test */
    public function coach_can_list_users(): void
    {
        $coachUser = User::factory()->for(UserType::where('name', UserType::COACH)->first(), 'userType')->create();
        User::factory()->count(5)->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create(['firstname' => $this->faker->firstName, 'lastname' => $this->faker->lastName]);

        $response = $this->actingAs($coachUser)->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonCount(6, 'data');
    }

    /** @test */
    public function admin_can_filter_users_by_name(): void
    {
        $adminUser = User::factory()->for(UserType::where('name', UserType::ADMIN)->first(), 'userType')->create(['firstname' => 'Admin', 'lastname' => 'User']);
        User::factory()->create(['firstname' => 'John', 'lastname' => 'Doe', 'user_type_id' => UserType::where('name', UserType::PLAYER)->first()->id]);
        User::factory()->create(['firstname' => 'Jane', 'lastname' => 'Smith', 'user_type_id' => UserType::where('name', UserType::PLAYER)->first()->id]);
        User::factory()->create(['firstname' => 'Peter', 'lastname' => 'Jones', 'user_type_id' => UserType::where('name', UserType::COACH)->first()->id]);

        $response = $this->actingAs($adminUser)->getJson('/api/users?name=John');

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['firstname' => 'John', 'lastname' => 'Doe']);
    }

    /** @test */
    public function admin_can_filter_users_by_user_type(): void
    {
        $adminUser = User::factory()->for(UserType::where('name', UserType::ADMIN)->first(), 'userType')->create();
        $playerUserType = UserType::where('name', UserType::PLAYER)->first();
        $coachUserType = UserType::where('name', UserType::COACH)->first();

        User::factory()->count(3)->for($playerUserType, 'userType')->create(['firstname' => $this->faker->firstName, 'lastname' => $this->faker->lastName]);
        User::factory()->count(2)->for($coachUserType, 'userType')->create(['firstname' => $this->faker->firstName, 'lastname' => $this->faker->lastName]);

        $response = $this->actingAs($adminUser)->getJson('/api/users?user_type_id='.$playerUserType->id);

        $response->assertStatus(200);
        $response->assertJson(fn (AssertableJson $json) =>
            $json->has('data', 3)
                 ->has('data.0', fn (AssertableJson $json) =>
                    $json->where('user_type.id', $playerUserType->id)
                         ->etc()
                 )
                 ->has('data.1', fn (AssertableJson $json) =>
                    $json->where('user_type.id', $playerUserType->id)
                         ->etc()
                 )
                 ->has('data.2', fn (AssertableJson $json) =>
                    $json->where('user_type.id', $playerUserType->id)
                         ->etc()
                 )
                 ->etc()
        );
    }

    /** @test */
    public function admin_can_filter_users_by_team(): void
    {
        $adminUser = User::factory()->for(UserType::where('name', UserType::ADMIN)->first(), 'userType')->create();
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();

        $user1 = User::factory()->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create(['firstname' => $this->faker->firstName, 'lastname' => $this->faker->lastName]);
        $user2 = User::factory()->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create(['firstname' => $this->faker->firstName, 'lastname' => $this->faker->lastName]);
        $user3 = User::factory()->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create(['firstname' => $this->faker->firstName, 'lastname' => $this->faker->lastName]);

        $user1->teams()->attach($teamA->id);
        $user2->teams()->attach($teamA->id);
        $user3->teams()->attach($teamB->id);

        $response = $this->actingAs($adminUser)->getJson('/api/users?team_id='.$teamA->id);

        $response->assertStatus(200);
        $response->assertJsonCount(2, 'data');
        $response->assertJsonFragment(['id' => $user1->id]);
        $response->assertJsonFragment(['id' => $user2->id]);
    }

    /** @test */
    public function admin_can_filter_users_by_multiple_criteria(): void
    {
        $adminUser = User::factory()->for(UserType::where('name', UserType::ADMIN)->first(), 'userType')->create();
        $playerUserType = UserType::where('name', UserType::PLAYER)->first();
        $coachUserType = UserType::where('name', UserType::COACH)->first();
        $teamA = Team::factory()->create();
        $teamB = Team::factory()->create();

        $user1 = User::factory()->for($playerUserType, 'userType')->create(['firstname' => 'Player', 'lastname' => 'One']);
        $user2 = User::factory()->for($playerUserType, 'userType')->create(['firstname' => 'Player', 'lastname' => 'Two']);
        $user3 = User::factory()->for($coachUserType, 'userType')->create(['firstname' => 'Coach', 'lastname' => 'One']);

        $user1->teams()->attach($teamA->id);
        $user2->teams()->attach($teamB->id);
        $user3->teams()->attach($teamA->id);

        $response = $this->actingAs($adminUser)->getJson(
            '/api/users?name=Player&user_type_id='.$playerUserType->id.'&team_id='.$teamA->id
        );

        $response->assertStatus(200);
        $response->assertJsonCount(1, 'data');
        $response->assertJsonFragment(['id' => $user1->id]);
    }
}