<?php

namespace Database\Factories;

use App\Models\DailyRecord;
use App\Models\Like;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Like>
 */
class LikeFactory extends Factory
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
            'daily_record_id' => DailyRecord::factory(),
            'created_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
