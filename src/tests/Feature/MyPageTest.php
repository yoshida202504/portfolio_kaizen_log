<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\Follow;
use App\Models\ImprovementRecord;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MyPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_my_page(): void
    {
        $this->get(route('mypage'))->assertRedirect(route('login'));
    }

    public function test_my_page_displays_name_and_profile_edit_link(): void
    {
        $user = User::factory()->create([
            'name' => 'マイページ利用者',
            'age' => 30,
            'gender' => '回答しない',
        ]);

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertOk()
            ->assertSee('マイページ利用者')
            ->assertDontSee($user->email)
            ->assertDontSee('回答しない')
            ->assertSee('プロフィールを編集する')
            ->assertDontSee('自分の日報一覧を見る');
    }

    public function test_my_page_displays_followed_users(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create(['name' => 'フォロー中のユーザー']);
        $unrelatedUser = User::factory()->create(['name' => '関係のないユーザー']);
        Follow::factory()->create([
            'follower_id' => $user->id,
            'followed_id' => $followedUser->id,
        ]);

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertOk()
            ->assertSee('フォロー中')
            ->assertSee('フォロー中のユーザー')
            ->assertDontSee('関係のないユーザー');
    }

    public function test_my_page_displays_followers_without_unrelated_users(): void
    {
        $user = User::factory()->create();
        $follower = User::factory()->create(['name' => 'フォロワーのユーザー']);
        User::factory()->create(['name' => '別の関係ないユーザー']);
        Follow::factory()->create([
            'follower_id' => $follower->id,
            'followed_id' => $user->id,
        ]);

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertOk()
            ->assertSee('フォロワー')
            ->assertSee('フォロワーのユーザー')
            ->assertDontSee('別の関係ないユーザー');
    }

    public function test_my_page_displays_liked_public_daily_records(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create(['name' => '公開日報の投稿者']);
        $record = $this->createRecord($owner, [
            'is_public' => true,
            'actions' => 'いいねした公開日報です。',
        ]);
        Like::factory()->create([
            'user_id' => $user->id,
            'daily_record_id' => $record->id,
        ]);

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertOk()
            ->assertSee('公開日報の投稿者')
            ->assertSee('いいねした公開日報です。');
    }

    public function test_my_page_displays_liked_private_daily_records_only_while_mutually_following(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        $record = $this->createRecord($owner, [
            'is_public' => false,
            'actions' => '相互フォロー時だけ見えるいいね済み日報です。',
        ]);
        $this->createMutualFollowing($user, $owner);
        Like::factory()->create([
            'user_id' => $user->id,
            'daily_record_id' => $record->id,
        ]);

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertSee($record->actions);

        Follow::query()
            ->where('follower_id', $user->id)
            ->where('followed_id', $owner->id)
            ->delete();

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertDontSee($record->actions);
    }

    public function test_my_page_does_not_display_the_legacy_improvement_rate_chart(): void
    {
        $user = User::factory()->create();
        $firstRecord = $this->createRecord($user, [
            'record_date' => '2026-09-20',
            'improvement_rate' => 20,
        ]);
        $secondRecord = $this->createRecord($user, [
            'record_date' => '2026-09-22',
            'improvement_rate' => 80,
        ]);

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertOk()
            ->assertDontSee('改善率の推移')
            ->assertDontSee($firstRecord->record_date.'：20%')
            ->assertDontSee($secondRecord->record_date.'：80%');
    }

    public function test_my_page_does_not_render_the_legacy_improvement_rate_empty_state(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertDontSee('改善率が記録された日報はまだありません。');
    }

    public function test_my_page_summarizes_current_month_executed_improvements_and_pending_reflections(): void
    {
        $user = User::factory()->create();
        $executedRecord = $this->createRecord($user, ['record_date' => now()->toDateString()]);
        $notExecutedRecord = $this->createRecord($user, ['record_date' => now()->toDateString()]);
        $previousMonthRecord = $this->createRecord($user, ['record_date' => now()->subMonth()->toDateString()]);
        $pendingRecord = $this->createRecord($user, [
            'created_at' => now()->subDays(5),
            'updated_at' => now()->subDays(5),
        ]);

        ImprovementRecord::factory()->for($executedRecord, 'dailyRecord')->create([
            'result_evaluation' => 'A',
            'executed_at' => now()->toDateString(),
        ]);
        ImprovementRecord::factory()->for($notExecutedRecord, 'dailyRecord')->notExecuted()->create();
        ImprovementRecord::factory()->for($previousMonthRecord, 'dailyRecord')->create([
            'executed_at' => now()->subMonth()->toDateString(),
        ]);

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertOk()
            ->assertSee('今月の改善行動数')
            ->assertSee('未振り返り')
            ->assertSee('改善記録')
            ->assertSee('>1件<', false)
            ->assertSee(route('improvement-records.index'), false);

        $this->assertTrue($pendingRecord->isImprovementReflectionDue());
    }

    private function createRecord(User $owner, array $overrides = []): DailyRecord
    {
        $attributes = array_merge([
            'record_date' => '2026-09-20',
            'improvement_rate' => null,
        ], $overrides);

        return DailyRecord::factory()->for($owner)->create($attributes);
    }

    private function createMutualFollowing(User $firstUser, User $secondUser): void
    {
        Follow::factory()->create([
            'follower_id' => $firstUser->id,
            'followed_id' => $secondUser->id,
        ]);
        Follow::factory()->create([
            'follower_id' => $secondUser->id,
            'followed_id' => $firstUser->id,
        ]);
    }
}
