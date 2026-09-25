<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyRecordTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_create_a_daily_record(): void
    {
        $user = User::factory()->create();

        $dailyRecordData = [
            'record_date' => now()->subDay()->toDateString(),
            'actions' => 'Feature Testで日報を作成しました。',
            'good_points' => '良かったことです。',
            'improvement_points' => '改善点です。',
            'improvement_strategy' => '改善策です。',
            'is_public' => false,
        ];

        $response = $this->actingAs($user)
            ->post(route('records.store'), $dailyRecordData);

        $response->assertRedirect(route('home'));

        $this->assertDatabaseHas('daily_records', [
            'user_id' => $user->id,
            'record_date' => $dailyRecordData['record_date'],
            'actions' => $dailyRecordData['actions'],
            'good_points' => $dailyRecordData['good_points'],
            'improvement_points' => $dailyRecordData['improvement_points'],
            'improvement_strategy' => $dailyRecordData['improvement_strategy'],
            'is_public' => false,
        ]);
    }

    public function test_authenticated_user_can_view_only_their_own_daily_records_on_home(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();

        $ownRecord = DailyRecord::factory()->for($user)->create([
            'actions' => '自分の日報です。',
        ]);
        $otherRecord = DailyRecord::factory()->for($otherUser)->create([
            'actions' => '他ユーザーの日報です。',
        ]);

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee($ownRecord->actions);
        $response->assertDontSee($otherRecord->actions);
    }

    public function test_authenticated_user_can_view_their_own_daily_record(): void
    {
        $user = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($user)->create([
            'actions' => '詳細画面で確認する日報です。',
        ]);

        $response = $this->actingAs($user)
            ->get(route('records.show', $dailyRecord));

        $response->assertOk();
        $response->assertSee($dailyRecord->actions);
    }

    public function test_authenticated_user_can_update_their_own_daily_record(): void
    {
        $user = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($user)->create();

        $updatedData = [
            'record_date' => now()->subDays(2)->toDateString(),
            'actions' => '更新後の今日やったことです。',
            'good_points' => '更新後の良かったことです。',
            'improvement_points' => '更新後の改善点です。',
            'improvement_strategy' => '更新後の改善策です。',
            'is_public' => true,
        ];

        $response = $this->actingAs($user)
            ->patch(route('records.update', $dailyRecord), $updatedData);

        $response->assertRedirect(route('records.show', $dailyRecord));

        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'record_date' => $updatedData['record_date'],
            'actions' => $updatedData['actions'],
            'good_points' => $updatedData['good_points'],
            'improvement_points' => $updatedData['improvement_points'],
            'improvement_strategy' => $updatedData['improvement_strategy'],
            'is_public' => true,
        ]);
    }

    public function test_authenticated_user_can_soft_delete_their_own_daily_record(): void
    {
        $user = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($user)->create();

        $response = $this->actingAs($user)
            ->delete(route('records.destroy', $dailyRecord));

        $response->assertRedirect(route('home'));
        $this->assertSoftDeleted('daily_records', ['id' => $dailyRecord->id]);
        $this->assertNull(DailyRecord::find($dailyRecord->id));
    }
}
