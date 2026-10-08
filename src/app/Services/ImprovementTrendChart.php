<?php

namespace App\Services;

use Illuminate\Support\Collection;

class ImprovementTrendChart
{
    private const WIDTH = 600;

    private const HEIGHT = 260;

    private const PADDING = 44;

    private const MAX_DATE_LABELS = 6;

    /**
     * @param  Collection<int, object{improvement_rate: mixed, record_date: mixed}>  $records
     * @return array{points: Collection<int, array{x: float, y: float, rate: int, record_date: mixed, show_date_label: bool}>, labelY: int, width: int, height: int, padding: int}
     */
    public function build(Collection $records): array
    {
        $pointCount = $records->count();
        $labelInterval = max(1, (int) ceil($pointCount / self::MAX_DATE_LABELS));
        $points = $records->values()->map(function (object $record, int $index) use ($pointCount, $labelInterval): array {
            $x = $pointCount === 1
                ? self::WIDTH / 2
                : self::PADDING + ($index * ((self::WIDTH - (self::PADDING * 2)) / ($pointCount - 1)));
            $y = self::PADDING + ((100 - (int) $record->improvement_rate) / 100 * (self::HEIGHT - (self::PADDING * 2)));

            return [
                'x' => round($x, 2),
                'y' => round($y, 2),
                'rate' => (int) $record->improvement_rate,
                'record_date' => $record->record_date,
                'show_date_label' => $index === 0 || $index === $pointCount - 1 || $index % $labelInterval === 0,
            ];
        });

        return [
            'points' => $points,
            'labelY' => self::HEIGHT - 12,
            'width' => self::WIDTH,
            'height' => self::HEIGHT,
            'padding' => self::PADDING,
        ];
    }
}
