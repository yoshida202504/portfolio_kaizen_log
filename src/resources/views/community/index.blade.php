<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>他のユーザーの日報</title>
        <style>
            body { max-width: 720px; margin: 40px auto; padding: 0 16px; font-family: sans-serif; line-height: 1.5; }
            .record { margin: 16px 0; padding: 12px 16px; border: 1px solid #d1d5db; }
            img { display: block; margin-top: 12px; }
        </style>
    </head>
    <body>
        <h1>他のユーザーの日報</h1>

        @forelse ($dailyRecords as $dailyRecord)
            <article class="record">
                <p>投稿者：<a href="{{ route('users.show', $dailyRecord->user) }}">{{ $dailyRecord->user->name }}</a></p>
                <p>日付：{{ $dailyRecord->record_date }}</p>
                <p>今日やったこと：{{ $dailyRecord->actions }}</p>
                <p>良かったこと：{{ $dailyRecord->good_points }}</p>
                <p>改善点：{{ $dailyRecord->improvement_points }}</p>
                <p>改善策：{{ $dailyRecord->improvement_strategy }}</p>
                <p>公開設定：{{ $dailyRecord->is_public ? '公開' : '非公開' }}</p>

                @if ($dailyRecord->image_path)
                    <img src="{{ asset('storage/' . $dailyRecord->image_path) }}" alt="日報画像" width="320">
                @endif

                <a href="{{ route('records.show', $dailyRecord) }}">詳細を見る</a>
            </article>
        @empty
            <p>閲覧できる他のユーザーの日報はまだありません。</p>
        @endforelse

        <a href="{{ route('home') }}">自分の日報一覧へ戻る</a>
    </body>
</html>
