<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Event>
 */
class EventFactory extends Factory
{
    protected $model = Event::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $start = $this->faker->dateTimeBetween('-1 month', '+1 month');
        $end = (clone $start)->modify('+2 hours');

        return [
            'title' => $this->faker->sentence(3),
            'start_at' => $start,
            'close_at' => $end,
            'author_id' => User::factory(),
            'place' => $this->faker->city(),
            'additionnal_info' => $this->faker->optional()->sentence(),
            'address' => $this->faker->address(),
        ];
    }
}
