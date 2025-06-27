<?php

namespace Database\Factories;

use App\Models\Encounter;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Encounter>
 */
class EncounterFactory extends Factory
{
    protected $model = Encounter::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'season_id' => Season::factory(),
            'team_id' => Team::factory(),
            'opponent' => $this->faker->company(),
            'is_at_home' => $this->faker->boolean(),
            'happens_at' => $this->faker->dateTimeBetween('-1 month', '+3 months'),
            'is_victory' => $this->faker->optional()->boolean(),
        ];
    }
}
