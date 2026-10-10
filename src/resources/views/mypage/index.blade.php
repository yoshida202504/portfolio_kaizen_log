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

        <section class="mypage-improvement-summary" aria-label="改善の要約">
            <article class="summary-card">
                <span>今月の改善行動数</span>
                <strong>{{ $monthlyImprovementActionCount }}件</strong>
                <p>A〜Cとして実施した改善策</p>
            </article>
            <article class="summary-card">
                <span>未振り返り</span>
                <strong>{{ $pendingImprovementCount }}件</strong>
                <p>入力期限が近い日報</p>
            </article>
            <a class="summary-card summary-card-link" href="{{ route('improvement-records.index') }}">
                <span>改善記録</span>
                <strong>振り返る</strong>
                <p>週ごとの実施数と評価を見る</p>
            </a>
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
                @if ($likedRecords->hasPages())
                    <nav class="pagination" aria-label="いいねした日報のページ移動">
                        @if ($likedRecords->onFirstPage())
                            <span class="pagination-link is-disabled" aria-disabled="true">前へ</span>
                        @else
                            <a class="pagination-link" href="{{ $likedRecords->previousPageUrl() }}" rel="prev">前へ</a>
                        @endif
                        <span class="pagination-status" aria-current="page">{{ $likedRecords->currentPage() }} / {{ $likedRecords->lastPage() }} ページ</span>
                        @if ($likedRecords->hasMorePages())
                            <a class="pagination-link" href="{{ $likedRecords->nextPageUrl() }}" rel="next">次へ</a>
                        @else
                            <span class="pagination-link is-disabled" aria-disabled="true">次へ</span>
                        @endif
                    </nav>
                @endif
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

    </div>
@endsection
