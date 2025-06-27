<?php

namespace Database\Factories;

use App\Models\IndividualStat;
use App\Models\Encounter;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\IndividualStat>
 */
class IndividualStatFactory extends Factory
{
    protected $model = IndividualStat::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'encounter_id' => Encounter::factory(),
            'user_id' => User::factory(),
            'json' => json_encode([
                'points' => $this->faker->numberBetween(0, 40),
                'rebounds' => $this->faker->numberBetween(0, 20),
                'assists' => $this->faker->numberBetween(0, 15),
            ]),
        ];
    }
}
