<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_is_redirected_to_login_for_profile_routes(): void
    {
        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->patch(route('profile.update'), [])->assertRedirect(route('login'));
        $this->delete(route('profile.destroy'), [])->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_open_their_profile_edit_page(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee($user->name)
            ->assertSee($user->email)
            ->assertSee('退会をご希望の方はこちら')
            ->assertSee(route('profile.withdraw'), false);
    }

    public function test_authenticated_user_can_update_their_profile_and_keep_their_own_name_and_email(): void
    {
        $user = User::factory()->create([
            'name' => '更新前ユーザー',
            'email' => 'before@example.com',
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => '更新後ユーザー',
                'email' => 'after@example.com',
            ])
            ->assertRedirect(route('mypage'));

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => '更新後ユーザー',
            'email' => 'after@example.com',
        ]);

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => '更新後ユーザー',
                'email' => 'after@example.com',
            ])
            ->assertSessionDoesntHaveErrors();
    }

    public function test_profile_update_rejects_another_users_name_or_email(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create([
            'name' => '重複ユーザー名',
            'email' => 'duplicate@example.com',
        ]);

        $this->actingAs($user)
            ->from(route('profile.edit'))
            ->patch(route('profile.update'), [
                'name' => $otherUser->name,
                'email' => $otherUser->email,
            ])
            ->assertRedirect(route('profile.edit'))
            ->assertSessionHasErrors(['name', 'email']);
    }

    public function test_profile_has_no_age_or_gender(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertDontSee('年齢')
            ->assertDontSee('性別');

        $this->actingAs($user)
            ->patch(route('profile.update'), [
                'name' => $user->name,
                'email' => $user->email,
                'age' => 100,
                'gender' => '不正な値',
            ])
            ->assertRedirect(route('mypage'))
            ->assertSessionDoesntHaveErrors();

        $this->assertFalse(Schema::hasColumn('users', 'age'));
        $this->assertFalse(Schema::hasColumn('users', 'gender'));
    }

    public function test_withdraw_page_shows_what_happens_and_the_agreement(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('profile.withdraw'))
            ->assertOk()
            ->assertSee('退会の確認')
            ->assertSee('退会すると、次のようになります')
            ->assertSee('上記を確認し、退会することに同意します。')
            ->assertSee(route('profile.destroy'), false);
    }

    public function test_visitor_cannot_open_the_withdraw_page(): void
    {
        $this->get(route('profile.withdraw'))->assertRedirect(route('login'));
    }

    public function test_account_withdrawal_requires_confirmation(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('profile.withdraw'))
            ->delete(route('profile.destroy'))
            ->assertRedirect(route('profile.withdraw'))
            ->assertSessionHasErrors('confirm_withdrawal');

        $this->assertDatabaseHas('users', ['id' => $user->id, 'deleted_at' => null]);
    }

    public function test_account_withdrawal_soft_deletes_user_logs_out_and_keeps_daily_records(): void
    {
        $user = User::factory()->create();
        $record = DailyRecord::factory()->for($user)->create(['is_public' => true]);

        $this->actingAs($user)
            ->delete(route('profile.destroy'), ['confirm_withdrawal' => '1'])
            ->assertRedirect('/');

        $this->assertGuest();
        $this->assertTrue(User::withTrashed()->findOrFail($user->id)->trashed());
        $this->assertDatabaseHas('daily_records', ['id' => $record->id, 'deleted_at' => null]);
    }

    public function test_public_daily_record_of_withdrawn_user_cannot_be_viewed_by_another_user(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $record = DailyRecord::factory()->for($owner)->create(['is_public' => true]);
        $owner->delete();

        $this->actingAs($viewer)
            ->get(route('records.show', $record))
            ->assertForbidden();
    }
}
