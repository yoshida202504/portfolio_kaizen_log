<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class ImprovementRecordTest extends TestCase
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

    public function test_daily_record_requires_an_expected_result_when_created(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('records.store'), $this->dailyRecordPayload(['expected_result' => '']))
            ->assertSessionHasErrors('expected_result');
    }

    public function test_owner_can_store_an_executed_improvement_record_with_an_a_to_c_evaluation(): void
    {
        $owner = User::factory()->create();
        $record = $this->createRecord($owner);

        $this->actingAs($owner)
            ->get(route('records.improvement.edit', $record))
            ->assertOk()
            ->assertSee($record->expected_result)
            ->assertSee('A：想定より良い結果が出た');

        $this->actingAs($owner)
            ->patch(route('records.improvement.update', $record), [
                'evaluation' => 'A',
                'executed_at' => '2026-09-27',
                'actual_result' => '想定以上に集中して作業できました。',
            ])
            ->assertRedirect(route('records.show', $record));

        $this->assertDatabaseHas('improvement_records', [
            'daily_record_id' => $record->id,
            'execution_status' => 'executed',
            'result_evaluation' => 'A',
            'actual_result' => '想定以上に集中して作業できました。',
            'not_executed_reason' => null,
        ]);
        $this->assertSame('2026-09-27', $record->improvementRecord()->firstOrFail()->executed_at->toDateString());
    }

    public function test_owner_can_store_a_not_executed_improvement_record_with_a_reason(): void
    {
        $owner = User::factory()->create();
        $record = $this->createRecord($owner);

        $this->actingAs($owner)
            ->patch(route('records.improvement.update', $record), [
                'evaluation' => 'D',
                'not_executed_reason' => 'other',
                'not_executed_note' => '急な顧客対応が入りました。',
            ])
            ->assertRedirect(route('records.show', $record));

        $this->assertDatabaseHas('improvement_records', [
            'daily_record_id' => $record->id,
            'execution_status' => 'not_executed',
            'result_evaluation' => null,
            'executed_at' => null,
            'actual_result' => null,
            'not_executed_reason' => 'other',
            'not_executed_note' => '急な顧客対応が入りました。',
        ]);
    }

    public function test_executed_and_not_executed_validations_are_enforced(): void
    {
        $owner = User::factory()->create();
        $record = $this->createRecord($owner);

        $this->actingAs($owner)
            ->patch(route('records.improvement.update', $record), ['evaluation' => 'B'])
            ->assertSessionHasErrors(['executed_at', 'actual_result']);

        $this->actingAs($owner)
            ->patch(route('records.improvement.update', $record), ['evaluation' => 'D'])
            ->assertSessionHasErrors('not_executed_reason');

        $this->actingAs($owner)
            ->patch(route('records.improvement.update', $record), [
                'evaluation' => 'D',
                'not_executed_reason' => 'other',
            ])
            ->assertSessionHasErrors('not_executed_note');

        $this->assertDatabaseCount('improvement_records', 0);
    }

    public function test_owner_can_update_on_day_seven_until_2359_but_not_after_the_deadline(): void
    {
        $owner = User::factory()->create();
        $record = $this->createRecord($owner, '2026-09-21 09:00:00');

        Carbon::setTestNow(Carbon::create(2026, 9, 27, 23, 59, 59, 'Asia/Tokyo'));

        $this->actingAs($owner)
            ->patch(route('records.improvement.update', $record), [
                'evaluation' => 'B',
                'executed_at' => '2026-09-27',
                'actual_result' => '想定どおり実施できました。',
            ])
            ->assertRedirect(route('records.show', $record));

        Carbon::setTestNow(Carbon::create(2026, 9, 28, 0, 0, 0, 'Asia/Tokyo'));

        $this->actingAs($owner)
            ->patch(route('records.improvement.update', $record), [
                'evaluation' => 'A',
                'executed_at' => '2026-09-27',
                'actual_result' => '期限後に変更しようとした内容です。',
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('improvement_records', [
            'daily_record_id' => $record->id,
            'result_evaluation' => 'B',
        ]);
    }

    public function test_other_users_cannot_access_or_update_an_improvement_record(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $record = $this->createRecord($owner);

        $this->actingAs($otherUser)
            ->get(route('records.improvement.edit', $record))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->patch(route('records.improvement.update', $record), [
                'evaluation' => 'A',
                'executed_at' => '2026-09-27',
                'actual_result' => '不正な更新です。',
            ])
            ->assertForbidden();

        $this->assertDatabaseCount('improvement_records', 0);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function dailyRecordPayload(array $overrides = []): array
    {
        return array_merge([
            'record_date' => '2026-09-27',
            'actions' => '商談準備をしました。',
            'good_points' => '早めに資料を確認できました。',
            'improvement_points' => '提案内容の整理が不足していました。',
            'improvement_strategy' => '朝に提案内容を箇条書きにします。',
            'expected_result' => '商談前に提案の優先順位を説明できるようにします。',
            'is_public' => false,
        ], $overrides);
    }

    private function createRecord(User $user, ?string $createdAt = null): DailyRecord
    {
        return DailyRecord::factory()->for($user)->create([
            'record_date' => '2026-09-20',
            'created_at' => $createdAt ?? now(),
            'updated_at' => $createdAt ?? now(),
        ]);
    }
}
