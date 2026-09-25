<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>改善結果を記録する</title>
        <style>
            body { max-width: 640px; margin: 40px auto; padding: 0 16px; font-family: sans-serif; line-height: 1.5; }
            label { display: block; margin-top: 16px; font-weight: bold; }
            textarea, select { box-sizing: border-box; width: 100%; margin-top: 4px; padding: 8px; }
            textarea { min-height: 120px; }
            .record { margin: 16px 0; padding: 12px 16px; border: 1px solid #d1d5db; }
            .errors { padding: 12px 16px; color: #b91c1c; background: #fef2f2; }
            button { margin-top: 24px; padding: 8px 16px; border: 0; background: #2563eb; color: #fff; cursor: pointer; }
        </style>
    </head>
    <body>
        <h1>改善結果を記録する</h1>

        <section class="record">
            <p>日付：{{ $record->record_date }}</p>
            <p>改善策：{{ $record->improvement_strategy }}</p>
        </section>

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('records.improvement.update', $record) }}">
            @csrf
            @method('PATCH')

            <label for="improvement_result">改善結果</label>
            <textarea id="improvement_result" name="improvement_result">{{ old('improvement_result', $record->improvement_result) }}</textarea>

            <label for="improvement_rate">改善率</label>
            <select id="improvement_rate" name="improvement_rate">
                <option value="">選択しない</option>
                @foreach ([0, 20, 40, 60, 80, 100] as $rate)
                    <option value="{{ $rate }}" @selected((string) old('improvement_rate', $record->improvement_rate) === (string) $rate)>
                        {{ $rate }}%
                    </option>
                @endforeach
            </select>

            <button type="submit">保存する</button>
        </form>

        <p><a href="{{ route('records.show', $record) }}">日報詳細へ戻る</a></p>
    </body>
</html>
