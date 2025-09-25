<?php

namespace Tests\Feature\Feature;

use App\Models\Event;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class EventControllerTest extends TestCase
{
    use RefreshDatabase;

    protected User $adminUser;

    protected User $privilegedUser; // Staff, President

    protected User $nonPrivilegedUser; // Player, Coach

    protected User $eventAuthor; // User to be used as author for events

    protected function setUp(): void
    {
        parent::setUp();

        // Create user types
        UserType::firstOrCreate(['name' => UserType::ADMIN]);
        UserType::firstOrCreate(['name' => UserType::PRESIDENT]);
        UserType::firstOrCreate(['name' => UserType::STAFF]);
        UserType::firstOrCreate(['name' => UserType::COACH]);
        UserType::firstOrCreate(['name' => UserType::PLAYER]);

        // Create and authenticate an admin user
        $this->adminUser = User::factory()->create(['user_type_id' => UserType::where('name', UserType::ADMIN)->first()->id]);
        Sanctum::actingAs($this->adminUser);

        // Create a privileged user (e.g., Staff) for authorization tests
        $this->privilegedUser = User::factory()->create(['user_type_id' => UserType::where('name', UserType::STAFF)->first()->id]);

        // Create a non-privileged user (Player)
        $this->nonPrivilegedUser = User::factory()->create(['user_type_id' => UserType::where('name', UserType::PLAYER)->first()->id]);

        // Create a user to be used as author for events
        $this->eventAuthor = User::factory()->create();
    }

    /** @test */
    public function admin_can_list_upcoming_events(): void
    {
        // Create past event
        Event::factory()->create(['author_id' => $this->eventAuthor->id, 'start_at' => now()->subDays(2), 'close_at' => now()->subDays(1)]);
        // Create upcoming events
        Event::factory()->count(2)->create(['author_id' => $this->eventAuthor->id, 'start_at' => now()->addDays(1), 'close_at' => now()->addDays(2)]);

        $response = $this->getJson('/api/events');

        $response->assertStatus(200);
        $response->assertJsonCount(2); // Only upcoming events
    }

    /** @test */
    public function admin_can_create_event(): void
    {
        $eventData = [
            'title' => 'New Event',
            'start_at' => now()->addDays(5)->toDateTimeString(),
            'close_at' => now()->addDays(6)->toDateTimeString(),
            'place' => 'Event Place',
            'address' => 'Event Address',
            'additionnal_info' => 'Some info',
            'author_id' => $this->eventAuthor->id,
        ];

        $response = $this->postJson('/api/events', $eventData);

        $response->assertStatus(201);
        $this->assertDatabaseHas('events', ['title' => 'New Event']);
    }

    /** @test */
    public function admin_can_update_event(): void
    {
        $event = Event::factory()->create(['author_id' => $this->eventAuthor->id]);
        $newTitle = 'Updated Event Title';

        $response = $this->putJson('/api/events/'.$event->id, ['title' => $newTitle]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('events', ['id' => $event->id, 'title' => $newTitle]);
    }

    /** @test */
    public function admin_can_delete_event(): void
    {
        $event = Event::factory()->create(['author_id' => $this->eventAuthor->id]);

        $response = $this->deleteJson('/api/events/'.$event->id);

        $response->assertStatus(204);
        $this->assertDatabaseMissing('events', ['id' => $event->id]);
    }

    /** @test */
    public function non_privileged_user_cannot_create_event(): void
    {
        Sanctum::actingAs($this->nonPrivilegedUser);
        $eventData = [
            'title' => 'New Event',
            'start_at' => now()->addDays(5)->toDateTimeString(),
            'close_at' => now()->addDays(6)->toDateTimeString(),
            'place' => 'Event Place',
            'address' => 'Event Address',
            'additionnal_info' => 'Some info',
            'author_id' => $this->eventAuthor->id,
        ];

        $response = $this->postJson('/api/events', $eventData);

        $response->assertStatus(403);
    }

    /** @test */
    public function non_privileged_user_cannot_update_event(): void
    {
        Sanctum::actingAs($this->nonPrivilegedUser);
        $event = Event::factory()->create(['author_id' => $this->eventAuthor->id]);

        $response = $this->putJson('/api/events/'.$event->id, ['title' => 'Updated Title']);

        $response->assertStatus(403);
    }

    /** @test */
    public function non_privileged_user_cannot_delete_event(): void
    {
        Sanctum::actingAs($this->nonPrivilegedUser);
        $event = Event::factory()->create(['author_id' => $this->eventAuthor->id]);

        $response = $this->deleteJson('/api/events/'.$event->id);

        $response->assertStatus(403);
    }
}
