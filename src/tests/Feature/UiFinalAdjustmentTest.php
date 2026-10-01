<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiFinalAdjustmentTest extends TestCase
{
    use RefreshDatabase;

    public function test_forms_render_validation_aligned_required_and_optional_indicators(): void
    {
        $this->get(route('register'))
            ->assertOk()
            ->assertSee('ユーザー名<span class="field-required">必須</span>', false)
            ->assertSee('年齢<span class="field-optional">任意</span>', false)
            ->assertSee('8文字以上で、英大文字・英小文字・数字をそれぞれ含めてください。');

        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('records.create'))
            ->assertOk()
            ->assertSee('今日やったこと<span class="field-required">必須</span>', false)
            ->assertSee('画像<span class="field-optional">任意</span>', false);
    }

    public function test_main_updates_flash_a_success_message(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('records.store'), $this->dailyRecordPayload())
            ->assertRedirect(route('home'))
            ->assertSessionHas('success', '日報を登録しました。');

        $record = DailyRecord::factory()->for($user)->create(['record_date' => now()->toDateString()]);

        $this->actingAs($user)
            ->patch(route('records.improvement.update', $record), [
                'improvement_result' => '実行して振り返りました。',
                'improvement_rate' => 60,
            ])
            ->assertRedirect(route('records.show', $record))
            ->assertSessionHas('success', '改善結果を保存しました。');

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'age' => $user->age,
                'gender' => $user->gender,
            ])
            ->assertRedirect(route('mypage'))
            ->assertSessionHas('success', 'プロフィールを更新しました。');
    }

    public function test_titles_and_daily_record_delete_confirmation_are_rendered(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<title>ログイン | Kaizen Log</title>', false);

        $user = User::factory()->create();
        $record = DailyRecord::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('records.show', $record))
            ->assertOk()
            ->assertSee('<title>日報詳細 | Kaizen Log</title>', false)
            ->assertSee("onsubmit=\"return confirm('この日報を削除しますか？')\"", false);
    }

    /**
     * @return array<string, mixed>
     */
    private function dailyRecordPayload(): array
    {
        return [
            'record_date' => now()->toDateString(),
            'actions' => '今日やったことです。',
            'good_points' => '良かったことです。',
            'improvement_points' => '改善点です。',
            'improvement_strategy' => '改善策です。',
            'is_public' => false,
        ];
    }
}
