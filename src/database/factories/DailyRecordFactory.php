<?php

namespace Database\Factories;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DailyRecord>
 */
class DailyRecordFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'record_date' => fake()->dateTimeBetween('-1 year', 'now')->format('Y-m-d'),
            'actions' => fake()->paragraph(),
            'good_points' => fake()->paragraph(),
            'improvement_points' => fake()->paragraph(),
            'improvement_strategy' => fake()->paragraph(),
            'improvement_result' => fake()->optional()->paragraph(),
            'improvement_rate' => fake()->optional()->randomElement([0, 20, 40, 60, 80, 100]),
            'is_public' => fake()->boolean(),
            'image_path' => null,
        ];
    }
}
