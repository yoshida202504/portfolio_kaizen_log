<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ImprovementRecord extends Model
{
    use HasFactory, SoftDeletes;

    public const STATUS_EXECUTED = 'executed';

    public const STATUS_NOT_EXECUTED = 'not_executed';

    public const EVALUATIONS = ['A', 'B', 'C'];

    public const NOT_EXECUTED_REASONS = ['forgot', 'no_time', 'unnecessary', 'other'];

    protected $fillable = [
        'execution_status',
        'result_evaluation',
        'executed_at',
        'actual_result',
        'not_executed_reason',
        'not_executed_note',
    ];

    protected function casts(): array
    {
        return [
            'executed_at' => 'date',
        ];
    }

    public function dailyRecord(): BelongsTo
    {
        return $this->belongsTo(DailyRecord::class);
    }

    public function getEvaluationAttribute(): string
    {
        return $this->execution_status === self::STATUS_NOT_EXECUTED
            ? 'D'
            : $this->result_evaluation;
    }
}
