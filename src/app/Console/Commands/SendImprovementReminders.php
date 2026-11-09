<?php

namespace App\Console\Commands;

use App\Mail\ImprovementReminderMail;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

/**
 * 改善結果の入力期限が近い日報（6〜7日目で未入力）があるユーザーにメールを送る。
 * routes/console.php で毎朝8時に1回動かす。ゲストは実在しないアドレスなので送らない。
 */
class SendImprovementReminders extends Command
{
    protected $signature = 'kaizen:send-improvement-reminders';

    protected $description = '改善結果の入力期限が近い日報があるユーザーに、お知らせメールを送る';

    public function handle(): int
    {
        $sentCount = 0;

        User::query()
            ->where('email', '!=', User::GUEST_EMAIL)
            ->whereHas('dailyRecords', fn ($query) => $query->improvementReflectionDue())
            ->orderBy('id')
            ->each(function (User $user) use (&$sentCount): void {
                $dailyRecords = $user->dailyRecords()
                    ->improvementReflectionDue()
                    ->oldest('created_at')
                    ->get();

                Mail::to($user)->send(new ImprovementReminderMail($user, $dailyRecords));
                $sentCount++;
            });

        $this->info("お知らせメールを {$sentCount} 人に送りました。");

        return self::SUCCESS;
    }
}
