<?php

namespace App\Services;

use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;

class ImprovementActivityChart
{
    private const MAX_WEEK_LABELS = 8;

    /**
     * @param  Collection<int, object{executed_at: mixed}>  $records
     * @return Collection<int, array{label: string, count: int, show_label: bool}>
     */
    public function build(Collection $records, CarbonImmutable $periodStart, CarbonImmutable $periodEnd): Collection
    {
        $countsByWeek = $records
            ->filter(fn (object $record): bool => $record->executed_at !== null)
            ->groupBy(fn (object $record): string => CarbonImmutable::parse($record->executed_at)->startOfWeek()->toDateString())
            ->map->count();

        $weeks = collect();
        $weekStart = $periodStart->startOfWeek();
        $lastWeekStart = $periodEnd->startOfWeek();

        $weekCount = $weekStart->diffInWeeks($lastWeekStart) + 1;
        $labelInterval = max(1, (int) ceil($weekCount / self::MAX_WEEK_LABELS));
        $weekIndex = 0;

        while ($weekStart->lessThanOrEqualTo($lastWeekStart)) {
            $weeks->push([
                'label' => $weekStart->format('n/j'),
                'count' => $countsByWeek->get($weekStart->toDateString(), 0),
                'show_label' => $weekIndex === 0 || $weekStart->isSameDay($lastWeekStart) || $weekIndex % $labelInterval === 0,
            ]);

            $weekStart = $weekStart->addWeek();
            $weekIndex++;
        }

        return $weeks;
    }
}
