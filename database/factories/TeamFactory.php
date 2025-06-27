<?php

namespace Database\Factories;

use App\Models\Team;
use App\Models\Category;
use App\Models\User;
use App\Models\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    protected $model = Team::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'coach_id' => User::factory(),
            'season_id' => Season::factory(),
            'gender' => $this->faker->randomElement(['male', 'female', 'mixed']),
        ];
    }
}
