<?php

namespace App\Http\Controllers;

use App\Http\Requests\ImprovementRecordIndexRequest;
use App\Models\ImprovementRecord;
use App\Services\ImprovementActivityChart;
use App\Services\ImprovementTrendChart;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\View\View;

class ImprovementRecordController extends Controller
{
    public function __construct(
        private readonly ImprovementActivityChart $improvementActivityChart,
        private readonly ImprovementTrendChart $improvementTrendChart,
    ) {}

    public function index(ImprovementRecordIndexRequest $request): View
    {
        $year = (int) $request->input('year', now(config('app.timezone'))->year);
        $startMonth = (int) $request->input('start_month', 1);
        $endMonth = (int) $request->input('end_month', 12);
        $evaluation = $request->input('evaluation', 'all');

        $periodStart = CarbonImmutable::create($year, $startMonth, 1, 0, 0, 0, config('app.timezone'));
        $periodEnd = CarbonImmutable::create($year, $endMonth, 1, 0, 0, 0, config('app.timezone'))->endOfMonth();

        $records = $this->recordsForPeriod($request->user()->id, $periodStart, $periodEnd);
        $filteredRecords = $this->filterByEvaluation($records, $evaluation);

        $breakdown = [
            'A' => (clone $records)->where('result_evaluation', 'A')->count(),
            'B' => (clone $records)->where('result_evaluation', 'B')->count(),
            'C' => (clone $records)->where('result_evaluation', 'C')->count(),
            'D' => (clone $records)->where('execution_status', ImprovementRecord::STATUS_NOT_EXECUTED)->count(),
        ];

        $executedRecords = (clone $filteredRecords)
            ->where('execution_status', ImprovementRecord::STATUS_EXECUTED)
            ->whereIn('result_evaluation', ImprovementRecord::EVALUATIONS)
            ->get(['id', 'executed_at']);
        $legacyImprovementRecords = $request->user()->dailyRecords()
            ->whereNotNull('improvement_rate')
            ->orderBy('record_date')
            ->orderBy('created_at')
            ->get();
        $legacyChart = $this->improvementTrendChart->build($legacyImprovementRecords);

        return view('improvement-records.index', [
            'records' => $filteredRecords
                ->with('dailyRecord:id,record_date,actions,improvement_strategy,expected_result')
                ->orderByDesc('executed_at')
                ->orderByDesc('created_at')
                ->paginate(20)
                ->withQueryString(),
            'breakdown' => $breakdown,
            'weeklyActivity' => $this->improvementActivityChart->build($executedRecords, $periodStart, $periodEnd),
            'year' => $year,
            'startMonth' => $startMonth,
            'endMonth' => $endMonth,
            'evaluation' => $evaluation,
            'legacyImprovementRecords' => $legacyImprovementRecords,
            'legacyChartPoints' => $legacyChart['points'],
            'legacyChartLabelY' => $legacyChart['labelY'],
            'legacyChartWidth' => $legacyChart['width'],
            'legacyChartHeight' => $legacyChart['height'],
            'legacyChartPadding' => $legacyChart['padding'],
        ]);
    }

    private function recordsForPeriod(int $userId, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): Builder
    {
        return ImprovementRecord::query()
            ->whereHas('dailyRecord', function (Builder $query) use ($userId): void {
                $query->where('user_id', $userId);
            })
            ->where(function (Builder $query) use ($periodStart, $periodEnd): void {
                $query->where(function (Builder $executed) use ($periodStart, $periodEnd): void {
                    $executed->where('execution_status', ImprovementRecord::STATUS_EXECUTED)
                        ->whereBetween('executed_at', [$periodStart->toDateString(), $periodEnd->toDateString()]);
                })->orWhere(function (Builder $notExecuted) use ($periodStart, $periodEnd): void {
                    $notExecuted->where('execution_status', ImprovementRecord::STATUS_NOT_EXECUTED)
                        ->whereHas('dailyRecord', function (Builder $dailyRecords) use ($periodStart, $periodEnd): void {
                            $dailyRecords->whereBetween('record_date', [$periodStart->toDateString(), $periodEnd->toDateString()]);
                        });
                });
            });
    }

    private function filterByEvaluation(Builder $records, string $evaluation): Builder
    {
        return match ($evaluation) {
            'executed' => $records->where('execution_status', ImprovementRecord::STATUS_EXECUTED),
            'above_or_expected' => $records->whereIn('result_evaluation', ['A', 'B']),
            'above' => $records->where('result_evaluation', 'A'),
            'below' => $records->where('result_evaluation', 'C'),
            'not_executed' => $records->where('execution_status', ImprovementRecord::STATUS_NOT_EXECUTED),
            default => $records,
        };
    }
}
