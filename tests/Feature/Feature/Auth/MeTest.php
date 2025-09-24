<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class MeTest extends TestCase
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
    public function authenticated_user_can_access_their_own_profile(): void
    {
        $user = User::factory()->for(UserType::where('name', UserType::PLAYER)->first(), 'userType')->create();
        Sanctum::actingAs($user);

        $response = $this->getJson('/api/me');

        $response->assertStatus(200);
        $response->assertJson(['id' => $user->id, 'email' => $user->email]);
    }

    /** @test */
    public function unauthenticated_user_cannot_access_their_own_profile(): void
    {
        $response = $this->getJson('/api/me');

        $response->assertStatus(401);
    }
}
