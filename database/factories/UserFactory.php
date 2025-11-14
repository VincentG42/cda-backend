<?php

namespace Database\Factories;

use App\Models\User;
use App\Models\UserType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'password' => bcrypt('password'),
            'user_type_id' => null, // Set to null by default
            'lastname' => $this->faker->lastName(),
            'firstname' => $this->faker->firstName(),
            'licence_number' => $this->faker->optional()->bothify('LIC-####'),
            'has_to_change_password' => $this->faker->boolean(),
        ];
    }

    /**
     * Configure the model factory.
     */
    public function configure(): static
    {
        return $this->afterMaking(function (User $user) {
            if (is_null($user->user_type_id)) {
                $user->user_type_id = UserType::factory()->create()->id;
            }
        })->afterCreating(function (User $user) {
            if (is_null($user->user_type_id)) {
                $user->user_type_id = UserType::factory()->create()->id;
                $user->save();
            }
        });
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }
}
