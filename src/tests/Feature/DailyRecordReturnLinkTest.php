<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class DailyRecordReturnLinkTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_second_page_detail_returns_to_the_original_card(): void
    {
        $user = User::factory()->create();
        $records = $this->createRecords($user, '自分の日報', 11);
        $record = $records[10];

        $listResponse = $this->actingAs($user)->get(route('home', ['page' => 2]));
        $detailUrl = route('records.show', [
            'record' => $record,
            'source' => 'home',
            'page' => 2,
        ]);

        $listResponse
            ->assertOk()
            ->assertSee('id="record-'.$record->id.'"', false)
            ->assertSee($detailUrl);

        $this->actingAs($user)
            ->get($detailUrl)
            ->assertOk()
            ->assertSee(route('home', ['page' => 2]).'#record-'.$record->id)
            ->assertSee('日報一覧へ戻る');
    }

    public function test_community_first_and_second_page_details_return_to_the_original_card(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $records = $this->createRecords($owner, 'Community日報', 11, ['is_public' => true]);

        foreach ([1 => $records[0], 2 => $records[10]] as $page => $record) {
            $detailUrl = route('records.show', [
                'record' => $record,
                'source' => 'community',
                'page' => $page,
            ]);

            $this->actingAs($viewer)
                ->get(route('community.index', ['page' => $page]))
                ->assertOk()
                ->assertSee('id="record-'.$record->id.'"', false)
                ->assertSee($detailUrl);

            $this->actingAs($viewer)
                ->get($detailUrl)
                ->assertOk()
                ->assertSee(route('community.index', ['page' => $page]).'#record-'.$record->id)
                ->assertSee('Communityへ戻る');
        }
    }

    public function test_search_detail_preserves_filters_page_and_card_position(): void
    {
        $user = User::factory()->create();
        $record = $this->createRecords($user, 'Laravel 検索日報', 11, [
            'record_date' => '2026-09-20',
        ])[10];
        $filters = [
            'page' => 2,
            'record_date' => '2026-09-20',
            'keyword' => 'Laravel',
        ];
        $detailUrl = route('records.show', [
            'record' => $record,
            'source' => 'search',
            ...$filters,
        ]);

        $this->actingAs($user)
            ->get(route('records.search', $filters))
            ->assertOk()
            ->assertSee('id="record-'.$record->id.'"', false)
            ->assertSee($detailUrl);

        $this->actingAs($user)
            ->get($detailUrl)
            ->assertOk()
            ->assertSee(route('records.search', $filters).'#record-'.$record->id)
            ->assertSee('検索結果へ戻る');
    }

    public function test_user_detail_returns_to_the_original_card_for_the_record_owner_only(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $record = $this->createRecords($owner, 'ユーザー詳細日報', 11, ['is_public' => true])[10];
        $detailUrl = route('records.show', [
            'record' => $record,
            'source' => 'user',
            'user' => $owner->id,
            'page' => 2,
        ]);

        $this->actingAs($viewer)
            ->get(route('users.show', [$owner, 'page' => 2]))
            ->assertOk()
            ->assertSee('id="record-'.$record->id.'"', false)
            ->assertSee($detailUrl);

        $this->actingAs($viewer)
            ->get($detailUrl)
            ->assertOk()
            ->assertSee(route('users.show', [$owner, 'page' => 2]).'#record-'.$record->id)
            ->assertSee('ユーザー詳細へ戻る');
    }

    public function test_direct_or_invalid_source_falls_back_to_the_home_list(): void
    {
        $user = User::factory()->create();
        $record = DailyRecord::factory()->for($user)->create();

        $this->actingAs($user)
            ->get(route('records.show', $record))
            ->assertOk()
            ->assertSee(route('home'))
            ->assertSee('日報一覧へ戻る');

        $this->actingAs($user)
            ->get(route('records.show', [
                'record' => $record,
                'source' => 'https://example.com',
                'page' => 999,
            ]))
            ->assertOk()
            ->assertSee(route('home'))
            ->assertDontSee('example.com');
    }

    public function test_non_mutual_follower_cannot_bypass_private_record_authorization_with_a_return_source(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $record = DailyRecord::factory()->for($owner)->create(['is_public' => false]);

        $this->actingAs($viewer)
            ->get(route('records.show', [
                'record' => $record,
                'source' => 'community',
                'page' => 2,
            ]))
            ->assertForbidden();
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
