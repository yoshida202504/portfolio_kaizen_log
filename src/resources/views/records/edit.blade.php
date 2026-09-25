<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>日報を編集する</title>
        <style>
            body { max-width: 640px; margin: 40px auto; padding: 0 16px; font-family: sans-serif; line-height: 1.5; }
            label { display: block; margin-top: 16px; font-weight: bold; }
            input, textarea, select { box-sizing: border-box; width: 100%; margin-top: 4px; padding: 8px; }
            textarea { min-height: 96px; }
            .errors { padding: 12px 16px; color: #b91c1c; background: #fef2f2; }
            button { margin-top: 24px; padding: 8px 16px; border: 0; background: #2563eb; color: #fff; cursor: pointer; }
        </style>
    </head>
    <body>
        <h1>日報を編集する</h1>

        @if ($errors->any())
            <div class="errors">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('records.update', $record) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')

            <label for="record_date">日付</label>
            <input id="record_date" name="record_date" type="date" value="{{ old('record_date', $record->record_date) }}" required>

            <label for="actions">今日やったこと</label>
            <textarea id="actions" name="actions" required>{{ old('actions', $record->actions) }}</textarea>

            <label for="good_points">良かったこと</label>
            <textarea id="good_points" name="good_points" required>{{ old('good_points', $record->good_points) }}</textarea>

            <label for="improvement_points">改善点</label>
            <textarea id="improvement_points" name="improvement_points" required>{{ old('improvement_points', $record->improvement_points) }}</textarea>

            <label for="improvement_strategy">改善策</label>
            <textarea id="improvement_strategy" name="improvement_strategy" required>{{ old('improvement_strategy', $record->improvement_strategy) }}</textarea>

            <label for="is_public">公開設定</label>
            <select id="is_public" name="is_public" required>
                <option value="0" @selected((string) old('is_public', (string) (int) $record->is_public) === '0')>非公開</option>
                <option value="1" @selected((string) old('is_public', (string) (int) $record->is_public) === '1')>公開</option>
            </select>

            @if ($record->image_path)
                <p>現在の画像</p>
                <img src="{{ asset('storage/' . $record->image_path) }}" alt="現在の日報画像" width="240">
                <label>
                    <input name="remove_image" type="checkbox" value="1">
                    現在の画像を削除する
                </label>
            @endif

            <label for="image">画像を変更する</label>
            <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp">

            <button type="submit">更新する</button>
        </form>
    </body>
</html>
