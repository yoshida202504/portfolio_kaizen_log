<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CodeCleanupTest extends TestCase
{
    use RefreshDatabase;

    public function test_keyword_search_treats_percent_and_underscore_as_literal_characters(): void
    {
        $user = User::factory()->create();
        DailyRecord::factory()->for($user)->create(['actions' => '達成率100%を目指した']);
        DailyRecord::factory()->for($user)->create(['actions' => 'snake_caseに統一した']);
        DailyRecord::factory()->for($user)->create([
            'actions' => '普通の日報',
            'good_points' => '特になし',
            'improvement_points' => '特になし',
            'improvement_strategy' => '特になし',
        ]);

        $percentResults = $this->actingAs($user)
            ->get(route('records.search', ['keyword' => '%']))
            ->assertOk()
            ->viewData('dailyRecords');
        $this->assertSame(['達成率100%を目指した'], $percentResults->pluck('actions')->all());

        $underscoreResults = $this->actingAs($user)
            ->get(route('records.search', ['keyword' => '_']))
            ->assertOk()
            ->viewData('dailyRecords');
        $this->assertSame(['snake_caseに統一した'], $underscoreResults->pluck('actions')->all());
    }

    public function test_keyword_search_matches_any_body_field(): void
    {
        $user = User::factory()->create();
        DailyRecord::factory()->for($user)->create(['improvement_strategy' => '朝にタスクを3つ書き出す']);
        DailyRecord::factory()->for($user)->create(['improvement_strategy' => '別の改善策']);

        $results = $this->actingAs($user)
            ->get(route('records.search', ['keyword' => 'タスク']))
            ->assertOk()
            ->viewData('dailyRecords');

        $this->assertSame(['朝にタスクを3つ書き出す'], $results->pluck('improvement_strategy')->all());
    }

    public function test_my_page_paginates_liked_daily_records(): void
    {
        $user = User::factory()->create();
        $owner = User::factory()->create();
        DailyRecord::factory()->for($owner)->count(11)->create(['is_public' => true])
            ->each(fn (DailyRecord $record) => Like::factory()->create([
                'user_id' => $user->id,
                'daily_record_id' => $record->id,
            ]));

        $firstPage = $this->actingAs($user)->get(route('mypage'))->assertOk();
        $this->assertCount(10, $firstPage->viewData('likedRecords'));
        $firstPage->assertSee('liked_page=2#liked-records-panel', false);

        $secondPage = $this->actingAs($user)->get(route('mypage', ['liked_page' => 2]))->assertOk();
        $this->assertCount(1, $secondPage->viewData('likedRecords'));
    }

    public function test_pages_declare_japanese_as_the_document_language(): void
    {
        $this->get(route('login'))
            ->assertOk()
            ->assertSee('<html lang="ja">', false);
    }
}
