<?php

namespace Tests\Unit;

use App\Services\ImprovementTrendChart;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ImprovementTrendChartTest extends TestCase
{
    public function test_it_builds_chart_points_and_limits_date_labels(): void
    {
        $records = Collection::times(8, fn (int $index) => (object) [
            'record_date' => sprintf('2026-09-%02d', $index),
            'improvement_rate' => $index * 10,
        ]);

        $chart = app(ImprovementTrendChart::class)->build($records);

        $this->assertSame(600, $chart['width']);
        $this->assertSame(260, $chart['height']);
        $this->assertCount(8, $chart['points']);
        $this->assertTrue($chart['points']->first()['show_date_label']);
        $this->assertTrue($chart['points']->last()['show_date_label']);
        $this->assertLessThanOrEqual(6, $chart['points']->where('show_date_label', true)->count());
    }

    public function test_it_centers_a_single_chart_point(): void
    {
        $chart = app(ImprovementTrendChart::class)->build(collect([
            (object) ['record_date' => '2026-09-20', 'improvement_rate' => 60],
        ]));

        $this->assertSame(300.0, $chart['points']->first()['x']);
        $this->assertSame(112.8, $chart['points']->first()['y']);
    }
}
