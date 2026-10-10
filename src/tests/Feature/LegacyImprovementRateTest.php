<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class LegacyImprovementRateTest extends TestCase
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

    public function test_legacy_improvement_rate_payload_is_rejected_and_not_saved(): void
    {
        $owner = User::factory()->create();
        $record = DailyRecord::factory()->for($owner)->create(['record_date' => '2026-09-26']);

        $this->actingAs($owner)
            ->patch(route('records.improvement.update', $record), [
                'improvement_result' => '旧形式の改善結果',
                'improvement_rate' => 60,
            ])
            ->assertSessionHasErrors(['evaluation' => '評価を選択してください。']);

        $this->assertDatabaseHas('daily_records', [
            'id' => $record->id,
            'improvement_result' => null,
            'improvement_rate' => null,
        ]);
        $this->assertDatabaseCount('improvement_records', 0);
    }

    public function test_legacy_improvement_values_are_still_displayed_as_old_records(): void
    {
        $owner = User::factory()->create();
        $record = DailyRecord::factory()->for($owner)->create(['record_date' => '2026-09-01']);
        $record->forceFill([
            'improvement_result' => '旧仕様で記録した改善結果です。',
            'improvement_rate' => 40,
        ])->save();

        $this->actingAs($owner)
            ->get(route('records.show', $record))
            ->assertOk()
            ->assertSee('改善結果（旧記録）')
            ->assertSee('旧仕様で記録した改善結果です。')
            ->assertSee('40%');
    }

    public function test_legacy_chart_is_hidden_when_the_user_has_no_legacy_records(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('improvement-records.index'))
            ->assertOk()
            ->assertDontSee('参考：従来の改善率の推移');
    }

    public function test_legacy_values_cannot_be_mass_assigned_from_a_daily_record_update(): void
    {
        $owner = User::factory()->create();
        $record = DailyRecord::factory()->for($owner)->create();

        $record->update(['improvement_rate' => 100, 'improvement_result' => '上書き']);

        $this->assertDatabaseHas('daily_records', [
            'id' => $record->id,
            'improvement_result' => null,
            'improvement_rate' => null,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_improvement_routes(): void
    {
        $record = DailyRecord::factory()->create(['record_date' => '2026-09-26']);

        $this->get(route('records.improvement.edit', $record))
            ->assertRedirect(route('login'));

        $this->patch(route('records.improvement.update', $record), ['evaluation' => 'D', 'not_executed_reason' => 'forgot'])
            ->assertRedirect(route('login'));
    }
}
