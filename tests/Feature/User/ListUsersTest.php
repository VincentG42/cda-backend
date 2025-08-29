<?php

namespace Tests\Feature\User;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ListUsersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function admin_user_can_list_users(): void
    {
        // Create user types
        $adminUserType = UserType::factory()->create(['name' => UserType::ADMIN]);
        UserType::factory()->create(['name' => UserType::PLAYER]);

        // Create an admin user
        $adminUser = User::factory()->for($adminUserType, 'userType')->create();

        // Create some other users
        User::factory()->count(5)->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create();

        // Act as the admin user
        $response = $this->actingAs($adminUser)->getJson('/api/users');

        // Assert
        $response->assertStatus(200);
        $response->assertJsonCount(6);
    }

    /** @test */
    public function non_admin_user_cannot_list_users(): void
    {
        // Create user types
        $playerUserType = UserType::factory()->create(['name' => UserType::PLAYER]);

        // Create a player user
        $playerUser = User::factory()->for($playerUserType, 'userType')->create();

        // Act as the player user
        $response = $this->actingAs($playerUser)->getJson('/api/users');

        // Assert
        $response->assertStatus(403);
    }
}
