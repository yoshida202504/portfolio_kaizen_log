<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DailyRecordAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_accessing_daily_record_pages(): void
    {
        $dailyRecord = DailyRecord::factory()->create();

        $this->get(route('records.create'))->assertRedirect(route('login'));
        $this->get(route('records.show', $dailyRecord))->assertRedirect(route('login'));
        $this->get(route('records.edit', $dailyRecord))->assertRedirect(route('login'));
    }

    public function test_owner_can_view_their_own_public_daily_record(): void
    {
        $owner = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($owner)->create([
            'is_public' => true,
            'actions' => '本人が閲覧する公開日報です。',
        ]);

        $response = $this->actingAs($owner)->get(route('records.show', $dailyRecord));

        $response->assertOk();
        $response->assertSee($dailyRecord->actions);
    }

    public function test_owner_can_view_their_own_private_daily_record(): void
    {
        $owner = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($owner)->create([
            'is_public' => false,
            'actions' => '本人が閲覧する非公開日報です。',
        ]);

        $response = $this->actingAs($owner)->get(route('records.show', $dailyRecord));

        $response->assertOk();
        $response->assertSee($dailyRecord->actions);
    }

    public function test_other_user_can_view_a_public_daily_record(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($owner)->create([
            'is_public' => true,
            'actions' => '他ユーザーも閲覧できる公開日報です。',
        ]);

        $response = $this->actingAs($viewer)->get(route('records.show', $dailyRecord));

        $response->assertOk();
        $response->assertSee($dailyRecord->actions);
    }

    public function test_other_user_cannot_view_a_private_daily_record_without_mutual_following(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($owner)->create(['is_public' => false]);

        $this->actingAs($viewer)
            ->get(route('records.show', $dailyRecord))
            ->assertForbidden();
    }

    public function test_one_way_following_does_not_allow_viewing_a_private_daily_record(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($owner)->create(['is_public' => false]);
        Follow::factory()->create([
            'follower_id' => $viewer->id,
            'followed_id' => $owner->id,
        ]);

        $this->actingAs($viewer)
            ->get(route('records.show', $dailyRecord))
            ->assertForbidden();
    }

    public function test_mutual_following_allows_viewing_a_private_daily_record(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($owner)->create([
            'is_public' => false,
            'actions' => '相互フォロー相手が閲覧できる非公開日報です。',
        ]);
        $this->createMutualFollowing($owner, $viewer);

        $response = $this->actingAs($viewer)->get(route('records.show', $dailyRecord));

        $response->assertOk();
        $response->assertSee($dailyRecord->actions);
    }

    public function test_removing_one_follow_prevents_viewing_a_private_daily_record_again(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($owner)->create(['is_public' => false]);
        $followFromOwner = $this->createMutualFollowing($owner, $viewer);

        $this->actingAs($viewer)
            ->get(route('records.show', $dailyRecord))
            ->assertOk();

        $followFromOwner->delete();

        $this->actingAs($viewer)
            ->get(route('records.show', $dailyRecord))
            ->assertForbidden();
    }

    public function test_other_user_cannot_open_the_edit_page_even_when_mutually_following(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($owner)->create(['is_public' => true]);
        $this->createMutualFollowing($owner, $viewer);

        $this->actingAs($viewer)
            ->get(route('records.edit', $dailyRecord))
            ->assertForbidden();
    }

    public function test_other_user_cannot_update_a_daily_record(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($owner)->create([
            'actions' => '所有者の日報です。',
        ]);

        $response = $this->actingAs($viewer)->patch(
            route('records.update', $dailyRecord),
            $this->validDailyRecordData(['actions' => '他ユーザーによる更新内容です。']),
        );

        $response->assertForbidden();
        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'actions' => '所有者の日報です。',
        ]);
    }

    public function test_other_user_cannot_delete_a_daily_record(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($owner)->create();

        $response = $this->actingAs($viewer)->delete(route('records.destroy', $dailyRecord));

        $response->assertForbidden();
        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'deleted_at' => null,
        ]);
    }

    public function test_owner_can_edit_update_and_delete_their_own_daily_record(): void
    {
        $owner = User::factory()->create();
        $dailyRecord = DailyRecord::factory()->for($owner)->create();

        $this->actingAs($owner)
            ->get(route('records.edit', $dailyRecord))
            ->assertOk();

        $updatedData = $this->validDailyRecordData([
            'actions' => '本人が更新した日報です。',
        ]);

        $this->actingAs($owner)
            ->patch(route('records.update', $dailyRecord), $updatedData)
            ->assertRedirect(route('records.show', $dailyRecord));

        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'actions' => $updatedData['actions'],
        ]);

        $this->actingAs($owner)
            ->delete(route('records.destroy', $dailyRecord))
            ->assertRedirect(route('home'));

        $this->assertSoftDeleted('daily_records', ['id' => $dailyRecord->id]);
    }

    private function createMutualFollowing(User $firstUser, User $secondUser): Follow
    {
        $followFromFirstUser = Follow::factory()->create([
            'follower_id' => $firstUser->id,
            'followed_id' => $secondUser->id,
        ]);

        Follow::factory()->create([
            'follower_id' => $secondUser->id,
            'followed_id' => $firstUser->id,
        ]);

        return $followFromFirstUser;
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validDailyRecordData(array $overrides = []): array
    {
        return array_merge([
            'record_date' => now()->subDay()->toDateString(),
            'actions' => '認可テストの日報です。',
            'good_points' => '認可テストで良かったことです。',
            'improvement_points' => '認可テストの改善点です。',
            'improvement_strategy' => '認可テストの改善策です。',
            'is_public' => false,
        ], $overrides);
    }
}
