<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListUsersTest extends TestCase
{
    use RefreshDatabase;

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
        User::factory()->count(5)->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create();

        $response = $this->actingAs($adminUser)->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonCount(6);
    }

    /** @test */
    public function president_can_list_users(): void
    {
        $presidentUser = User::factory()->for(UserType::where('name', UserType::PRESIDENT)->first(), 'userType')->create();
        User::factory()->count(5)->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create();

        $response = $this->actingAs($presidentUser)->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonCount(6);
    }

    /** @test */
    public function staff_can_list_users(): void
    {
        $staffUser = User::factory()->for(UserType::where('name', UserType::STAFF)->first(), 'userType')->create();
        User::factory()->count(5)->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create();

        $response = $this->actingAs($staffUser)->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonCount(6);
    }

    /** @test */
    public function coach_can_list_users(): void
    {
        $coachUser = User::factory()->for(UserType::where('name', UserType::COACH)->first(), 'userType')->create();
        User::factory()->count(5)->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create();

        $response = $this->actingAs($coachUser)->getJson('/api/users');

        $response->assertStatus(200);
        $response->assertJsonCount(6);
    }
}
