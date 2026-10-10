<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class UiPolishTest extends TestCase
{
    use RefreshDatabase;

    private const HARMFUL_ERROR = 'このコメントは、ユーザーを傷つける可能性があるため投稿できません。';

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_new_comment_error_is_not_shown_on_existing_comment_edit_forms(): void
    {
        $commenter = User::factory()->create();
        $record = DailyRecord::factory()->for(User::factory())->create(['is_public' => true]);
        $comment = Comment::factory()->for($commenter)->for($record, 'dailyRecord')->create(['body' => '既存のコメントです。']);

        $response = $this->actingAs($commenter)
            ->from(route('records.show', $record))
            ->followingRedirects()
            ->post(route('records.comments.store', $record), ['comment_form' => 'new', 'body' => '死ね']);

        $response->assertOk()
            ->assertSee('>既存のコメントです。</textarea>', false)
            ->assertDontSee('id="comment-body-error-'.$comment->id.'"', false)
            ->assertSee('id="comment-body-error"', false);
        $this->assertSame(1, substr_count($response->getContent(), self::HARMFUL_ERROR));
    }

    public function test_comment_update_error_is_shown_only_on_the_edited_comment(): void
    {
        $commenter = User::factory()->create();
        $record = DailyRecord::factory()->for(User::factory())->create(['is_public' => true]);
        $editedComment = Comment::factory()->for($commenter)->for($record, 'dailyRecord')->create(['body' => '編集するコメント']);
        $otherComment = Comment::factory()->for($commenter)->for($record, 'dailyRecord')->create(['body' => '別のコメント']);

        $response = $this->actingAs($commenter)
            ->from(route('records.show', $record))
            ->followingRedirects()
            ->patch(route('comments.update', $editedComment), ['comment_form' => (string) $editedComment->id, 'body' => '死ね']);

        $response->assertOk()
            ->assertSee('id="comment-body-error-'.$editedComment->id.'"', false)
            ->assertDontSee('id="comment-body-error-'.$otherComment->id.'"', false)
            ->assertSee('>別のコメント</textarea>', false)
            ->assertDontSee('id="comment-body-error"', false);
        $this->assertSame(1, substr_count($response->getContent(), self::HARMFUL_ERROR));
    }

    public function test_home_shows_the_number_of_daily_records_in_the_current_month(): void
    {
        Carbon::setTestNow(Carbon::create(2026, 9, 27, 12, 0, 0, 'Asia/Tokyo'));
        $user = User::factory()->create();
        DailyRecord::factory()->for($user)->count(2)->create(['record_date' => '2026-09-10']);
        DailyRecord::factory()->for($user)->create(['record_date' => '2026-08-31']);
        DailyRecord::factory()->for(User::factory())->create(['record_date' => '2026-09-10']);

        $this->actingAs($user)
            ->get(route('home'))
            ->assertOk()
            ->assertSee('今月の日報')
            ->assertSee('<strong>2件</strong>', false)
            ->assertDontSee('今月の目標')
            ->assertSee(route('improvement-records.index'), false);
    }

    public function test_not_found_page_is_rendered_in_japanese(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('records.show', 999999))
            ->assertNotFound()
            ->assertSee('ページが見つかりません')
            ->assertSee('自分の日報一覧へ戻る');
    }

    public function test_forbidden_page_shows_the_reason_passed_to_abort(): void
    {
        $user = User::factory()->create();
        $record = DailyRecord::factory()->for($user)->create();
        $record->forceFill(['created_at' => now()->subDays(10)])->save();

        $this->actingAs($user)
            ->get(route('records.improvement.edit', $record))
            ->assertForbidden()
            ->assertSee('アクセスできません')
            ->assertSee('改善結果の入力期限を過ぎています。');
    }

    public function test_forbidden_page_explains_private_record_access(): void
    {
        $record = DailyRecord::factory()->for(User::factory())->create(['is_public' => false]);

        $this->actingAs(User::factory()->create())
            ->get(route('records.show', $record))
            ->assertForbidden()
            ->assertSee('相互フォローしているユーザーだけが閲覧できます');
    }
}
