<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommunityFollowTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_community_user_detail_and_follow_routes(): void
    {
        $user = User::factory()->create();

        $this->get(route('community.index'))->assertRedirect(route('login'));
        $this->get(route('users.show', $user))->assertRedirect(route('login'));
        $this->post(route('users.follow.store', $user))->assertRedirect(route('login'));
    }

    public function test_community_excludes_own_daily_records_and_displays_other_users_public_daily_records(): void
    {
        $viewer = User::factory()->create();
        $ownRecord = $this->createDailyRecord($viewer, [
            'is_public' => true,
            'actions' => '自分の日報はCommunityに表示しません。',
        ]);
        $otherUser = User::factory()->create();
        $publicRecord = $this->createDailyRecord($otherUser, [
            'is_public' => true,
            'actions' => '他ユーザーの公開日報です。',
        ]);

        $response = $this->actingAs($viewer)->get(route('community.index'));

        $response->assertOk();
        $response->assertSee($publicRecord->actions);
        $response->assertDontSee($ownRecord->actions);
    }

    public function test_community_hides_private_daily_records_without_a_follow_relationship(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $privateRecord = $this->createDailyRecord($owner, [
            'is_public' => false,
            'actions' => '関係がない相手の非公開日報です。',
        ]);

        $this->actingAs($viewer)
            ->get(route('community.index'))
            ->assertDontSee($privateRecord->actions);
    }

    public function test_community_hides_private_daily_records_with_one_way_following(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $privateRecord = $this->createDailyRecord($owner, [
            'is_public' => false,
            'actions' => '一方向フォローでは見えない非公開日報です。',
        ]);
        Follow::factory()->create([
            'follower_id' => $viewer->id,
            'followed_id' => $owner->id,
        ]);

        $this->actingAs($viewer)
            ->get(route('community.index'))
            ->assertDontSee($privateRecord->actions);
    }

    public function test_community_displays_private_daily_records_for_mutual_followers(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $privateRecord = $this->createDailyRecord($owner, [
            'is_public' => false,
            'actions' => '相互フォローなら見える非公開日報です。',
        ]);
        $this->createMutualFollowing($viewer, $owner);

        $this->actingAs($viewer)
            ->get(route('community.index'))
            ->assertSee($privateRecord->actions);
    }

    public function test_community_does_not_display_soft_deleted_daily_records(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $deletedRecord = $this->createDailyRecord($owner, [
            'is_public' => true,
            'actions' => '削除済みの日報です。',
        ]);
        $deletedRecord->delete();

        $this->actingAs($viewer)
            ->get(route('community.index'))
            ->assertDontSee($deletedRecord->actions);
    }

    public function test_community_orders_daily_records_by_record_date_then_created_at_descending(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $olderRecord = $this->createDailyRecord($owner, [
            'record_date' => '2026-09-20',
            'created_at' => '2026-09-20 09:00:00',
            'actions' => '同日では古い日報です。',
            'is_public' => true,
        ]);
        $newerSameDayRecord = $this->createDailyRecord($owner, [
            'record_date' => '2026-09-20',
            'created_at' => '2026-09-20 12:00:00',
            'actions' => '同日では新しい日報です。',
            'is_public' => true,
        ]);
        $newerDateRecord = $this->createDailyRecord($owner, [
            'record_date' => '2026-09-21',
            'created_at' => '2026-09-21 09:00:00',
            'actions' => '日付が最も新しい日報です。',
            'is_public' => true,
        ]);

        $this->actingAs($viewer)
            ->get(route('community.index'))
            ->assertSeeInOrder([
                $newerDateRecord->actions,
                $newerSameDayRecord->actions,
                $olderRecord->actions,
            ]);
    }

    public function test_user_detail_displays_public_daily_records_without_email_or_password(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create([
            'name' => '公開日報の投稿者',
            'age' => 30,
            'gender' => '回答しない',
        ]);
        $publicRecord = $this->createDailyRecord($owner, [
            'is_public' => true,
            'actions' => 'ユーザー詳細で表示する公開日報です。',
        ]);
        $privateRecord = $this->createDailyRecord($owner, [
            'is_public' => false,
            'actions' => 'ユーザー詳細で非表示の非公開日報です。',
        ]);

        $response = $this->actingAs($viewer)->get(route('users.show', $owner));

        $response->assertOk();
        $response->assertSee($owner->name);
        $response->assertSee('30');
        $response->assertSee($owner->gender);
        $response->assertSee($publicRecord->actions);
        $response->assertDontSee($privateRecord->actions);
        $response->assertDontSee($owner->email);
        $response->assertDontSee($owner->password);
    }

    public function test_user_detail_displays_private_daily_records_for_mutual_followers(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $privateRecord = $this->createDailyRecord($owner, [
            'is_public' => false,
            'actions' => '相互フォロー時にユーザー詳細で表示する非公開日報です。',
        ]);
        $this->createMutualFollowing($viewer, $owner);

        $this->actingAs($viewer)
            ->get(route('users.show', $owner))
            ->assertSee($privateRecord->actions);
    }

    public function test_missing_user_detail_returns_not_found(): void
    {
        $viewer = User::factory()->create();

        $this->actingAs($viewer)
            ->get(route('users.show', 999999))
            ->assertNotFound();
    }

    public function test_own_user_detail_does_not_display_follow_controls(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('users.show', $user));

        $response->assertOk();
        $response->assertDontSee('フォローする');
        $response->assertDontSee('フォローを解除する');
    }

    public function test_authenticated_user_can_follow_another_user(): void
    {
        $viewer = User::factory()->create();
        $userToFollow = User::factory()->create();

        $response = $this->actingAs($viewer)
            ->post(route('users.follow.store', $userToFollow));

        $response->assertRedirect(route('users.show', $userToFollow));
        $this->assertDatabaseHas('follows', [
            'follower_id' => $viewer->id,
            'followed_id' => $userToFollow->id,
        ]);
        $this->assertNotNull(Follow::query()
            ->where('follower_id', $viewer->id)
            ->where('followed_id', $userToFollow->id)
            ->value('created_at'));
    }

    public function test_authenticated_user_can_unfollow_another_user(): void
    {
        $viewer = User::factory()->create();
        $userToUnfollow = User::factory()->create();
        Follow::factory()->create([
            'follower_id' => $viewer->id,
            'followed_id' => $userToUnfollow->id,
        ]);

        $response = $this->actingAs($viewer)
            ->delete(route('users.follow.destroy', $userToUnfollow));

        $response->assertRedirect(route('users.show', $userToUnfollow));
        $this->assertDatabaseMissing('follows', [
            'follower_id' => $viewer->id,
            'followed_id' => $userToUnfollow->id,
        ]);
    }

    public function test_unfollowing_a_user_who_is_not_followed_does_not_remove_other_follow_records(): void
    {
        $viewer = User::factory()->create();
        $userToUnfollow = User::factory()->create();
        $otherFollowedUser = User::factory()->create();
        Follow::factory()->create([
            'follower_id' => $viewer->id,
            'followed_id' => $otherFollowedUser->id,
        ]);

        $this->actingAs($viewer)
            ->delete(route('users.follow.destroy', $userToUnfollow))
            ->assertRedirect(route('users.show', $userToUnfollow));

        $this->assertDatabaseHas('follows', [
            'follower_id' => $viewer->id,
            'followed_id' => $otherFollowedUser->id,
        ]);
        $this->assertDatabaseMissing('follows', [
            'follower_id' => $viewer->id,
            'followed_id' => $userToUnfollow->id,
        ]);
    }

    public function test_user_cannot_follow_themselves(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('users.follow.store', $user))
            ->assertForbidden();

        $this->assertDatabaseCount('follows', 0);
    }

    public function test_duplicate_follow_request_keeps_only_one_follow_record(): void
    {
        $viewer = User::factory()->create();
        $userToFollow = User::factory()->create();

        $this->actingAs($viewer)->post(route('users.follow.store', $userToFollow));
        $this->actingAs($viewer)->post(route('users.follow.store', $userToFollow));

        $this->assertSame(1, Follow::query()
            ->where('follower_id', $viewer->id)
            ->where('followed_id', $userToFollow->id)
            ->count());
    }

    public function test_one_way_following_is_not_mutual_following(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $this->actingAs($firstUser)->post(route('users.follow.store', $secondUser));

        $this->assertFalse($firstUser->isMutuallyFollowing($secondUser));
        $this->assertFalse($secondUser->isMutuallyFollowing($firstUser));
    }

    public function test_two_way_following_is_mutual_following(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();

        $this->actingAs($firstUser)->post(route('users.follow.store', $secondUser));
        $this->actingAs($secondUser)->post(route('users.follow.store', $firstUser));

        $this->assertTrue($firstUser->isMutuallyFollowing($secondUser));
        $this->assertTrue($secondUser->isMutuallyFollowing($firstUser));
    }

    public function test_unfollowing_removes_private_daily_record_access_from_detail_community_and_user_detail(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $privateRecord = $this->createDailyRecord($owner, [
            'is_public' => false,
            'actions' => '解除後に見えなくなる非公開日報です。',
        ]);
        $this->createMutualFollowing($viewer, $owner);

        $this->actingAs($viewer)->get(route('records.show', $privateRecord))->assertOk();
        $this->actingAs($viewer)->get(route('community.index'))->assertSee($privateRecord->actions);
        $this->actingAs($viewer)->get(route('users.show', $owner))->assertSee($privateRecord->actions);

        $this->actingAs($viewer)
            ->delete(route('users.follow.destroy', $owner))
            ->assertRedirect(route('users.show', $owner));

        $this->actingAs($viewer)->get(route('records.show', $privateRecord))->assertForbidden();
        $this->actingAs($viewer)->get(route('community.index'))->assertDontSee($privateRecord->actions);
        $this->actingAs($viewer)->get(route('users.show', $owner))->assertDontSee($privateRecord->actions);
    }

    public function test_changing_a_public_daily_record_to_private_removes_access_for_non_mutual_user(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $dailyRecord = $this->createDailyRecord($owner, [
            'is_public' => true,
            'actions' => '公開から非公開に変える日報です。',
        ]);

        $this->actingAs($viewer)->get(route('records.show', $dailyRecord))->assertOk();
        $this->actingAs($viewer)->get(route('community.index'))->assertSee($dailyRecord->actions);

        $this->actingAs($owner)
            ->patch(route('records.update', $dailyRecord), $this->validDailyRecordData([
                'is_public' => false,
                'actions' => $dailyRecord->actions,
            ]))
            ->assertRedirect(route('records.show', $dailyRecord));

        $this->actingAs($viewer)->get(route('records.show', $dailyRecord))->assertForbidden();
        $this->actingAs($viewer)->get(route('community.index'))->assertDontSee($dailyRecord->actions);
    }

    public function test_changing_a_private_daily_record_to_public_displays_it_in_community_for_non_mutual_user(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $dailyRecord = $this->createDailyRecord($owner, [
            'is_public' => false,
            'actions' => '非公開から公開に変える日報です。',
        ]);

        $this->actingAs($viewer)->get(route('community.index'))->assertDontSee($dailyRecord->actions);

        $this->actingAs($owner)
            ->patch(route('records.update', $dailyRecord), $this->validDailyRecordData([
                'is_public' => true,
                'actions' => $dailyRecord->actions,
            ]))
            ->assertRedirect(route('records.show', $dailyRecord));

        $this->actingAs($viewer)->get(route('community.index'))->assertSee($dailyRecord->actions);
    }

    public function test_daily_record_create_page_asks_for_public_or_private_selection_with_private_as_default(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('records.create'));

        $response->assertOk();
        $response->assertSee('この日報を公開しますか？');
        $response->assertSee('value="0" checked', false);
    }

    private function createDailyRecord(User $user, array $overrides = []): DailyRecord
    {
        return DailyRecord::factory()->for($user)->create(array_merge([
            'record_date' => '2026-09-20',
            'improvement_result' => null,
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

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validDailyRecordData(array $overrides = []): array
    {
        return array_merge([
            'record_date' => now()->subDay()->toDateString(),
            'actions' => '公開設定を変更する日報です。',
            'good_points' => '良かったことです。',
            'improvement_points' => '改善点です。',
            'improvement_strategy' => '改善策です。',
            'is_public' => false,
        ], $overrides);
    }
}
