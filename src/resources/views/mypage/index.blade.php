@extends('layouts.app')
@section('title', 'マイページ | Kaizen Log')

@section('content')
    <header class="page-header">
        <div><p class="eyebrow">MY GROWTH</p><h1>マイページ</h1><p class="page-lead">自分の記録と、小さな変化を振り返りましょう。</p></div>
    </header>

    <div class="mypage-content">
        <section class="card">
            <h2 class="panel-title">プロフィール</h2>
            <div class="profile-summary">
                <strong>{{ $user->name }}</strong>
                <a class="button-secondary" href="{{ route('profile.edit') }}">プロフィールを編集する</a>
            </div>
        </section>

        <section class="card relationship-card" aria-label="いいねとフォローの情報">
            <div class="relationship-tabs" role="tablist" aria-label="いいねとフォローの表示を切り替える">
                <button id="liked-records-tab" class="relationship-tab is-active" type="button" role="tab" aria-selected="true" aria-controls="liked-records-panel">いいねの日報</button>
                <button id="following-tab" class="relationship-tab" type="button" role="tab" aria-selected="false" aria-controls="following-panel" tabindex="-1">フォロー中</button>
                <button id="followers-tab" class="relationship-tab" type="button" role="tab" aria-selected="false" aria-controls="followers-panel" tabindex="-1">フォロワー</button>
            </div>

            <div id="liked-records-panel" class="relationship-panel" role="tabpanel" tabindex="0" aria-labelledby="liked-records-tab">
                <div class="record-list">
                    @forelse ($likedRecords as $record)
                        <article class="record-card"><div class="record-date">{{ $record->record_date }}</div><div><h3>{{ $record->actions }}</h3><p>投稿者：{{ $record->user->name }}</p></div><a href="{{ route('records.show', $record) }}">詳細を見る</a></article>
                    @empty
                        <p class="empty-state">いいねした日報はありません。</p>
                    @endforelse
                </div>
            </div>

            <div id="following-panel" class="relationship-panel" role="tabpanel" tabindex="0" aria-labelledby="following-tab" hidden>
                <div class="following-list">
                    @forelse ($following as $follow)
                        <a href="{{ route('users.show', $follow->followed) }}">{{ $follow->followed->name }}</a>
                    @empty
                        <p class="empty-state">フォロー中のユーザーはいません。</p>
                    @endforelse
                </div>
            </div>

            <div id="followers-panel" class="relationship-panel" role="tabpanel" tabindex="0" aria-labelledby="followers-tab" hidden>
                <div class="following-list">
                    @forelse ($followers as $follow)
                        <a href="{{ route('users.show', $follow->follower) }}">{{ $follow->follower->name }}</a>
                    @empty
                        <p class="empty-state">フォロワーはいません。</p>
                    @endforelse
                </div>
            </div>
        </section>

        <section class="card">
            <h2 class="panel-title">改善率の推移</h2>
            @if ($chartPoints->isNotEmpty())
                <svg class="improvement-chart" viewBox="0 0 {{ $chartWidth }} {{ $chartHeight }}" role="img" aria-label="改善率の推移グラフ">
                    <line x1="{{ $chartPadding }}" y1="{{ $chartPadding }}" x2="{{ $chartPadding }}" y2="{{ $chartHeight - $chartPadding }}" stroke="#6b7280" />
                    <line x1="{{ $chartPadding }}" y1="{{ $chartHeight - $chartPadding }}" x2="{{ $chartWidth - $chartPadding }}" y2="{{ $chartHeight - $chartPadding }}" stroke="#6b7280" />
                    <text x="4" y="{{ $chartPadding + 4 }}" font-size="12">100%</text><text x="16" y="{{ $chartHeight - $chartPadding + 4 }}" font-size="12">0%</text>
                    <polyline fill="none" stroke="#1967c9" stroke-width="3" points="{{ $chartPoints->map(fn ($point) => $point['x'] . ',' . $point['y'])->implode(' ') }}" />
                    @foreach ($chartPoints as $point)
                        <circle cx="{{ $point['x'] }}" cy="{{ $point['y'] }}" r="4" fill="#1967c9"><title>{{ $point['record_date'] }}：{{ $point['rate'] }}%</title></circle>
                        @if ($point['show_date_label'])
                            <text x="{{ $point['x'] }}" y="{{ $chartLabelY }}" text-anchor="end" font-size="11" transform="rotate(-35 {{ $point['x'] }} {{ $chartLabelY }})">{{ $point['record_date'] }}</text>
                        @endif
                        <text x="{{ $point['x'] }}" y="{{ $point['y'] - 8 }}" text-anchor="middle" font-size="11">{{ $point['rate'] }}%</text>
                    @endforeach
                </svg>
                <ul>@foreach ($improvementRecords as $record)<li>{{ $record->record_date }}：{{ $record->improvement_rate }}%</li>@endforeach</ul>
            @else
                <p class="empty-state">改善率が記録された日報はまだありません。</p>
            @endif
        </section>
    </div>
@endsection
