<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ImprovementReminderTest extends TestCase
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

    public function test_home_and_navigation_show_records_that_need_a_reflection_from_day_six(): void
    {
        $user = User::factory()->create();
        $dueRecord = $this->createRecord($user, '2026-09-22 09:00:00', '振り返りが必要な改善策です。');
        $tooEarlyRecord = $this->createRecord($user, '2026-09-23 09:00:00', 'まだ通知しない改善策です。');

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('改善結果の未入力')
            ->assertSee('未振り返り')
            ->assertSee($dueRecord->actions)
            ->assertSee(route('records.improvement.edit', $dueRecord), false);

        $this->assertSame(1, $user->pendingImprovementRecords()->count());
        $this->assertTrue($tooEarlyRecord->fresh()->isImprovementReflectionDue() === false);
    }

    public function test_completed_and_expired_records_are_not_shown_as_pending_reflections(): void
    {
        $user = User::factory()->create();
        $completedRecord = $this->createRecord($user, '2026-09-22 09:00:00', '完了済みの改善策です。');
        $expiredRecord = $this->createRecord($user, '2026-09-20 09:00:00', '期限切れの改善策です。');

        $completedRecord->improvementRecord()->create([
            'execution_status' => 'executed',
            'result_evaluation' => 'B',
            'executed_at' => '2026-09-26',
            'actual_result' => '完了しました。',
        ]);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertDontSee('改善結果の未入力');

        $this->assertSame(0, $user->pendingImprovementRecords()->count());
        $this->assertTrue($completedRecord->fresh()->isImprovementReflectionDue() === false);
        $this->assertTrue($expiredRecord->fresh()->isImprovementReflectionDue() === false);
    }

    private function createRecord(User $user, string $createdAt, string $actions): DailyRecord
    {
        return DailyRecord::factory()->for($user)->create([
            'record_date' => '2026-09-20',
            'actions' => $actions,
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ]);
    }
}
