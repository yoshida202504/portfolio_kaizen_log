<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyRecordErrorTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_gets_a_404_for_a_missing_daily_record_show_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('records.show', 999999))
            ->assertNotFound();
    }

    public function test_authenticated_user_gets_a_404_for_a_missing_daily_record_edit_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('records.edit', 999999))
            ->assertNotFound();
    }

    public function test_authenticated_user_gets_a_404_when_updating_a_missing_daily_record(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->patch(
            route('records.update', 999999),
            $this->validDailyRecordData(),
        );

        $response->assertNotFound();
        $this->assertDatabaseCount('daily_records', 0);
    }

    public function test_authenticated_user_gets_a_404_when_deleting_a_missing_daily_record(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->delete(route('records.destroy', 999999))
            ->assertNotFound();
    }

    public function test_soft_deleted_daily_record_is_not_found_by_route_model_binding(): void
    {
        $user = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($user)->create();
        $dailyRecord->delete();

        $this->actingAs($user)
            ->get(route('records.show', $dailyRecord))
            ->assertNotFound();
    }

    public function test_get_request_to_post_only_daily_record_store_route_returns_method_not_allowed(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('records.store'))
            ->assertStatus(405);
    }

    public function test_post_request_to_get_only_daily_record_edit_route_returns_method_not_allowed(): void
    {
        $user = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($user)->create();

        $this->actingAs($user)
            ->post(route('records.edit', $dailyRecord))
            ->assertStatus(405);
    }

    public function test_feature_test_environment_does_not_require_a_csrf_token_for_a_valid_update_request(): void
    {
        $user = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($user)->create();
        $data = $this->validDailyRecordData([
            'actions' => 'CSRFトークンなしで更新されたテスト用の内容です。',
        ]);

        $response = $this->actingAs($user)
            ->patch(route('records.update', $dailyRecord), $data);

        $response->assertRedirect(route('records.show', $dailyRecord));
        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'actions' => $data['actions'],
        ]);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validDailyRecordData(array $overrides = []): array
    {
        return array_merge([
            'record_date' => now()->subDay()->toDateString(),
            'actions' => 'エラーケース確認用の日報です。',
            'good_points' => 'エラーケース確認で良かったことです。',
            'improvement_points' => 'エラーケース確認の改善点です。',
            'improvement_strategy' => 'エラーケース確認の改善策です。',
            'is_public' => false,
        ], $overrides);
    }
}
