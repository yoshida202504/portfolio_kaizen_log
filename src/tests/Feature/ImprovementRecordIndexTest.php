<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\ImprovementRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ImprovementRecordIndexTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 9, 27, 12, 0, 0, 'Asia/Tokyo'));
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_guest_is_redirected_to_login_for_improvement_records(): void
    {
        $this->get(route('improvement-records.index'))
            ->assertRedirect(route('login'));
    }

    public function test_improvement_records_page_shows_weekly_activity_and_a_to_d_breakdown(): void
    {
        $user = User::factory()->create();
        $recordA = $this->createDailyRecord($user, '2026-09-01', 'A評価の改善');
        $recordB = $this->createDailyRecord($user, '2026-09-02', 'B評価の改善');
        $recordC = $this->createDailyRecord($user, '2026-09-03', 'C評価の改善');
        $recordD = $this->createDailyRecord($user, '2026-09-04', 'D評価の改善');

        ImprovementRecord::factory()->for($recordA, 'dailyRecord')->create([
            'result_evaluation' => 'A',
            'executed_at' => '2026-09-07',
        ]);
        ImprovementRecord::factory()->for($recordB, 'dailyRecord')->create([
            'result_evaluation' => 'B',
            'executed_at' => '2026-09-07',
        ]);
        ImprovementRecord::factory()->for($recordC, 'dailyRecord')->create([
            'result_evaluation' => 'C',
            'executed_at' => '2026-09-14',
        ]);
        ImprovementRecord::factory()->for($recordD, 'dailyRecord')->notExecuted()->create();

        $this->actingAs($user)
            ->get(route('improvement-records.index', ['year' => 2026, 'start_month' => 9, 'end_month' => 9]))
            ->assertOk()
            ->assertSee('改善記録')
            ->assertSee('週ごとの改善行動数')
            ->assertSee('評価 A')
            ->assertSee('評価 B')
            ->assertSee('評価 C')
            ->assertSee('評価 D')
            ->assertSee('A評価の改善')
            ->assertSee('D評価の改善')
            ->assertSee('>2<', false);
    }

    public function test_improvement_record_list_shows_strategy_and_expected_result_of_daily_record(): void
    {
        $user = User::factory()->create();
        $record = DailyRecord::factory()->for($user)->create([
            'record_date' => '2026-09-10',
            'actions' => '改善策表示の確認',
            'improvement_strategy' => '作業前に3行でゴールを書く',
            'expected_result' => '作業の手戻りが減る',
        ]);
        ImprovementRecord::factory()->for($record, 'dailyRecord')->create([
            'result_evaluation' => 'B',
            'executed_at' => '2026-09-11',
        ]);

        $this->actingAs($user)
            ->get(route('improvement-records.index', ['year' => 2026, 'start_month' => 9, 'end_month' => 9]))
            ->assertOk()
            ->assertSee('作業前に3行でゴールを書く')
            ->assertSee('作業の手戻りが減る');
    }

    public function test_evaluation_and_period_filters_only_return_matching_records_and_keep_query_string(): void
    {
        $user = User::factory()->create();
        $matchingRecord = $this->createDailyRecord($user, '2026-09-01', '想定以上の改善');
        $otherEvaluationRecord = $this->createDailyRecord($user, '2026-09-02', '想定どおりの改善');
        $outsidePeriodRecord = $this->createDailyRecord($user, '2026-10-01', '10月の改善');

        ImprovementRecord::factory()->for($matchingRecord, 'dailyRecord')->create([
            'result_evaluation' => 'A',
            'executed_at' => '2026-09-10',
        ]);
        ImprovementRecord::factory()->for($otherEvaluationRecord, 'dailyRecord')->create([
            'result_evaluation' => 'B',
            'executed_at' => '2026-09-10',
        ]);
        ImprovementRecord::factory()->for($outsidePeriodRecord, 'dailyRecord')->create([
            'result_evaluation' => 'A',
            'executed_at' => '2026-10-10',
        ]);

        $this->actingAs($user)
            ->get(route('improvement-records.index', [
                'year' => 2026,
                'start_month' => 9,
                'end_month' => 9,
                'evaluation' => 'above',
            ]))
            ->assertOk()
            ->assertSee('想定以上の改善')
            ->assertDontSee('想定どおりの改善')
            ->assertDontSee('10月の改善');
    }

    public function test_page_displays_legacy_improvement_rate_chart_in_chronological_order(): void
    {
        $user = User::factory()->create();
        $firstRecord = $this->createDailyRecord($user, '2026-09-01', '最初の改善率記録');
        $secondRecord = $this->createDailyRecord($user, '2026-09-02', '次の改善率記録');
        $firstRecord->update(['improvement_rate' => 20]);
        $secondRecord->update(['improvement_rate' => 80]);

        $this->actingAs($user)
            ->get(route('improvement-records.index'))
            ->assertOk()
            ->assertSee('参考：従来の改善率の推移')
            ->assertSee('aria-label="改善率の推移グラフ"', false)
            ->assertSeeInOrder([
                $firstRecord->record_date.'：20%',
                $secondRecord->record_date.'：80%',
            ]);
    }

    public function test_page_excludes_other_users_and_soft_deleted_improvement_records(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $visibleRecord = $this->createDailyRecord($user, '2026-09-01', '表示される改善');
        $otherUserRecord = $this->createDailyRecord($otherUser, '2026-09-01', '他人の改善');
        $deletedImprovementRecord = $this->createDailyRecord($user, '2026-09-01', '削除済み改善');
        $deletedDailyRecord = $this->createDailyRecord($user, '2026-09-01', '削除済み日報の改善');

        ImprovementRecord::factory()->for($visibleRecord, 'dailyRecord')->create(['executed_at' => '2026-09-10']);
        ImprovementRecord::factory()->for($otherUserRecord, 'dailyRecord')->create(['executed_at' => '2026-09-10']);
        $deleted = ImprovementRecord::factory()->for($deletedImprovementRecord, 'dailyRecord')->create(['executed_at' => '2026-09-10']);
        ImprovementRecord::factory()->for($deletedDailyRecord, 'dailyRecord')->create(['executed_at' => '2026-09-10']);
        $deleted->delete();
        $deletedDailyRecord->delete();

        $this->actingAs($user)
            ->get(route('improvement-records.index', ['year' => 2026, 'start_month' => 9, 'end_month' => 9]))
            ->assertOk()
            ->assertSee('表示される改善')
            ->assertDontSee('他人の改善')
            ->assertDontSee('削除済み改善')
            ->assertDontSee('削除済み日報の改善');
    }

    public function test_improvement_record_list_is_paginated_and_preserves_filters(): void
    {
        $user = User::factory()->create();

        foreach (range(1, 21) as $number) {
            $dailyRecord = $this->createDailyRecord($user, '2026-09-01', "改善記録{$number}");
            ImprovementRecord::factory()->for($dailyRecord, 'dailyRecord')->create([
                'result_evaluation' => 'A',
                'executed_at' => '2026-09-'.str_pad((string) (($number - 1) % 20 + 1), 2, '0', STR_PAD_LEFT),
            ]);
        }

        $parameters = ['year' => 2026, 'start_month' => 9, 'end_month' => 9, 'evaluation' => 'above'];

        $firstPage = $this->actingAs($user)
            ->get(route('improvement-records.index', $parameters))
            ->assertOk()
            ->assertSee('evaluation=above', false);
        $this->assertCount(20, $firstPage->viewData('records'));

        $secondPage = $this->actingAs($user)
            ->get(route('improvement-records.index', array_merge($parameters, ['page' => 2])))
            ->assertOk();
        $this->assertCount(1, $secondPage->viewData('records'));
    }

    private function createDailyRecord(User $user, string $recordDate, string $actions): DailyRecord
    {
        return DailyRecord::factory()->for($user)->create([
            'record_date' => $recordDate,
            'actions' => $actions,
            'expected_result' => '期待した成果です。',
        ]);
    }
}
