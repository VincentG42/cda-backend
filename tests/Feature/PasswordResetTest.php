<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordResetTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_password_reset_link_can_be_requested(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $response = $this->postJson('/api/forgot-password', ['email' => 'test@example.com']);

        $response->assertStatus(200)
            ->assertJson(['message' => 'We have emailed your password reset link.']);

        $this->assertDatabaseHas('password_reset_tokens', ['email' => 'test@example.com']);
    }

    /** @test */
    public function a_password_reset_link_cannot_be_requested_for_non_existent_email(): void
    {
        $response = $this->postJson('/api/forgot-password', ['email' => 'nonexistent@example.com']);

        $response->assertStatus(500); // Laravel returns 500 if email not found by default
    }

    /** @test */
    public function a_password_can_be_reset_with_a_valid_token(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'password' => Hash::make('old-password')]);

        // Manually create a password reset token
        $token = \Illuminate\Support\Str::random(60);
        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($token), // Token is hashed in the database
            'created_at' => now(),
        ]);

        $response = $this->postJson('/api/reset-password', [
            'token' => $token,
            'email' => 'test@example.com',
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Your password has been reset.']);

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

        $response->assertStatus(500); // Laravel returns 500 for invalid token
        $this->assertTrue(Hash::check('old-password', $user->fresh()->password));
    }

    /** @test */
    public function a_password_cannot_be_reset_with_mismatched_passwords(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com', 'password' => Hash::make('old-password')]);

        // Manually create a password reset token
        $token = \Illuminate\Support\Str::random(60);
        DB::table('password_reset_tokens')->insert([
            'email' => 'test@example.com',
            'token' => Hash::make($token),
            'created_at' => now(),
        ]);

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
