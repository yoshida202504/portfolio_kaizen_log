<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <title>改善結果の入力が残っています</title>
</head>
<body style="margin: 0; padding: 24px; color: #26384a; background: #f7f7f2; font-family: sans-serif; line-height: 1.7;">
    <div style="max-width: 560px; margin: 0 auto; padding: 24px; background: #ffffff; border-radius: 12px;">
        <p style="margin: 0 0 16px;">{{ $user->name }}さん、おはようございます。</p>
        <p style="margin: 0 0 20px;">
            改善結果の入力期限が近い日報が <strong>{{ $dailyRecords->count() }}件</strong> あります。<br>
            改善策をやってみた結果を、想定と比べてA〜Dで振り返りましょう。
        </p>

        <div style="margin: 0 0 20px; padding: 16px; background: #fff8e1; border: 2px solid #e0b100; border-radius: 10px;">
            <p style="margin: 0 0 8px; color: #8a5a00; font-weight: bold;">いちばん期限が近い日報（{{ $oldestRecord->improvementDeadline()->format('n/j') }} 23:59 まで）</p>
            <p style="margin: 0;">【改善策】{{ $oldestRecord->improvement_strategy }}</p>
            <p style="margin: 0 0 12px;">【想定する結果】{{ $oldestRecord->expected_result }}</p>
            <a href="{{ route('records.improvement.edit', $oldestRecord) }}" style="display: inline-block; padding: 8px 16px; color: #ffffff; background: #1767c8; border-radius: 6px; text-decoration: none;">改善結果を入力する</a>
        </div>

        @foreach ($otherRecords as $dailyRecord)
            <div style="margin: 0 0 12px; padding: 12px 16px; border: 1px solid #e3e9e5; border-radius: 10px;">
                <p style="margin: 0 0 4px; color: #71808a; font-size: 13px;">{{ $dailyRecord->improvementDeadline()->format('n/j') }} 23:59 まで</p>
                <p style="margin: 0;">【改善策】{{ $dailyRecord->improvement_strategy }}</p>
                <p style="margin: 0 0 8px;">【想定する結果】{{ $dailyRecord->expected_result }}</p>
                <a href="{{ route('records.improvement.edit', $dailyRecord) }}" style="color: #1767c8;">改善結果を入力する</a>
            </div>
        @endforeach

        <p style="margin: 24px 0 0; color: #71808a; font-size: 13px;">このメールは、改善結果がまだ入力されていない日報（作成から6〜7日目）がある方にだけ、毎朝8時にお送りしています。</p>
    </div>
</body>
</html>
