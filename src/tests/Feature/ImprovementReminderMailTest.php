<?php

namespace Tests\Feature;

use App\Mail\ImprovementReminderMail;
use App\Models\DailyRecord;
use App\Models\ImprovementRecord;
use App\Models\User;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ImprovementReminderMailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::create(2026, 9, 27, 8, 0, 0, 'Asia/Tokyo'));
        Mail::fake();
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();

        parent::tearDown();
    }

    public function test_mail_is_sent_only_to_users_with_records_on_day_six_or_seven_without_a_result(): void
    {
        $dueUser = User::factory()->create();
        $dayEarly = User::factory()->create();
        $expired = User::factory()->create();
        $reflected = User::factory()->create();
        $guest = User::factory()->create(['email' => User::GUEST_EMAIL]);

        $this->recordCreatedDaysAgo($dueUser, 5);
        $this->recordCreatedDaysAgo($dueUser, 6);
        $this->recordCreatedDaysAgo($dayEarly, 4);
        $this->recordCreatedDaysAgo($expired, 7);
        $done = $this->recordCreatedDaysAgo($reflected, 5);
        ImprovementRecord::factory()->for($done, 'dailyRecord')->create();
        $this->recordCreatedDaysAgo($guest, 5);

        $this->artisan('kaizen:send-improvement-reminders')->assertSuccessful();

        Mail::assertSent(ImprovementReminderMail::class, 1);
        Mail::assertSent(ImprovementReminderMail::class, fn (ImprovementReminderMail $mail) => $mail->hasTo($dueUser->email)
            && $mail->dailyRecords->count() === 2);
    }

    public function test_mail_shows_count_strategy_expected_result_and_highlights_the_oldest(): void
    {
        $user = User::factory()->create(['name' => '振り返る人']);
        $oldest = $this->recordCreatedDaysAgo($user, 6, ['improvement_strategy' => '古い方の改善策', 'expected_result' => '古い方の想定']);
        $newer = $this->recordCreatedDaysAgo($user, 5, ['improvement_strategy' => '新しい方の改善策', 'expected_result' => '新しい方の想定']);

        $mail = new ImprovementReminderMail($user, collect([$oldest, $newer]));

        $mail->assertHasSubject('【Kaizen Log】改善結果の入力が2件残っています');
        $mail->assertSeeInOrderInHtml([
            '振り返る人さん',
            '2件',
            'いちばん期限が近い日報（9/27 23:59 まで）',
            '【改善策】古い方の改善策',
            '【想定する結果】古い方の想定',
            '【改善策】新しい方の改善策',
            '【想定する結果】新しい方の想定',
        ]);
        $mail->assertSeeInHtml(route('records.improvement.edit', $oldest), false);
    }

    public function test_command_is_scheduled_every_day_at_eight(): void
    {
        $event = collect(app(Schedule::class)->events())
            ->first(fn ($event) => str_contains($event->command, 'kaizen:send-improvement-reminders'));

        $this->assertNotNull($event);
        $this->assertSame('0 8 * * *', $event->expression);
        $this->assertSame('Asia/Tokyo', $event->timezone);
        $this->assertTrue($event->withoutOverlapping);
    }

    private function recordCreatedDaysAgo(User $user, int $days, array $attributes = []): DailyRecord
    {
        $createdAt = now()->subDays($days)->setTime(21, 0);

        return DailyRecord::factory()->for($user)->create(array_merge([
            'record_date' => $createdAt->toDateString(),
            'created_at' => $createdAt,
            'updated_at' => $createdAt,
        ], $attributes));
    }
}
