@extends('layouts.app')
@section('title', '自分の日報 | Kaizen Log')

@section('content')
    <header class="page-header">
        <div>
            <p class="eyebrow">REFLECT &amp; GROW</p>
            <h1>自分の日報一覧</h1>
            <p class="page-lead">今日を見つめ、次の小さな行動につなげましょう。</p>
        </div>
    </header>

    <div class="dashboard-grid">
        <div>
            <div class="summary-grid">
                <div class="summary-card"><span>記録した日報</span><strong>{{ $dailyRecords->total() }}件</strong></div>
                <div class="summary-card"><span>今月の目標</span><strong>振り返り</strong></div>
            </div>

            <form class="search-form" method="GET" action="{{ route('records.search') }}">
                <div class="field">
                    <label for="record_date">日付</label>
                    <input id="record_date" name="record_date" type="date" value="{{ request('record_date') }}">
                </div>
                <div class="field">
                    <label for="keyword">キーワード</label>
                    <input id="keyword" name="keyword" type="text" value="{{ request('keyword') }}" placeholder="今日やったこと・改善点など">
                </div>
                <button class="button" type="submit">検索する</button>
                <a class="button-secondary" href="{{ route('home') }}">検索を解除</a>
            </form>

            <section class="page-section">
                <h2 class="panel-title">最近の日報 <span>{{ request()->hasAny(['record_date', 'keyword']) ? '検索結果' : '' }}</span></h2>
                <div class="record-list">
                    @forelse ($dailyRecords as $dailyRecord)
                        <article id="record-{{ $dailyRecord->id }}" class="record-card">
                            <div class="record-date">{{ $dailyRecord->record_date }}</div>
                            <div>
                                <h3>{{ $dailyRecord->actions }}</h3>
                                <p>改善策：{{ $dailyRecord->improvement_strategy }}</p>
                            </div>
                            <div class="record-actions">
                                <span class="status {{ $dailyRecord->is_public ? 'status-public' : 'status-private' }}">{{ $dailyRecord->is_public ? '公開' : '非公開' }}</span>
                                @php
                                    $detailParameters = [
                                        'record' => $dailyRecord,
                                        'source' => request()->routeIs('records.search') ? 'search' : 'home',
                                        'page' => $dailyRecords->currentPage(),
                                    ];

                                    if (request()->routeIs('records.search')) {
                                        $detailParameters['record_date'] = request('record_date');
                                        $detailParameters['keyword'] = request('keyword');
                                    }
                                @endphp
                                <a href="{{ route('records.show', $detailParameters) }}">詳細を見る</a>
                            </div>
                        </article>
                    @empty
                        <p class="empty-state">まだ日報がありません。今日できたことを、ひとつだけでも記録してみましょう。</p>
                    @endforelse
                </div>
                @if ($dailyRecords->hasPages())
                    <nav class="pagination" aria-label="自分の日報一覧のページ移動">
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
            </section>
        </div>

        <aside class="card">
            <h2 class="panel-title">次の行動へ</h2>
            <p>日報は、自分の変化に気づくための小さな記録です。</p>
            <p><a href="{{ route('records.create') }}">今日の日報を作成する</a></p>
            <p><a href="{{ route('community.index') }}">他のユーザーの日報を見る</a></p>
            <p><a href="{{ route('mypage') }}">改善率の推移を見る</a></p>
        </aside>
    </div>
@endsection
