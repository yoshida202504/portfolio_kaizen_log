<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublishConfirmDialogTest extends TestCase
{
    use RefreshDatabase;

    private const MESSAGE = 'この日報を【公開用の日報】にします。他のユーザーが閲覧できるようになりますが、よろしいでしょうか？';

    public function test_create_page_has_the_publish_confirm_dialog(): void
    {
        $this->actingAs(User::factory()->create())
            ->get(route('records.create'))
            ->assertOk()
            ->assertSee('data-publish-confirm data-was-public="0"', false)
            ->assertSee(self::MESSAGE)
            ->assertSee('公開する</button>', false)
            ->assertSee('戻る</button>', false)
            ->assertSee('js/publish-confirm.js', false);
    }

    public function test_edit_page_tells_the_script_whether_the_record_is_already_public(): void
    {
        $user = User::factory()->create();
        $private = DailyRecord::factory()->for($user)->create(['is_public' => false]);
        $public = DailyRecord::factory()->for($user)->create(['is_public' => true]);

        $this->actingAs($user)
            ->get(route('records.edit', $private))
            ->assertOk()
            ->assertSee('data-was-public="0"', false)
            ->assertSee(self::MESSAGE);

        $this->actingAs($user)
            ->get(route('records.edit', $public))
            ->assertOk()
            ->assertSee('data-was-public="1"', false);
    }
}
