<?php

namespace Tests\Feature;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DailyRecordValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_cannot_create_a_daily_record_with_a_future_date(): void
    {
        $user = User::factory()->create();
        $data = $this->validDailyRecordData([
            'record_date' => now()->addYear()->toDateString(),
        ]);

        $response = $this->actingAs($user)->post(route('records.store'), $data);

        $response->assertSessionHasErrors('record_date');
        $this->assertDatabaseCount('daily_records', 0);
    }

    public function test_authenticated_user_cannot_create_a_daily_record_when_required_body_fields_are_empty(): void
    {
        $user = User::factory()->create();
        $data = $this->validDailyRecordData([
            'actions' => '',
            'good_points' => '',
            'improvement_points' => '',
            'improvement_strategy' => '',
        ]);

        $response = $this->actingAs($user)->post(route('records.store'), $data);

        $response->assertSessionHasErrors([
            'actions',
            'good_points',
            'improvement_points',
            'improvement_strategy',
        ]);
        $this->assertDatabaseCount('daily_records', 0);
    }

    public function test_authenticated_user_can_create_a_daily_record_with_a_body_field_of_exactly_1000_characters(): void
    {
        $user = User::factory()->create();
        $actions = str_repeat('a', 1000);
        $data = $this->validDailyRecordData(['actions' => $actions]);

        $response = $this->actingAs($user)->post(route('records.store'), $data);

        $response->assertRedirect(route('home'));
        $response->assertSessionDoesntHaveErrors('actions');
        $this->assertDatabaseHas('daily_records', [
            'user_id' => $user->id,
            'actions' => $actions,
        ]);
    }

    public function test_authenticated_user_cannot_create_a_daily_record_with_a_body_field_over_1000_characters(): void
    {
        $user = User::factory()->create();
        $data = $this->validDailyRecordData([
            'actions' => str_repeat('a', 1001),
        ]);

        $response = $this->actingAs($user)->post(route('records.store'), $data);

        $response->assertSessionHasErrors('actions');
        $this->assertDatabaseCount('daily_records', 0);
    }

    public function test_authenticated_user_can_create_a_third_daily_record_on_the_same_date(): void
    {
        $user = User::factory()->create();
        $recordDate = now()->subDay()->toDateString();
        DailyRecord::factory()->count(2)->for($user)->create(['record_date' => $recordDate]);

        $response = $this->actingAs($user)->post(route('records.store'), $this->validDailyRecordData([
            'record_date' => $recordDate,
        ]));

        $response->assertRedirect(route('home'));
        $response->assertSessionDoesntHaveErrors('record_date');
        $this->assertSame(3, $user->dailyRecords()->whereDate('record_date', $recordDate)->count());
    }

    public function test_authenticated_user_cannot_create_a_fourth_daily_record_on_the_same_date(): void
    {
        $user = User::factory()->create();
        $recordDate = now()->subDay()->toDateString();
        DailyRecord::factory()->count(3)->for($user)->create(['record_date' => $recordDate]);

        $response = $this->actingAs($user)->post(route('records.store'), $this->validDailyRecordData([
            'record_date' => $recordDate,
        ]));

        $response->assertSessionHasErrors('record_date');
        $this->assertSame(3, $user->dailyRecords()->whereDate('record_date', $recordDate)->count());
    }

    public function test_another_user_can_create_a_daily_record_on_a_date_where_the_first_user_already_has_three(): void
    {
        $firstUser = User::factory()->create();
        $secondUser = User::factory()->create();
        $recordDate = now()->subDay()->toDateString();
        DailyRecord::factory()->count(3)->for($firstUser)->create(['record_date' => $recordDate]);

        $response = $this->actingAs($secondUser)->post(route('records.store'), $this->validDailyRecordData([
            'record_date' => $recordDate,
        ]));

        $response->assertRedirect(route('home'));
        $response->assertSessionDoesntHaveErrors('record_date');
        $this->assertSame(1, $secondUser->dailyRecords()->whereDate('record_date', $recordDate)->count());
    }

    public function test_updating_an_existing_daily_record_does_not_count_it_as_an_extra_record_for_the_same_date(): void
    {
        $user = User::factory()->create();
        $recordDate = now()->subDay()->toDateString();
        $dailyRecord = DailyRecord::factory()->for($user)->create(['record_date' => $recordDate]);
        DailyRecord::factory()->count(2)->for($user)->create(['record_date' => $recordDate]);
        $data = $this->validDailyRecordData([
            'record_date' => $recordDate,
            'actions' => '同じ日付の3件目を編集した内容です。',
        ]);

        $response = $this->actingAs($user)->patch(route('records.update', $dailyRecord), $data);

        $response->assertRedirect(route('records.show', $dailyRecord));
        $response->assertSessionDoesntHaveErrors('record_date');
        $this->assertDatabaseHas('daily_records', [
            'id' => $dailyRecord->id,
            'actions' => $data['actions'],
        ]);
    }

    public function test_jpg_png_and_webp_images_are_accepted(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $extensions = ['jpg', 'png', 'webp'];

        foreach ($extensions as $index => $extension) {
            $data = $this->validDailyRecordData([
                'record_date' => now()->subDays($index + 1)->toDateString(),
                'image' => $this->fakeImageForExtension($extension),
            ]);

            $response = $this->actingAs($user)->post(route('records.store'), $data);

            $response->assertRedirect(route('home'));
            $response->assertSessionDoesntHaveErrors('image');
        }

        $this->assertSame(3, $user->dailyRecords()->count());

        foreach ($user->dailyRecords()->get() as $dailyRecord) {
            Storage::disk('public')->assertExists($dailyRecord->image_path);
        }
    }

    public function test_a_non_image_file_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $data = $this->validDailyRecordData([
            'image' => UploadedFile::fake()->create('daily-record.txt', 10, 'text/plain'),
        ]);

        $response = $this->actingAs($user)->post(route('records.store'), $data);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseCount('daily_records', 0);
    }

    public function test_an_image_larger_than_five_megabytes_is_rejected(): void
    {
        Storage::fake('public');
        $user = User::factory()->create();
        $data = $this->validDailyRecordData([
            'image' => UploadedFile::fake()->image('large-daily-record.jpg')->size(5121),
        ]);

        $response = $this->actingAs($user)->post(route('records.store'), $data);

        $response->assertSessionHasErrors('image');
        $this->assertDatabaseCount('daily_records', 0);
    }

    public function test_invalid_is_public_value_is_rejected(): void
    {
        $user = User::factory()->create();
        $data = $this->validDailyRecordData(['is_public' => 'invalid']);

        $response = $this->actingAs($user)->post(route('records.store'), $data);

        $response->assertSessionHasErrors('is_public');
        $this->assertDatabaseCount('daily_records', 0);
    }

    /**
     * @param  array<string, mixed>  $overrides
     * @return array<string, mixed>
     */
    private function validDailyRecordData(array $overrides = []): array
    {
        return array_merge([
            'record_date' => now()->subDay()->toDateString(),
            'actions' => 'テストで今日やったことです。',
            'good_points' => 'テストで良かったことです。',
            'improvement_points' => 'テストで改善点です。',
            'improvement_strategy' => 'テストで改善策です。',
            'is_public' => false,
        ], $overrides);
    }

    private function fakeImageForExtension(string $extension): UploadedFile
    {
        if ($extension === 'webp') {
            return UploadedFile::fake()->createWithContent(
                'daily-record.webp',
                base64_decode('UklGRiIAAABXRUJQVlA4IBYAAADQAQCdASoBAAEALmk0mk0iIiIiIhAAAD+/gbQAA3AA/v89WAAAAA=='),
            );
        }

        return UploadedFile::fake()->image("daily-record.{$extension}");
    }
}
