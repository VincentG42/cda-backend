<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\UserType;
use App\Repositories\UserRepository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserRepositoryTest extends TestCase
{
    use RefreshDatabase;

    protected UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();
        $this->userRepository = new UserRepository;

        // Create some user types
        UserType::factory()->create(['name' => UserType::ADMIN]);
        UserType::factory()->create(['name' => UserType::PLAYER]);
    }

    /** @test */
    public function it_can_retrieve_all_users_with_their_user_type(): void
    {
        // Create some users
        User::factory()->count(3)->create();

        $users = $this->userRepository->all();

        $this->assertCount(3, $users);
        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $users);
        $this->assertNotNull($users->first()->userType);
    }

    /** @test */
    public function it_can_find_a_user_by_id(): void
    {
        $user = User::factory()->create();

        $foundUser = $this->userRepository->find($user->id);

        $this->assertNotNull($foundUser);
        $this->assertEquals($user->id, $foundUser->id);
    }
}
