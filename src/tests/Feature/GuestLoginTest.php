<?php

namespace Tests\Feature;

use App\Models\Comment;
use App\Models\DailyRecord;
use App\Models\Follow;
use App\Models\ImprovementRecord;
use App\Models\Like;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestLoginTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_page_shows_the_guest_login_button(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('ゲストとして試す')
            ->assertSee(route('login.guest'), false);
    }

    public function test_guest_can_log_in_with_the_guest_login_button(): void
    {
        $this->seed(DemoSeeder::class);

        $this->post(route('login.guest'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('success');

        $this->assertAuthenticatedAs(User::query()->where('email', User::GUEST_EMAIL)->firstOrFail());
    }

    public function test_guest_login_resets_the_guest_data_to_the_demo_state(): void
    {
        $this->seed(DemoSeeder::class);
        $guest = User::query()->where('email', User::GUEST_EMAIL)->firstOrFail();
        $sato = User::query()->where('name', '佐藤 美咲')->firstOrFail();
        // Follows are left out: the guest's follows are always removed on login.
        $counts = array_diff_key($this->tableCounts(), ['follows' => 0]);
        $guestRecordActions = $guest->dailyRecords()->orderBy('id')->pluck('actions')->all();

        // A previous reviewer changes the guest data.
        $added = DailyRecord::factory()->for($guest)->create(['actions' => '前のゲストが書いた日報']);
        $guest->dailyRecords()->where('id', '!=', $added->id)->first()->delete();
        $satoRecord = $sato->dailyRecords()->where('is_public', true)->firstOrFail();
        Like::query()->where('user_id', $guest->id)->delete();
        Comment::factory()->create(['user_id' => $guest->id, 'daily_record_id' => $satoRecord->id, 'body' => '前のゲストのコメント']);

        $this->post(route('login.guest'))->assertRedirect(route('home'));

        $this->assertSame($counts, array_diff_key($this->tableCounts(), ['follows' => 0]));
        $this->assertSame($guestRecordActions, $guest->dailyRecords()->orderBy('id')->pluck('actions')->all());
        $this->assertDatabaseMissing('daily_records', ['actions' => '前のゲストが書いた日報']);
        $this->assertDatabaseMissing('comments', ['body' => '前のゲストのコメント']);
        $this->assertGreaterThan(0, Like::query()->where('user_id', $guest->id)->count());
        $this->assertSame(0, Follow::query()->where('follower_id', $guest->id)->orWhere('followed_id', $guest->id)->count());
    }

    public function test_guest_login_does_not_change_other_users_data(): void
    {
        $this->seed(DemoSeeder::class);
        $others = User::query()->where('email', '!=', User::GUEST_EMAIL)->pluck('id');
        $before = DailyRecord::withTrashed()->whereIn('user_id', $others)->orderBy('id')->get(['id', 'actions', 'is_public'])->toArray();

        $this->post(route('login.guest'));

        $this->assertSame($before, DailyRecord::withTrashed()->whereIn('user_id', $others)->orderBy('id')->get(['id', 'actions', 'is_public'])->toArray());
    }

    public function test_guest_login_explains_how_to_create_the_guest_account_when_it_is_missing(): void
    {
        $this->from(route('login'))
            ->post(route('login.guest'))
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('guest');

        $this->assertGuest();
    }

    public function test_guest_cannot_update_the_profile_or_withdraw(): void
    {
        $this->seed(DemoSeeder::class);
        $guest = User::query()->where('email', User::GUEST_EMAIL)->firstOrFail();

        $this->actingAs($guest)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('ゲストユーザーはプロフィールの変更と退会ができません。');

        $this->actingAs($guest)
            ->patch(route('profile.update'), [
                'name' => '書き換えた名前',
                'email' => 'changed@example.com',
            ])
            ->assertForbidden();

        $this->actingAs($guest)
            ->delete(route('profile.destroy'), ['confirm_withdrawal' => '1'])
            ->assertForbidden();

        $this->assertDatabaseHas('users', ['id' => $guest->id, 'name' => 'ゲストユーザー', 'deleted_at' => null]);
    }

    public function test_regular_users_are_not_treated_as_guests(): void
    {
        $this->assertFalse(User::factory()->create()->isGuest());
    }

    public function test_demo_seeder_creates_japanese_demo_data_with_every_evaluation(): void
    {
        $this->seed(DemoSeeder::class);

        $this->assertSame(4, User::query()->count());
        $guest = User::query()->where('email', User::GUEST_EMAIL)->firstOrFail();
        $this->assertGreaterThan(30, $guest->dailyRecords()->count());

        $evaluations = ImprovementRecord::query()->get()->map->evaluation->unique()->sort()->values()->all();
        $this->assertSame(['A', 'B', 'C', 'D'], $evaluations);
        $this->assertTrue($guest->isMutuallyFollowing(User::query()->where('name', '佐藤 美咲')->firstOrFail()));
        $this->assertGreaterThan(0, Like::query()->count());
        $this->assertGreaterThan(0, Comment::query()->count());
        $this->assertSame(0, ImprovementRecord::query()->whereDate('executed_at', '>', now()->toDateString())->count());
    }

    public function test_demo_seeder_can_run_twice_without_duplicating_data(): void
    {
        $this->seed(DemoSeeder::class);
        $counts = $this->tableCounts();

        $this->seed(DemoSeeder::class);

        $this->assertSame($counts, $this->tableCounts());
    }

    /**
     * @return array<string, int>
     */
    private function tableCounts(): array
    {
        return [
            'users' => User::query()->count(),
            'daily_records' => DailyRecord::query()->count(),
            'improvement_records' => ImprovementRecord::query()->count(),
            'follows' => Follow::query()->count(),
            'likes' => Like::query()->count(),
            'comments' => Comment::query()->count(),
        ];
    }
}
