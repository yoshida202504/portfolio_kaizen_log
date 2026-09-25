<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\Follow;
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

    public function test_my_page_displays_profile_and_daily_record_links(): void
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
            ->assertSee($user->email)
            ->assertSee('プロフィールを編集する')
            ->assertSee('自分の日報一覧を見る')
            ->assertSee('日報を作成する');
    }

    public function test_my_page_displays_followed_users(): void
    {
        $user = User::factory()->create();
        $followedUser = User::factory()->create(['name' => 'フォロー中のユーザー']);
        Follow::factory()->create([
            'follower_id' => $user->id,
            'followed_id' => $followedUser->id,
        ]);

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertOk()
            ->assertSee('フォロー中のユーザー');
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

    public function test_my_page_displays_improvement_rate_trend_in_chronological_order(): void
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
            ->assertSee('改善率の推移')
            ->assertSee('aria-label="改善率の推移グラフ"', false)
            ->assertSeeInOrder([
                $firstRecord->record_date.'：20%',
                $secondRecord->record_date.'：80%',
            ]);
    }

    public function test_my_page_shows_message_when_no_improvement_rate_has_been_recorded(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('mypage'))
            ->assertSee('改善率が記録された日報はまだありません。');
    }

    private function createRecord(User $owner, array $overrides = []): DailyRecord
    {
        return DailyRecord::factory()->for($owner)->create(array_merge([
            'record_date' => '2026-09-20',
            'improvement_rate' => null,
        ], $overrides));
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
