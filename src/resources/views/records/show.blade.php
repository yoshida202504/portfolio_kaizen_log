@extends('layouts.app')
@section('title', '日報詳細 | Kaizen Log')

@section('content')
    <header class="page-header">
        <div>
            <p class="eyebrow">DAILY RECORD</p>
            <h1>日報詳細</h1>
            <p class="page-lead">記録を振り返り、次の改善につなげます。</p>
        </div>
        <a class="button-secondary" href="{{ $returnUrl }}">{{ $returnLabel }}</a>
    </header>

    <div class="detail-grid">
        <div>
            <article class="card detail-card">
                <span class="status {{ $record->is_public ? 'status-public' : 'status-private' }}">{{ $record->is_public ? '公開' : '非公開' }}</span>
                <p><span class="detail-label">日付</span>{{ $record->record_date }}</p>
                <p><span class="detail-label">今日やったこと</span>{{ $record->actions }}</p>
                <p><span class="detail-label">良かったこと</span>{{ $record->good_points }}</p>
                <p><span class="detail-label">改善点</span>{{ $record->improvement_points }}</p>
                <p><span class="detail-label">改善策</span>{{ $record->improvement_strategy }}</p>
                <p><span class="detail-label">想定する結果</span>{{ $record->expected_result ?: '未記録' }}</p>
                @if ($record->improvementRecord)
                    <p><span class="detail-label">評価</span>{{ $record->improvementRecord->evaluation }}</p>
                    @if ($record->improvementRecord->evaluation === 'D')
                        <p><span class="detail-label">未実施の理由</span>{{ ['forgot' => '忘れた', 'no_time' => '時間がなかった', 'unnecessary' => '不要になった', 'other' => 'その他'][$record->improvementRecord->not_executed_reason] }}</p>
                        @if ($record->improvementRecord->not_executed_note)
                            <p><span class="detail-label">その他の理由</span>{{ $record->improvementRecord->not_executed_note }}</p>
                        @endif
                    @else
                        <p><span class="detail-label">実施日</span>{{ $record->improvementRecord->executed_at?->format('Y-m-d') }}</p>
                        <p><span class="detail-label">実際の結果</span>{{ $record->improvementRecord->actual_result }}</p>
                    @endif
                @elseif ($record->improvement_result || ! is_null($record->improvement_rate))
                    <p><span class="detail-label">改善結果（旧記録）</span>{{ $record->improvement_result ?: '未記録' }}</p>
                    <p><span class="detail-label">改善率（旧記録）</span>{{ is_null($record->improvement_rate) ? '未記録' : $record->improvement_rate . '%' }}</p>
                @else
                    <p><span class="detail-label">改善記録</span>未記録</p>
                @endif
                @if ($record->image_path)
                    <img class="detail-image" src="{{ asset('storage/' . $record->image_path) }}" alt="日報画像">
                @endif
                <div id="like-section" class="detail-like">
                    @if ($canInteract)
                        <form method="POST" action="{{ $hasLiked ? route('records.like.destroy', $record) : route('records.like.store', $record) }}">
                            @csrf
                            @if ($hasLiked) @method('DELETE') @endif
                            <button class="like-button {{ $hasLiked ? 'is-liked' : '' }}" type="submit" aria-label="{{ $hasLiked ? 'いいねを解除する 現在' : 'いいねする 現在' }}{{ $record->likes_count }}件">
                                <span class="like-icon" aria-hidden="true">{{ $hasLiked ? '♥' : '♡' }}</span>
                                <span>{{ $record->likes_count }}</span>
                            </button>
                        </form>
                    @else
                        <span class="like-count" aria-label="いいね {{ $record->likes_count }}件"><span class="like-icon" aria-hidden="true">♡</span> {{ $record->likes_count }}</span>
                    @endif
                </div>
            </article>

            <section class="card" id="comments">
                <h2 class="panel-title">コメント</h2>
                @forelse ($record->comments as $comment)
                    <article id="comment-{{ $comment->id }}" class="comment">
                        <p class="comment-meta">{{ $comment->user?->name ?? '退会したユーザー' }}</p>
                        <p class="comment-body">{{ $comment->body }}</p>
                        @can('update', $comment)
                            <form method="POST" action="{{ route('comments.update', $comment) }}">
                                @csrf @method('PATCH')
                                <div class="form-field"><label for="comment-body-{{ $comment->id }}">コメントを編集<span class="field-required">必須</span></label><textarea id="comment-body-{{ $comment->id }}" name="body" rows="3" maxlength="1000" required aria-invalid="{{ $errors->has('body') ? 'true' : 'false' }}" aria-describedby="@error('body') comment-body-error @enderror">{{ old('body', $comment->body) }}</textarea>@error('body')<p id="comment-body-error" class="field-error">{{ $message }}</p>@enderror</div>
                                <div class="form-actions"><button class="button-secondary" type="submit">更新する</button></div>
                            </form>
                        @endcan
                        @can('delete', $comment)
                            <form method="POST" action="{{ route('comments.destroy', $comment) }}" onsubmit="return confirm('このコメントを削除しますか？')">@csrf @method('DELETE') <button class="text-button" type="submit">削除する</button></form>
                        @endcan
                    </article>
                @empty
                    <p class="empty-state">コメントはまだありません。</p>
                @endforelse

                @if ($canInteract)
                    <form method="POST" action="{{ route('records.comments.store', $record) }}">
                        @csrf
                        <div class="form-field"><label for="body">コメントを投稿<span class="field-required">必須</span></label><textarea id="body" name="body" rows="4" maxlength="1000" required placeholder="記録を読んで感じたことを書きましょう" aria-invalid="{{ $errors->has('body') ? 'true' : 'false' }}" aria-describedby="@error('body') comment-body-error @enderror">{{ old('body') }}</textarea>@error('body')<p id="comment-body-error" class="field-error">{{ $message }}</p>@enderror</div>
                        <div class="form-actions"><button class="button" type="submit">コメントする</button></div>
                    </form>
                @endif
            </section>
        </div>

        <aside class="side-actions">
            @can('update', $record)
                <a class="button-secondary" href="{{ route('records.edit', $record) }}">編集する</a>
                @if ($record->isImprovementInputAvailable())
                    <a class="button" href="{{ route('records.improvement.edit', $record) }}">改善結果を記録・編集</a>
                @else
                    <p class="form-help">改善結果の入力期限を過ぎています。</p>
                @endif
            @endcan
            @can('delete', $record)
                <form method="POST" action="{{ route('records.destroy', $record) }}" onsubmit="return confirm('この日報を削除しますか？')">@csrf @method('DELETE') <button class="button-danger" type="submit">削除する</button></form>
            @endcan
        </aside>
    </div>
@endsection
