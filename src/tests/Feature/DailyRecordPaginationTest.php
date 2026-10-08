<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DailyRecordPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_paginates_only_the_authenticated_users_daily_records_in_latest_order(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $records = $this->createRecords($user, '自分の日報', 11);
        $otherRecord = $this->createRecords($otherUser, '他ユーザーの日報', 1)[0];

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertViewHas('dailyRecords', fn ($dailyRecords) => $dailyRecords->count() === 10 && $dailyRecords->total() === 11);
        $response->assertSee($records[0]->actions);
        $response->assertDontSee($records[10]->actions);
        $response->assertDontSee($otherRecord->actions);

        $this->actingAs($user)
            ->get(route('home', ['page' => 2]))
            ->assertOk()
            ->assertSee($records[10]->actions)
            ->assertDontSee($records[0]->actions);
    }

    public function test_search_paginates_results_and_keeps_date_and_keyword_on_the_next_page(): void
    {
        $user = User::factory()->create();
        $records = $this->createRecords($user, 'Laravel 検索日報', 11, [
            'record_date' => '2026-09-20',
        ]);
        $filters = [
            'record_date' => '2026-09-20',
            'keyword' => 'Laravel',
        ];

        $response = $this->actingAs($user)->get(route('records.search', $filters));

        $response->assertOk();
        $response->assertViewHas('dailyRecords', fn ($dailyRecords) => $dailyRecords->count() === 10 && $dailyRecords->total() === 11);
        $response->assertSee($records[0]->actions);
        $response->assertDontSee($records[10]->actions);

        $nextPageUrl = $response->viewData('dailyRecords')->nextPageUrl();
        $this->assertNotNull($nextPageUrl);
        $this->assertStringContainsString('record_date=2026-09-20', $nextPageUrl);
        $this->assertStringContainsString('keyword=Laravel', $nextPageUrl);

        $this->actingAs($user)
            ->get(route('records.search', [...$filters, 'page' => 2]))
            ->assertOk()
            ->assertSee($records[10]->actions)
            ->assertDontSee($records[0]->actions);
    }

    public function test_community_paginates_only_visible_daily_records(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $privateOwner = User::factory()->create();
        $publicRecords = $this->createRecords($owner, 'Community 公開日報', 11, [
            'is_public' => true,
        ]);
        $ownRecord = $this->createRecords($viewer, '自分のCommunity日報', 1, [
            'is_public' => true,
        ])[0];
        $privateRecord = $this->createRecords($privateOwner, '非公開Community日報', 1, [
            'is_public' => false,
        ])[0];

        $response = $this->actingAs($viewer)->get(route('community.index'));

        $response->assertOk();
        $response->assertViewHas('dailyRecords', fn ($dailyRecords) => $dailyRecords->count() === 10 && $dailyRecords->total() === 11);
        $response->assertSee($publicRecords[0]->actions);
        $response->assertDontSee($publicRecords[10]->actions);
        $response->assertDontSee($ownRecord->actions);
        $response->assertDontSee($privateRecord->actions);

        $this->actingAs($viewer)
            ->get(route('community.index', ['page' => 2]))
            ->assertOk()
            ->assertSee($publicRecords[10]->actions)
            ->assertDontSee($ownRecord->actions)
            ->assertDontSee($privateRecord->actions);
    }

    public function test_user_detail_paginates_only_daily_records_visible_to_the_viewer(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $publicRecords = $this->createRecords($owner, 'ユーザー詳細公開日報', 11, [
            'is_public' => true,
        ]);
        $privateRecord = $this->createRecords($owner, 'ユーザー詳細非公開日報', 1, [
            'is_public' => false,
        ])[0];

        $response = $this->actingAs($viewer)->get(route('users.show', $owner));

        $response->assertOk();
        $response->assertViewHas('dailyRecords', fn ($dailyRecords) => $dailyRecords->count() === 10 && $dailyRecords->total() === 11);
        $response->assertSee($publicRecords[0]->actions);
        $response->assertDontSee($publicRecords[10]->actions);
        $response->assertDontSee($privateRecord->actions);

        $this->actingAs($viewer)
            ->get(route('users.show', [$owner, 'page' => 2]))
            ->assertOk()
            ->assertSee($publicRecords[10]->actions)
            ->assertDontSee($privateRecord->actions);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<int, DailyRecord>
     */
    private function createRecords(User $user, string $prefix, int $count, array $overrides = []): array
    {
        return collect(range(1, $count))
            ->map(function (int $number) use ($user, $prefix, $overrides): DailyRecord {
                $createdAt = Carbon::parse('2026-09-25 12:00:00')->subMinutes($number - 1);

                return DailyRecord::factory()
                    ->for($user)
                    ->create(array_merge([
                        'record_date' => Carbon::parse('2026-09-25')->subDays($number - 1)->toDateString(),
                        'actions' => sprintf('%s %02d', $prefix, $number),
                        'good_points' => '良かったことです。',
                        'improvement_points' => '改善点です。',
                        'improvement_strategy' => '改善策です。',
                        'created_at' => $createdAt,
                        'updated_at' => $createdAt,
                    ], $overrides));
            })
            ->all();
    }
}
