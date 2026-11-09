<?php

namespace App\Mail;

use App\Models\DailyRecord;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Collection;

/**
 * 改善結果の入力期限が近い日報（6〜7日目で未入力）を、毎朝8時に知らせるメール。
 */
class ImprovementReminderMail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * @param  Collection<int, DailyRecord>  $dailyRecords  作成が古い順
     */
    public function __construct(
        public User $user,
        public Collection $dailyRecords,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '【Kaizen Log】改善結果の入力が'.$this->dailyRecords->count().'件残っています',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'mail.improvement-reminder',
            with: [
                'oldestRecord' => $this->dailyRecords->first(),
                'otherRecords' => $this->dailyRecords->slice(1),
            ],
        );
    }
}
