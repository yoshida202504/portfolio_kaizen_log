<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TopPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_visitor_sees_the_top_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('振り返りを習慣化できる日報アプリ')
            ->assertSee(route('register'), false)
            ->assertSee(route('login'), false)
            ->assertSee(route('login.guest'), false)
            ->assertSee('ゲストとして試す')
            ->assertSee('朝8時の通知')
            ->assertSee('想定と比べて評価')
            ->assertSee('改善記録');
    }

    public function test_logged_in_user_is_sent_from_the_top_page_to_home(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/')
            ->assertRedirect(route('home'));
    }

    public function test_home_is_at_home_path(): void
    {
        $this->assertSame(url('/home'), route('home'));

        $this->actingAs(User::factory()->create())
            ->get('/home')
            ->assertOk();
    }

    public function test_logout_goes_to_the_top_page(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('logout'))
            ->assertRedirect(route('top'));

        $this->assertGuest();
        $this->get(route('top'))->assertOk();
    }
}
