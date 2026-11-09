<?php

namespace Tests\Feature;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GuestFollowRestrictionTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_cannot_follow_another_user(): void
    {
        $guest = $this->guest();
        $user = User::factory()->create();

        $this->actingAs($guest)
            ->post(route('users.follow.store', $user))
            ->assertForbidden()
            ->assertSee('ゲストユーザーはフォローできません。');

        $this->assertSame(0, Follow::query()->count());
    }

    public function test_user_cannot_follow_the_guest(): void
    {
        $guest = $this->guest();
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('users.follow.store', $guest))
            ->assertForbidden()
            ->assertSee('ゲストユーザーはフォローできません。');

        $this->assertSame(0, Follow::query()->count());
    }

    public function test_follow_button_is_hidden_when_the_guest_is_involved(): void
    {
        $guest = $this->guest();
        $user = User::factory()->create();

        $this->actingAs($guest)
            ->get(route('users.show', $user))
            ->assertOk()
            ->assertDontSee('フォローする</button>', false)
            ->assertSee('ゲストユーザーはフォローできません。');

        $this->actingAs($user)
            ->get(route('users.show', $guest))
            ->assertOk()
            ->assertDontSee('フォローする</button>', false)
            ->assertSee('ゲストユーザーはフォローできません。');
    }

    public function test_regular_users_can_still_follow_each_other(): void
    {
        $user = User::factory()->create();
        $other = User::factory()->create();

        $this->actingAs($user)
            ->get(route('users.show', $other))
            ->assertSee('フォローする</button>', false);

        $this->actingAs($user)
            ->post(route('users.follow.store', $other))
            ->assertRedirect(route('users.show', $other));

        $this->assertTrue($user->isFollowing($other));
    }

    public function test_guest_my_page_shows_only_the_liked_records_tab(): void
    {
        $this->actingAs($this->guest())
            ->get(route('mypage'))
            ->assertOk()
            ->assertSee('いいねの日報')
            ->assertDontSee('id="following-tab"', false)
            ->assertDontSee('id="followers-tab"', false);
    }

    private function guest(): User
    {
        return User::factory()->create([
            'name' => 'ゲストユーザー',
            'email' => User::GUEST_EMAIL,
        ]);
    }
}
