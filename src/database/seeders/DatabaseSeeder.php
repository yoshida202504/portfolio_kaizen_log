<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\DailyRecord;
use App\Models\Follow;
use App\Models\Like;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $users = User::factory(5)->create();

        $dailyRecords = $users->flatMap(fn (User $user) => DailyRecord::factory(2)->create([
            'user_id' => $user->id,
        ]));

        foreach ($dailyRecords as $dailyRecord) {
            $otherUser = $users->first(
                fn (User $user) => $user->id != $dailyRecord->user_id,
            );

            Comment::factory()->create([
                'user_id' => $otherUser->id,
                'daily_record_id' => $dailyRecord->id,
            ]);

            Like::factory()->create([
                'user_id' => $otherUser->id,
                'daily_record_id' => $dailyRecord->id,
            ]);
        }

        foreach ($users as $index => $user) {
            Follow::factory()->create([
                'follower_id' => $user->id,
                'followed_id' => $users[($index + 1) % $users->count()]->id,
            ]);
        }
    }
}
