<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\DailyRecord;
use App\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CommentTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_when_posting_a_comment(): void
    {
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);

        $this->post(route('records.comments.store', $record), ['body' => '未ログインのコメント'])
            ->assertRedirect(route('login'));
    }

    public function test_user_can_comment_on_another_users_public_daily_record(): void
    {
        $commenter = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);

        $this->actingAs($commenter)
            ->post(route('records.comments.store', $record), ['body' => '公開日報へのコメントです。'])
            ->assertRedirect(route('records.show', $record));

        $this->assertDatabaseHas('comments', [
            'user_id' => $commenter->id,
            'daily_record_id' => $record->id,
            'body' => '公開日報へのコメントです。',
        ]);
    }

    public function test_user_can_comment_on_a_mutual_followers_private_daily_record(): void
    {
        $commenter = User::factory()->create();
        $owner = User::factory()->create();
        $record = $this->createRecord($owner, ['is_public' => false]);
        $this->createMutualFollowing($commenter, $owner);

        $this->actingAs($commenter)
            ->post(route('records.comments.store', $record), ['body' => '相互フォロー相手へのコメントです。'])
            ->assertRedirect(route('records.show', $record));

        $this->assertDatabaseHas('comments', [
            'user_id' => $commenter->id,
            'daily_record_id' => $record->id,
        ]);
    }

    public function test_user_cannot_comment_on_a_private_daily_record_without_mutual_following(): void
    {
        $commenter = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => false]);

        $this->actingAs($commenter)
            ->post(route('records.comments.store', $record), ['body' => '送信できないコメントです。'])
            ->assertForbidden();

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_user_cannot_comment_on_their_own_daily_record(): void
    {
        $owner = User::factory()->create();
        $record = $this->createRecord($owner, ['is_public' => true]);

        $this->actingAs($owner)
            ->post(route('records.comments.store', $record), ['body' => '自分へのコメントです。'])
            ->assertForbidden();

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_empty_comment_is_rejected(): void
    {
        $commenter = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);

        $this->actingAs($commenter)
            ->from(route('records.show', $record))
            ->post(route('records.comments.store', $record), ['body' => ''])
            ->assertRedirect(route('records.show', $record))
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_comment_with_1000_characters_is_saved(): void
    {
        $commenter = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);
        $body = str_repeat('あ', 1000);

        $this->actingAs($commenter)
            ->post(route('records.comments.store', $record), ['body' => $body])
            ->assertRedirect(route('records.show', $record));

        $this->assertDatabaseHas('comments', ['daily_record_id' => $record->id, 'body' => $body]);
    }

    public function test_comment_with_1001_characters_is_rejected(): void
    {
        $commenter = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);

        $this->actingAs($commenter)
            ->from(route('records.show', $record))
            ->post(route('records.comments.store', $record), ['body' => str_repeat('あ', 1001)])
            ->assertRedirect(route('records.show', $record))
            ->assertSessionHasErrors('body');

        $this->assertDatabaseCount('comments', 0);
    }

    public function test_comment_author_can_update_their_comment(): void
    {
        $commenter = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);
        $comment = $this->createComment($commenter, $record, '更新前のコメントです。');

        $this->actingAs($commenter)
            ->patch(route('comments.update', $comment), ['body' => '更新後のコメントです。'])
            ->assertRedirect(route('records.show', $record));

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => '更新後のコメントです。',
        ]);
    }

    public function test_comment_author_can_delete_their_comment_with_soft_delete(): void
    {
        $commenter = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);
        $comment = $this->createComment($commenter, $record, '削除するコメントです。');

        $this->actingAs($commenter)
            ->delete(route('comments.destroy', $comment))
            ->assertRedirect(route('records.show', $record));

        $this->assertSoftDeleted('comments', ['id' => $comment->id]);
    }

    public function test_another_user_cannot_update_or_delete_someone_elses_comment(): void
    {
        $commenter = User::factory()->create();
        $otherUser = User::factory()->create();
        $record = $this->createRecord(User::factory()->create(), ['is_public' => true]);
        $comment = $this->createComment($commenter, $record, '他人のコメントです。');

        $this->actingAs($otherUser)
            ->patch(route('comments.update', $comment), ['body' => '不正な更新です。'])
            ->assertForbidden();
        $this->actingAs($otherUser)
            ->delete(route('comments.destroy', $comment))
            ->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'body' => '他人のコメントです。', 'deleted_at' => null]);
    }

    public function test_daily_record_owner_cannot_update_or_delete_another_users_comment(): void
    {
        $owner = User::factory()->create();
        $commenter = User::factory()->create();
        $record = $this->createRecord($owner, ['is_public' => true]);
        $comment = $this->createComment($commenter, $record, '日報所有者以外のコメントです。');

        $this->actingAs($owner)
            ->patch(route('comments.update', $comment), ['body' => '所有者による更新です。'])
            ->assertForbidden();
        $this->actingAs($owner)
            ->delete(route('comments.destroy', $comment))
            ->assertForbidden();

        $this->assertDatabaseHas('comments', ['id' => $comment->id, 'deleted_at' => null]);
    }

    public function test_losing_mutual_follow_access_prevents_comment_update_and_keeps_the_comment_data(): void
    {
        $commenter = User::factory()->create();
        $owner = User::factory()->create();
        $record = $this->createRecord($owner, ['is_public' => false]);
        $this->createMutualFollowing($commenter, $owner);
        $comment = $this->createComment($commenter, $record, '解除後も残るコメントです。');

        Follow::query()
            ->where('follower_id', $commenter->id)
            ->where('followed_id', $owner->id)
            ->delete();

        $this->actingAs($commenter)
            ->patch(route('comments.update', $comment), ['body' => '更新できないコメントです。'])
            ->assertForbidden();
        $this->actingAs($commenter)
            ->delete(route('comments.destroy', $comment))
            ->assertForbidden();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'body' => '解除後も残るコメントです。',
            'deleted_at' => null,
        ]);
    }

    public function test_soft_deleted_comments_are_not_displayed_on_the_daily_record_detail_page(): void
    {
        $owner = User::factory()->create();
        $record = $this->createRecord($owner, ['is_public' => true]);
        $comment = $this->createComment(User::factory()->create(), $record, '表示されない削除済みコメントです。');
        $comment->delete();

        $this->actingAs($owner)
            ->get(route('records.show', $record))
            ->assertOk()
            ->assertDontSee('表示されない削除済みコメントです。');
    }

    public function test_comment_body_is_escaped_on_the_daily_record_detail_page(): void
    {
        $owner = User::factory()->create();
        $record = $this->createRecord($owner, ['is_public' => true]);
        $this->createComment(User::factory()->create(), $record, '<script>alert(1)</script>');

        $this->actingAs($owner)
            ->get(route('records.show', $record))
            ->assertDontSee('<script>alert(1)</script>', false)
            ->assertSee('&lt;script&gt;alert(1)&lt;/script&gt;', false);
    }

    public function test_changing_a_commented_public_daily_record_to_private_keeps_the_comment_but_blocks_access(): void
    {
        $commenter = User::factory()->create();
        $owner = User::factory()->create();
        $record = $this->createRecord($owner, ['is_public' => true]);
        $comment = $this->createComment($commenter, $record, '公開時に書いたコメントです。');
        $record->update(['is_public' => false]);

        $this->actingAs($commenter)
            ->get(route('records.show', $record))
            ->assertForbidden();

        $this->assertDatabaseHas('comments', [
            'id' => $comment->id,
            'deleted_at' => null,
        ]);
    }

    private function createRecord(User $owner, array $overrides = []): DailyRecord
    {
        return DailyRecord::factory()->for($owner)->create(array_merge([
            'record_date' => '2026-09-20',
        ], $overrides));
    }

    private function createComment(User $commenter, DailyRecord $record, string $body): Comment
    {
        return Comment::factory()->create([
            'user_id' => $commenter->id,
            'daily_record_id' => $record->id,
            'body' => $body,
        ]);
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
