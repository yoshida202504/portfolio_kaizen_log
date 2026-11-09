<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// 毎朝8時に、改善結果の入力期限が近い日報のお知らせメールを送る（1日1回・二重に動かさない）
Schedule::command('kaizen:send-improvement-reminders')
    ->dailyAt('08:00')
    ->timezone(config('app.timezone'))
    ->withoutOverlapping();
