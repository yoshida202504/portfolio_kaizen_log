@extends('layouts.app')
@section('title', '他のユーザーの日報 | Kaizen Log')

@section('content')
    <header class="page-header">
        <div><p class="eyebrow">COMMUNITY</p><h1>他のユーザーの日報</h1><p class="page-lead">ほかの人の公開日報から、次の一歩のヒントを見つけましょう。</p></div>
        <a class="button-secondary" href="{{ route('home') }}">自分の日報へ戻る</a>
    </header>

    @php($isSearching = request()->filled('record_date') || request()->filled('keyword'))

    <form class="search-form" method="GET" action="{{ route('community.index') }}">
        <div class="field">
            <label for="record_date">日付</label>
            <input id="record_date" name="record_date" type="date" value="{{ request('record_date') }}">
        </div>
        <div class="field">
            <label for="keyword">キーワード</label>
            <input id="keyword" name="keyword" type="text" value="{{ request('keyword') }}" placeholder="今日やったこと・改善点など">
        </div>
        <button class="button" type="submit">検索する</button>
        <a class="button-secondary" href="{{ route('community.index') }}">検索を解除</a>
    </form>

    <section class="record-list">
        @forelse ($dailyRecords as $dailyRecord)
            <article id="record-{{ $dailyRecord->id }}" class="record-card">
                <div class="record-date">{{ $dailyRecord->record_date }}<br><a href="{{ route('users.show', $dailyRecord->user) }}">{{ $dailyRecord->user->name }}</a></div>
                <div>
                    <h3>{{ $dailyRecord->actions }}</h3>
                    <p>良かったこと：{{ $dailyRecord->good_points }}</p>
                    <p>改善策：{{ $dailyRecord->improvement_strategy }}</p>
                    @if ($dailyRecord->image_path)<img class="detail-image" src="{{ asset('storage/' . $dailyRecord->image_path) }}" alt="日報画像">@endif
                </div>
                <div class="record-actions"><span class="status {{ $dailyRecord->is_public ? 'status-public' : 'status-private' }}">{{ $dailyRecord->is_public ? '公開' : '非公開' }}</span><a href="{{ route('records.show', array_filter(['record' => $dailyRecord, 'source' => 'community', 'page' => $dailyRecords->currentPage(), 'record_date' => request('record_date'), 'keyword' => request('keyword')], fn ($value) => filled($value))) }}">詳細を見る</a><span class="list-like-count" aria-label="いいね {{ $dailyRecord->likes_count }}件"><span aria-hidden="true">♡</span> {{ $dailyRecord->likes_count }}</span></div>
            </article>
        @empty
            <p class="empty-state">{{ $isSearching ? '条件に合う日報はありません。' : '公開されている他のユーザーの日報はまだありません。' }}</p>
        @endforelse
    </section>
    @if ($dailyRecords->hasPages())
        <nav class="pagination" aria-label="他のユーザーの日報一覧のページ移動">
            @if ($dailyRecords->onFirstPage())
                <span class="pagination-link is-disabled" aria-disabled="true">前へ</span>
            @else
                <a class="pagination-link" href="{{ $dailyRecords->previousPageUrl() }}" rel="prev">前へ</a>
            @endif
            <span class="pagination-status" aria-current="page">{{ $dailyRecords->currentPage() }} / {{ $dailyRecords->lastPage() }} ページ</span>
            @if ($dailyRecords->hasMorePages())
                <a class="pagination-link" href="{{ $dailyRecords->nextPageUrl() }}" rel="next">次へ</a>
            @else
                <span class="pagination-link is-disabled" aria-disabled="true">次へ</span>
            @endif
        </nav>
    @endif
@endsection
