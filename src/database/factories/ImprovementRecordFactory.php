<?php

namespace Database\Factories;

use App\Models\DailyRecord;
use App\Models\ImprovementRecord;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ImprovementRecord>
 */
class ImprovementRecordFactory extends Factory
{
    public function definition(): array
    {
        return [
            'daily_record_id' => DailyRecord::factory(),
            'execution_status' => ImprovementRecord::STATUS_EXECUTED,
            'result_evaluation' => fake()->randomElement(ImprovementRecord::EVALUATIONS),
            'executed_at' => fake()->date(),
            'actual_result' => fake()->paragraph(),
            'not_executed_reason' => null,
            'not_executed_note' => null,
        ];
    }

    public function notExecuted(string $reason = 'no_time'): static
    {
        return $this->state(fn (): array => [
            'execution_status' => ImprovementRecord::STATUS_NOT_EXECUTED,
            'result_evaluation' => null,
            'executed_at' => null,
            'actual_result' => null,
            'not_executed_reason' => $reason,
        ]);
    }
}
