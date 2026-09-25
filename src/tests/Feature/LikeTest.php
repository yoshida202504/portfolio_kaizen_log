<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\Follow;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_liking_a_daily_record(): void
    {
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);

        $this->post(route('records.like.store', $record))
            ->assertRedirect(route('login'));
    }

    public function test_user_can_like_another_users_public_daily_record(): void
    {
        $viewer = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);

        $this->actingAs($viewer)
            ->post(route('records.like.store', $record))
            ->assertRedirect(route('records.show', $record));

        $this->assertDatabaseHas('likes', [
            'user_id' => $viewer->id,
            'daily_record_id' => $record->id,
        ]);
        $this->assertNotNull(Like::query()
            ->where('user_id', $viewer->id)
            ->where('daily_record_id', $record->id)
            ->value('created_at'));
    }

    public function test_user_can_like_a_mutual_followers_private_daily_record(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $record = $this->createRecord($owner, ['is_public' => false]);
        $this->createMutualFollowing($viewer, $owner);

        $this->actingAs($viewer)
            ->post(route('records.like.store', $record))
            ->assertRedirect(route('records.show', $record));

        $this->assertDatabaseHas('likes', [
            'user_id' => $viewer->id,
            'daily_record_id' => $record->id,
        ]);
    }

    public function test_user_cannot_like_a_private_daily_record_without_mutual_following(): void
    {
        $viewer = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => false]);

        $this->actingAs($viewer)
            ->post(route('records.like.store', $record))
            ->assertForbidden();

        $this->assertDatabaseCount('likes', 0);
    }

    public function test_user_cannot_like_their_own_daily_record(): void
    {
        $owner = User::factory()->create();
        $record = $this->createRecord($owner, ['is_public' => true]);

        $this->actingAs($owner)
            ->post(route('records.like.store', $record))
            ->assertForbidden();

        $this->assertDatabaseCount('likes', 0);
    }

    public function test_duplicate_like_requests_keep_only_one_like(): void
    {
        $viewer = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);

        $this->actingAs($viewer)->post(route('records.like.store', $record));
        $this->actingAs($viewer)->post(route('records.like.store', $record));

        $this->assertSame(1, Like::query()
            ->where('user_id', $viewer->id)
            ->where('daily_record_id', $record->id)
            ->count());
    }

    public function test_user_can_remove_only_their_own_like(): void
    {
        $viewer = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);
        Like::factory()->create([
            'user_id' => $viewer->id,
            'daily_record_id' => $record->id,
        ]);

        $this->actingAs($viewer)
            ->delete(route('records.like.destroy', $record))
            ->assertRedirect(route('records.show', $record));

        $this->assertDatabaseMissing('likes', [
            'user_id' => $viewer->id,
            'daily_record_id' => $record->id,
        ]);
    }

    public function test_unliking_without_own_like_does_not_remove_another_users_like(): void
    {
        $viewer = User::factory()->create();
        $otherUser = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);
        Like::factory()->create([
            'user_id' => $otherUser->id,
            'daily_record_id' => $record->id,
        ]);

        $this->actingAs($viewer)
            ->delete(route('records.like.destroy', $record))
            ->assertRedirect(route('records.show', $record));

        $this->assertDatabaseHas('likes', [
            'user_id' => $otherUser->id,
            'daily_record_id' => $record->id,
        ]);
    }

    public function test_losing_mutual_follow_access_prevents_unliking_but_keeps_the_like_data(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $record = $this->createRecord($owner, ['is_public' => false]);
        $this->createMutualFollowing($viewer, $owner);
        Like::factory()->create([
            'user_id' => $viewer->id,
            'daily_record_id' => $record->id,
        ]);

        Follow::query()
            ->where('follower_id', $viewer->id)
            ->where('followed_id', $owner->id)
            ->delete();

        $this->actingAs($viewer)
            ->delete(route('records.like.destroy', $record))
            ->assertForbidden();

        $this->assertDatabaseHas('likes', [
            'user_id' => $viewer->id,
            'daily_record_id' => $record->id,
        ]);
    }

    public function test_changing_a_liked_public_daily_record_to_private_keeps_the_like_but_blocks_access(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $record = $this->createRecord($owner, ['is_public' => true]);
        Like::factory()->create([
            'user_id' => $viewer->id,
            'daily_record_id' => $record->id,
        ]);
        $record->update(['is_public' => false]);

        $this->actingAs($viewer)
            ->get(route('records.show', $record))
            ->assertForbidden();

        $this->assertDatabaseHas('likes', [
            'user_id' => $viewer->id,
            'daily_record_id' => $record->id,
        ]);
    }

    private function createRecord(User $owner, array $overrides = []): DailyRecord
    {
        return DailyRecord::factory()->for($owner)->create(array_merge([
            'record_date' => '2026-09-20',
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
