<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ユーザー詳細</title>
        <style>
            body { max-width: 720px; margin: 40px auto; padding: 0 16px; font-family: sans-serif; line-height: 1.5; }
            .profile, .record { margin: 16px 0; padding: 12px 16px; border: 1px solid #d1d5db; }
            button { padding: 8px 16px; border: 0; background: #2563eb; color: #fff; cursor: pointer; }
            img { display: block; margin-top: 12px; }
        </style>
    </head>
    <body>
        <h1>ユーザー詳細</h1>

        <section class="profile">
            <p>ユーザー名：{{ $user->name }}</p>
            @if (! is_null($user->age))
                <p>年齢：{{ $user->age }}</p>
            @endif
            <p>性別：{{ $user->gender }}</p>

            @if ($isOwnProfile)
                <p>自分のプロフィールです。自分の日報は<a href="{{ route('home') }}">自分の日報一覧</a>で確認できます。</p>
            @elseif ($isFollowing)
                <p>フォロー中です。</p>
                <form method="POST" action="{{ route('users.follow.destroy', $user) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit">フォローを解除する</button>
                </form>
            @else
                <p>フォローしていません。</p>
                <form method="POST" action="{{ route('users.follow.store', $user) }}">
                    @csrf
                    <button type="submit">フォローする</button>
                </form>
            @endif
        </section>

        @if (! $isOwnProfile)
            <h2>{{ $user->name }}さんの閲覧可能な日報</h2>

            @forelse ($dailyRecords as $dailyRecord)
                <article class="record">
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
                <p>閲覧できる日報はまだありません。</p>
            @endforelse
        @endif

        <a href="{{ route('community.index') }}">他のユーザーの日報へ戻る</a>
    </body>
</html>
