<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DailyRecordImprovementTest extends TestCase
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

    public function test_owner_can_open_the_improvement_form_and_save_an_improvement_result_within_the_deadline(): void
    {
        $owner = User::factory()->create();
        $dailyRecord = $this->createDailyRecord($owner);
        $data = [
            'improvement_result' => '改善策を実行したところ、集中して作業できました。',
        ];

        $this->actingAs($owner)
            ->get(route('records.improvement.edit', $dailyRecord))
            ->assertOk()
            ->assertSee($dailyRecord->record_date)
            ->assertSee($dailyRecord->improvement_strategy);

        $response = $this->actingAs($owner)
            ->patch(route('records.improvement.update', $dailyRecord), $data);

        $response->assertRedirect(route('records.show', $dailyRecord));
        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'improvement_result' => $data['improvement_result'],
            'improvement_rate' => null,
        ]);
    }

    public function test_owner_can_save_each_allowed_improvement_rate(): void
    {
        $owner = User::factory()->create();

        foreach ([0, 20, 40, 60, 80, 100] as $rate) {
            $dailyRecord = $this->createDailyRecord($owner);

            $this->actingAs($owner)
                ->patch(route('records.improvement.update', $dailyRecord), [
                    'improvement_rate' => $rate,
                ])
                ->assertRedirect(route('records.show', $dailyRecord));

            $this->assertDatabaseHas('daily_records', [
                'id' => $dailyRecord->id,
                'improvement_rate' => $rate,
            ]);
        }
    }

    public function test_owner_can_save_both_an_improvement_result_and_an_improvement_rate(): void
    {
        $owner = User::factory()->create();
        $dailyRecord = $this->createDailyRecord($owner);
        $data = [
            'improvement_result' => '改善結果と改善率を同時に記録します。',
            'improvement_rate' => 60,
        ];

        $response = $this->actingAs($owner)
            ->patch(route('records.improvement.update', $dailyRecord), $data);

        $response->assertRedirect(route('records.show', $dailyRecord));
        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'improvement_result' => $data['improvement_result'],
            'improvement_rate' => $data['improvement_rate'],
        ]);
    }

    public function test_owner_cannot_save_an_unallowed_improvement_rate(): void
    {
        $owner = User::factory()->create();
        $dailyRecord = $this->createDailyRecord($owner);

        $response = $this->actingAs($owner)
            ->patch(route('records.improvement.update', $dailyRecord), [
                'improvement_rate' => 10,
            ]);

        $response->assertSessionHasErrors('improvement_rate');
        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'improvement_rate' => null,
        ]);
    }

    public function test_owner_can_save_an_improvement_result_of_exactly_1000_characters(): void
    {
        $owner = User::factory()->create();
        $dailyRecord = $this->createDailyRecord($owner);
        $improvementResult = str_repeat('a', 1000);

        $response = $this->actingAs($owner)
            ->patch(route('records.improvement.update', $dailyRecord), [
                'improvement_result' => $improvementResult,
            ]);

        $response->assertRedirect(route('records.show', $dailyRecord));
        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'improvement_result' => $improvementResult,
        ]);
    }

    public function test_owner_cannot_save_an_improvement_result_of_more_than_1000_characters(): void
    {
        $owner = User::factory()->create();
        $dailyRecord = $this->createDailyRecord($owner);

        $response = $this->actingAs($owner)
            ->patch(route('records.improvement.update', $dailyRecord), [
                'improvement_result' => str_repeat('a', 1001),
            ]);

        $response->assertSessionHasErrors('improvement_result');
        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'improvement_result' => null,
        ]);
    }

    public function test_owner_can_update_improvement_on_the_seventh_day_after_record_date(): void
    {
        $owner = User::factory()->create();
        $dailyRecord = $this->createDailyRecord($owner, '2026-09-20');

        $response = $this->actingAs($owner)
            ->patch(route('records.improvement.update', $dailyRecord), [
                'improvement_rate' => 80,
            ]);

        $response->assertRedirect(route('records.show', $dailyRecord));
        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'improvement_rate' => 80,
        ]);
    }

    public function test_owner_cannot_access_or_update_improvement_after_the_eighth_day_and_existing_values_remain(): void
    {
        $owner = User::factory()->create();
        $dailyRecord = $this->createDailyRecord($owner, '2026-09-19', [
            'improvement_result' => '期限切れ前に記録した改善結果です。',
            'improvement_rate' => 40,
        ]);

        $this->actingAs($owner)
            ->get(route('records.improvement.edit', $dailyRecord))
            ->assertForbidden();

        $this->actingAs($owner)
            ->patch(route('records.improvement.update', $dailyRecord), [
                'improvement_result' => '期限切れ後に変更しようとした内容です。',
                'improvement_rate' => 100,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'improvement_result' => '期限切れ前に記録した改善結果です。',
            'improvement_rate' => 40,
        ]);
    }

    public function test_other_user_cannot_open_or_update_another_users_improvement(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $dailyRecord = $this->createDailyRecord($owner);

        $this->actingAs($otherUser)
            ->get(route('records.improvement.edit', $dailyRecord))
            ->assertForbidden();

        $this->actingAs($otherUser)
            ->patch(route('records.improvement.update', $dailyRecord), [
                'improvement_rate' => 100,
            ])
            ->assertForbidden();

        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'improvement_rate' => null,
        ]);
    }

    public function test_guest_is_redirected_to_login_when_accessing_improvement_routes(): void
    {
        $dailyRecord = DailyRecord::factory()->create([
            'record_date' => '2026-09-20',
            'improvement_result' => null,
            'improvement_rate' => null,
        ]);

        $this->get(route('records.improvement.edit', $dailyRecord))
            ->assertRedirect(route('login'));

        $this->patch(route('records.improvement.update', $dailyRecord), [
            'improvement_rate' => 20,
        ])->assertRedirect(route('login'));
    }

    public function test_owner_can_clear_both_optional_improvement_values(): void
    {
        $owner = User::factory()->create();
        $dailyRecord = $this->createDailyRecord($owner, '2026-09-20', [
            'improvement_result' => '以前の改善結果です。',
            'improvement_rate' => 60,
        ]);

        $response = $this->actingAs($owner)
            ->patch(route('records.improvement.update', $dailyRecord), [
                'improvement_result' => '',
                'improvement_rate' => '',
            ]);

        $response->assertRedirect(route('records.show', $dailyRecord));
        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'improvement_result' => null,
            'improvement_rate' => null,
        ]);
    }

    private function createDailyRecord(User $user, string $recordDate = '2026-09-20', array $overrides = []): DailyRecord
    {
        return DailyRecord::factory()->for($user)->create(array_merge([
            'record_date' => $recordDate,
            'improvement_result' => null,
            'improvement_rate' => null,
        ], $overrides));
    }
}
