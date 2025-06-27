<?php

namespace Database\Factories;

use App\Models\EncounterStat;
use App\Models\Encounter;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\EncounterStat>
 */
class EncounterStatFactory extends Factory
{
    protected $model = EncounterStat::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'encounter_id' => Encounter::factory(),
            'json' => json_encode([
                'score' => $this->faker->numberBetween(40, 120),
                'fouls' => $this->faker->numberBetween(0, 20),
            ]),
        ];
    }
}
