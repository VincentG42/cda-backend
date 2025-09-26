<?php

namespace Tests\Feature;

use App\Models\User;
use App\Notifications\ResetPasswordNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Password;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_password_reset_link_can_be_requested(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/forgot-password', ['email' => 'test@example.com']);

        $response->assertStatus(200)
            ->assertJson(['message' => __('passwords.sent')]);

        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'test@example.com']);
        Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    /** @test */
    public function a_password_reset_link_cannot_be_requested_for_non_existent_email(): void
    {
        $response = $this->postJson('/api/forgot-password', ['email' => 'nonexistent@example.com']);

        $response->assertStatus(422); // Laravel returns 422 for validation errors (email not found)
    }

    /** @test */
    public function a_password_can_be_reset_with_a_valid_token(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@example.com', 'password' => Hash::make('old-password')]);

        $token = Password::createToken($user);

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => __('passwords.reset')]);

        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));
        $this->assertDatabaseMissing('password_reset_tokens', ['email' => 'test@example.com']);
    }

    /** @test */
    public function a_password_cannot_be_reset_with_an_invalid_token(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'password' => Hash::make('old-password')]);

        $response = $this->postJson('/api/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(422); // Laravel returns 422 for invalid token
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    /** @test */
    public function a_password_cannot_be_reset_with_mismatched_passwords(): void
    {
        Notification::fake();

        $user = User::factory()->create(['email' => 'test@example.com', 'password' => Hash::make('old-password')]);

        // Request a password reset link to get a valid token
        $this->postJson('/api/forgot-password', ['email' => 'test@example.com']);
        $token = DB::table('password_reset_tokens')->where('email', 'test@example.com')->first()->token;

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'mismatched-password',
        ]);

        $response->assertStatus(422); // Validation error
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }
}
