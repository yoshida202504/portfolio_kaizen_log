@extends('layouts.app')
@section('title', 'ユーザー詳細 | Kaizen Log')

@section('content')
    <header class="page-header">
        <div><p class="eyebrow">USER PROFILE</p><h1>ユーザー詳細</h1><p class="page-lead">公開されている記録と、日々の工夫を確認できます。</p></div>
        <a class="button-secondary" href="{{ route('community.index') }}">Communityへ戻る</a>
    </header>

    <section class="card">
        <div class="profile-grid">
            <div class="profile-item"><span>ユーザー名</span><strong>{{ $user->name }}</strong></div>
            @if (! is_null($user->age))<div class="profile-item"><span>年齢</span><strong>{{ $user->age }}</strong></div>@endif
            <div class="profile-item"><span>性別</span><strong>{{ $user->gender }}</strong></div>
        </div>
        <div class="form-actions">
            @if ($isOwnProfile)
                <p>自分のプロフィールです。日報は<a href="{{ route('home') }}">自分の日報一覧</a>で確認できます。</p>
            @elseif ($isFollowing)
                <form method="POST" action="{{ route('users.follow.destroy', $user) }}">@csrf @method('DELETE') <button class="button-secondary" type="submit">フォローを解除する</button></form>
            @else
                <form method="POST" action="{{ route('users.follow.store', $user) }}">@csrf <button class="button" type="submit">フォローする</button></form>
            @endif
        </div>
    </section>

    @if (! $isOwnProfile)
        <section class="page-section" style="margin-top: 28px;">
            <h2 class="panel-title">{{ $user->name }}さんの閲覧可能な日報</h2>
            <div class="record-list">
                @forelse ($dailyRecords as $dailyRecord)
                    <article id="record-{{ $dailyRecord->id }}" class="record-card">
                        <div class="record-date">{{ $dailyRecord->record_date }}</div>
                        <div><h3>{{ $dailyRecord->actions }}</h3><p>改善策：{{ $dailyRecord->improvement_strategy }}</p>@if ($dailyRecord->image_path)<img class="detail-image" src="{{ asset('storage/' . $dailyRecord->image_path) }}" alt="日報画像">@endif</div>
                        <div class="record-actions"><span class="status {{ $dailyRecord->is_public ? 'status-public' : 'status-private' }}">{{ $dailyRecord->is_public ? '公開' : '非公開' }}</span><a href="{{ route('records.show', ['record' => $dailyRecord, 'source' => 'user', 'user' => $user->id, 'page' => $dailyRecords->currentPage()]) }}">詳細を見る</a><span class="list-like-count" aria-label="いいね {{ $dailyRecord->likes_count }}件"><span aria-hidden="true">♡</span> {{ $dailyRecord->likes_count }}</span></div>
                    </article>
                @empty
                    <p class="empty-state">閲覧できる日報はまだありません。</p>
                @endforelse
            </div>
            @if ($dailyRecords->hasPages())
                <nav class="pagination" aria-label="{{ $user->name }}さんの日報一覧のページ移動">
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
    @endif
@endsection
