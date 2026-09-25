<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>マイページ</title>
        <style>
            body { max-width: 720px; margin: 40px auto; padding: 0 16px; font-family: sans-serif; line-height: 1.5; }
            section { margin: 24px 0; padding: 16px; border: 1px solid #d1d5db; }
            .record, .user { margin: 12px 0; padding: 12px; border: 1px solid #e5e7eb; }
            svg { display: block; max-width: 100%; height: auto; margin-top: 16px; border: 1px solid #e5e7eb; }
        </style>
    </head>
    <body>
        <h1>マイページ</h1>

        <section>
            <h2>プロフィール</h2>
            <p>ユーザー名：{{ $user->name }}</p>
            <p>メールアドレス：{{ $user->email }}</p>
            @if (! is_null($user->age))
                <p>年齢：{{ $user->age }}</p>
            @endif
            <p>性別：{{ $user->gender }}</p>
            <a href="{{ route('profile.edit') }}">プロフィールを編集する</a>
        </section>

        <section>
            <h2>自分の日報</h2>
            <p><a href="{{ route('home') }}">自分の日報一覧を見る</a></p>
            <p><a href="{{ route('records.create') }}">日報を作成する</a></p>
        </section>

        <section>
            <h2>フォロー中のユーザー</h2>

            @forelse ($following as $follow)
                <article class="user">
                    <a href="{{ route('users.show', $follow->followed) }}">{{ $follow->followed->name }}</a>
                </article>
            @empty
                <p>フォロー中のユーザーはいません。</p>
            @endforelse
        </section>

        <section>
            <h2>いいねした日報</h2>

            @forelse ($likedRecords as $record)
                <article class="record">
                    <p>投稿者：{{ $record->user->name }}</p>
                    <p>日付：{{ $record->record_date }}</p>
                    <p>今日やったこと：{{ $record->actions }}</p>
                    <a href="{{ route('records.show', $record) }}">詳細を見る</a>
                </article>
            @empty
                <p>現在閲覧できる、いいねした日報はありません。</p>
            @endforelse
        </section>

        <section>
            <h2>改善率の推移</h2>

            @if ($chartPoints->isNotEmpty())
                <svg viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-label="改善率の推移グラフ">
                    <line x1="{{ $chartPadding }}" y1="{{ $chartPadding }}" x2="{{ $chartPadding }}" y2="{{ $chartHeight - $chartPadding }}" stroke="#6b7280" />
                    <line x1="{{ $chartPadding }}" y1="{{ $chartHeight - $chartPadding }}" x2="{{ $chartWidth - $chartPadding }}" y2="{{ $chartHeight - $chartPadding }}" stroke="#6b7280" />
                    <text x="4" y="{{ $chartPadding + 4 }}" font-size="12">100%</text>
                    <text x="16" y="{{ $chartHeight - $chartPadding + 4 }}" font-size="12">0%</text>
                    <polyline fill="none" stroke="#2563eb" stroke-width="3" points="{{ $chartPoints->map(fn ($point) => $point['x'] . ',' . $point['y'])->implode(' ') }}" />
                    @foreach ($chartPoints as $point)
                        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#2563eb" />
                        <text x="{{ $point['x'] }}" y="{{ $chartHeight - 12 }}" text-anchor="middle" font-size="11">{{ $point['record_date'] }}</text>
                        <text x="{{ $point['x'] }}" y="{{ $point['y'] - 8 }}" text-anchor="middle" font-size="11">{{ $point['rate'] }}%</text>
                    @endforeach
                </svg>

                <ul>
                    @foreach ($improvementRecords as $record)
                        <li>{{ $record->record_date }}：{{ $record->improvement_rate }}%</li>
                    @endforeach
                </ul>
            @else
                <p>改善率が記録された日報はまだありません。</p>
            @endif
        </section>

        <a href="{{ route('home') }}">自分の日報一覧へ戻る</a>
    </body>
</html>
