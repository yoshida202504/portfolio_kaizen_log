<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>日報詳細</title>
        <style>
            body { max-width: 640px; margin: 40px auto; padding: 0 16px; font-family: sans-serif; line-height: 1.5; }
            .record { margin: 16px 0; padding: 12px 16px; border: 1px solid #d1d5db; }
            .comment { margin: 12px 0; padding: 12px; border: 1px solid #d1d5db; }
        </style>
    </head>
    <body>
        <h1>日報詳細</h1>

        <article class="record">
            <p>日付：{{ $record->record_date }}</p>
            <p>今日やったこと：{{ $record->actions }}</p>
            <p>良かったこと：{{ $record->good_points }}</p>
            <p>改善点：{{ $record->improvement_points }}</p>
            <p>改善策：{{ $record->improvement_strategy }}</p>
            <p>改善結果：{{ $record->improvement_result ?: '未記録' }}</p>
            <p>改善率：{{ is_null($record->improvement_rate) ? '未記録' : $record->improvement_rate . '%' }}</p>
            <p>公開設定：{{ $record->is_public ? '公開' : '非公開' }}</p>

            @if ($record->image_path)
                <img src="{{ asset('storage/' . $record->image_path) }}" alt="日報画像" width="320">
            @endif
        </article>

        @can('update', $record)
            <a href="{{ route('records.edit', $record) }}">編集する</a>

            @if ($record->isImprovementInputAvailable())
                <a href="{{ route('records.improvement.edit', $record) }}">改善結果を記録・編集</a>
            @else
                <p>改善結果の入力期限を過ぎています。</p>
            @endif
        @endcan

        @can('delete', $record)
            <form method="POST" action="{{ route('records.destroy', $record) }}">
                @csrf
                @method('DELETE')
                <button type="submit">削除する</button>
            </form>
        @endcan

        <section>
            <h2>いいね</h2>
            <p>いいね数：{{ $record->likes_count }}</p>

            @if ($canInteract)
                @if ($hasLiked)
                    <form method="POST" action="{{ route('records.like.destroy', $record) }}">
                        @csrf
                        @method('DELETE')
                        <button type="submit">いいねを解除する</button>
                    </form>
                @else
                    <form method="POST" action="{{ route('records.like.store', $record) }}">
                        @csrf
                        <button type="submit">いいねする</button>
                    </form>
                @endif
            @endif
        </section>

        <section>
            <h2>コメント</h2>

            @forelse ($record->comments as $comment)
                <article class="comment">
                    <p>投稿者：{{ $comment->user?->name ?? '退会したユーザー' }}</p>
                    <p>{{ $comment->body }}</p>

                    @can('update', $comment)
                        <form method="POST" action="{{ route('comments.update', $comment) }}">
                            @csrf
                            @method('PATCH')
                            <label for="comment-body-{{ $comment->id }}">コメントを編集</label>
                            <textarea id="comment-body-{{ $comment->id }}" name="body" rows="3" maxlength="1000">{{ old('body', $comment->body) }}</textarea>
                            <button type="submit">更新する</button>
                        </form>
                    @endcan

                    @can('delete', $comment)
                        <form method="POST" action="{{ route('comments.destroy', $comment) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit">削除する</button>
                        </form>
                    @endcan
                </article>
            @empty
                <p>コメントはまだありません。</p>
            @endforelse

            @if ($canInteract)
                <form method="POST" action="{{ route('records.comments.store', $record) }}">
                    @csrf
                    <label for="body">コメントを投稿</label>
                    <textarea id="body" name="body" rows="4" maxlength="1000">{{ old('body') }}</textarea>
                    @error('body')
                        <p>{{ $message }}</p>
                    @enderror
                    <button type="submit">コメントする</button>
                </form>
            @endif
        </section>

        <a href="{{ route('home') }}">自分の日報一覧へ戻る</a>
    </body>
</html>
