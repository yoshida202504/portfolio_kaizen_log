<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\Like;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LikeDisplayTest extends TestCase
{
    use RefreshDatabase;

    public function test_daily_record_detail_distinguishes_liked_and_unliked_states_without_like_controls_for_the_owner(): void
    {
        $owner = User::factory()->create();
        $viewer = User::factory()->create();
        $record = DailyRecord::factory()->for($owner)->create([
            'record_date' => '2026-09-20',
            'is_public' => true,
        ]);
        Like::factory()->create(['user_id' => User::factory()->create()->id, 'daily_record_id' => $record->id]);
        Like::factory()->create(['user_id' => User::factory()->create()->id, 'daily_record_id' => $record->id]);
        Like::factory()->create(['user_id' => User::factory()->create()->id, 'daily_record_id' => $record->id]);

        $this->actingAs($viewer)
            ->get(route('records.show', $record))
            ->assertOk()
            ->assertSee('aria-label="いいねする 現在3件"', false)
            ->assertSee('♡', false);

        Like::factory()->create(['user_id' => $viewer->id, 'daily_record_id' => $record->id]);

        $this->actingAs($viewer)
            ->get(route('records.show', $record))
            ->assertOk()
            ->assertSee('aria-label="いいねを解除する 現在4件"', false)
            ->assertSee('♥', false);

        $this->actingAs($owner)
            ->get(route('records.show', $record))
            ->assertOk()
            ->assertDontSee('aria-label="いいねする', false)
            ->assertDontSee('aria-label="いいねを解除する', false)
            ->assertSee('aria-label="いいね 4件"', false);
    }

    public function test_community_and_user_detail_display_like_counts_without_per_record_queries(): void
    {
        $viewer = User::factory()->create();
        $owner = User::factory()->create();
        $record = DailyRecord::factory()->for($owner)->create([
            'record_date' => '2026-09-20',
            'is_public' => true,
        ]);
        Like::factory()->create(['user_id' => User::factory()->create()->id, 'daily_record_id' => $record->id]);
        Like::factory()->create(['user_id' => User::factory()->create()->id, 'daily_record_id' => $record->id]);
        Like::factory()->create(['user_id' => User::factory()->create()->id, 'daily_record_id' => $record->id]);

        $this->actingAs($viewer)
            ->get(route('community.index'))
            ->assertOk()
            ->assertSee('aria-label="いいね 3件"', false);

        $this->actingAs($viewer)
            ->get(route('users.show', $owner))
            ->assertOk()
            ->assertSee('aria-label="いいね 3件"', false);
    }
}
