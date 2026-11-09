<?php

namespace Tests\Feature;

use App\Models\Follow;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDetailTabsTest extends TestCase
{
    use RefreshDatabase;

    public function test_mutual_followers_can_open_the_following_and_followers_tabs(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $followedByOwner = User::factory()->create(['name' => 'オーナーがフォローしている人']);
        $ownerFollower = User::factory()->create(['name' => 'オーナーのフォロワー']);
        $this->follow($viewer, $owner);
        $this->follow($owner, $viewer);
        $this->follow($owner, $followedByOwner);
        $this->follow($ownerFollower, $owner);

        $this->actingAs($viewer)
            ->get(route('users.show', $owner))
            ->assertOk()
            ->assertSee('※相互フォローしていない場合、非公開の日報・フォロー中・フォロワーの一覧は見られません')
            ->assertSee('id="user-following-tab"', false)
            ->assertSee('id="user-followers-tab"', false)
            ->assertDontSee('aria-disabled="true">フォロー中', false)
            ->assertSee('オーナーがフォローしている人')
            ->assertSee('オーナーのフォロワー');
    }

    public function test_tabs_are_shown_but_cannot_be_opened_without_mutual_following(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $followedByOwner = User::factory()->create(['name' => 'オーナーがフォローしている人']);
        $this->follow($viewer, $owner);
        $this->follow($owner, $followedByOwner);

        $this->actingAs($viewer)
            ->get(route('users.show', $owner))
            ->assertOk()
            ->assertSee('※相互フォローしていない場合、非公開の日報・フォロー中・フォロワーの一覧は見られません')
            ->assertSee('aria-disabled="true">フォロー中', false)
            ->assertSee('aria-disabled="true">フォロワー', false)
            ->assertDontSee('id="user-following-tab"', false)
            ->assertDontSee('オーナーがフォローしている人');
    }

    public function test_own_user_detail_does_not_show_the_tabs(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('users.show', $user))
            ->assertOk()
            ->assertDontSee('id="user-records-tab"', false)
            ->assertDontSee('※相互フォローしていない場合');
    }

    private function follow(User $follower, User $followed): void
    {
        Follow::factory()->create([
            'follower_id' => $follower->id,
            'followed_id' => $followed->id,
        ]);
    }
}
