<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>自分の日報一覧</title>
        <style>
            body { max-width: 640px; margin: 40px auto; padding: 0 16px; font-family: sans-serif; line-height: 1.5; }
            .header { display: flex; align-items: center; justify-content: space-between; gap: 16px; }
            .record { margin: 12px 0; padding: 12px 16px; border: 1px solid #d1d5db; }
            button { padding: 8px 16px; border: 0; background: #2563eb; color: #fff; cursor: pointer; }
        </style>
    </head>
    <body>
        <div class="header">
            <h1>自分の日報一覧</h1>

            <div>
                <a href="{{ route('records.create') }}">日報を作成する</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit">ログアウト</button>
                </form>
            </div>
        </div>

        <form method="GET" action="{{ route('records.search') }}">
            <label for="record_date">日付</label>
            <input id="record_date" name="record_date" type="date" value="{{ request('record_date') }}">

            <label for="keyword">キーワード</label>
            <input id="keyword" name="keyword" type="text" value="{{ request('keyword') }}">

            <button type="submit">検索する</button>
            <a href="{{ route('home') }}">検索を解除する</a>
        </form>

        @forelse ($dailyRecords as $dailyRecord)
            <article class="record">
                <p>日付：{{ $dailyRecord->record_date }}</p>
                <p>今日やったこと：{{ $dailyRecord->actions }}</p>
                <p>良かったこと：{{ $dailyRecord->good_points }}</p>
                <p>改善点：{{ $dailyRecord->improvement_points }}</p>
                <p>改善策：{{ $dailyRecord->improvement_strategy }}</p>
                <p>公開設定：{{ $dailyRecord->is_public ? '公開' : '非公開' }}</p>
                <a href="{{ route('records.show', $dailyRecord) }}">詳細を見る</a>
            </article>
        @empty
            <p>まだ日報がありません。</p>
        @endforelse
    </body>
</html>
