@extends('layouts.app')

@section('title', '日報編集 | Kaizen Log')

@section('content')
    <header class="page-header">
        <div><p class="eyebrow">REFINE YOUR RECORD</p><h1>日報を編集する</h1><p class="page-lead">振り返りを、今の自分に合った言葉へ整えましょう。</p></div>
        <a class="button-secondary" href="{{ route('records.show', $record) }}">詳細へ戻る</a>
    </header>

    <section class="card form-card">
        @if ($errors->any())
            <div class="error-summary" role="alert"><p>入力内容を確認してください。</p><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('records.update', $record) }}" enctype="multipart/form-data">
            @csrf
            @method('PATCH')
            <div class="form-field"><label for="record_date">日付<span class="field-required">必須</span></label><input id="record_date" name="record_date" type="date" value="{{ old('record_date', $record->record_date) }}" required aria-invalid="{{ $errors->has('record_date') ? 'true' : 'false' }}" aria-describedby="@error('record_date') record-date-error @enderror">@error('record_date')<p id="record-date-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="actions">今日やったこと<span class="field-required">必須</span></label><textarea id="actions" name="actions" required maxlength="1000" aria-invalid="{{ $errors->has('actions') ? 'true' : 'false' }}" aria-describedby="@error('actions') actions-error @enderror">{{ old('actions', $record->actions) }}</textarea>@error('actions')<p id="actions-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="good_points">良かったこと<span class="field-required">必須</span></label><textarea id="good_points" name="good_points" required maxlength="1000" aria-invalid="{{ $errors->has('good_points') ? 'true' : 'false' }}" aria-describedby="@error('good_points') good-points-error @enderror">{{ old('good_points', $record->good_points) }}</textarea>@error('good_points')<p id="good-points-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="improvement_points">改善点<span class="field-required">必須</span></label><textarea id="improvement_points" name="improvement_points" required maxlength="1000" aria-invalid="{{ $errors->has('improvement_points') ? 'true' : 'false' }}" aria-describedby="@error('improvement_points') improvement-points-error @enderror">{{ old('improvement_points', $record->improvement_points) }}</textarea>@error('improvement_points')<p id="improvement-points-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="improvement_strategy">改善策<span class="field-required">必須</span></label><textarea id="improvement_strategy" name="improvement_strategy" required maxlength="1000" aria-invalid="{{ $errors->has('improvement_strategy') ? 'true' : 'false' }}" aria-describedby="@error('improvement_strategy') improvement-strategy-error @enderror">{{ old('improvement_strategy', $record->improvement_strategy) }}</textarea>@error('improvement_strategy')<p id="improvement-strategy-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="is_public">公開設定<span class="field-optional">任意</span></label><select id="is_public" name="is_public" aria-invalid="{{ $errors->has('is_public') ? 'true' : 'false' }}" aria-describedby="@error('is_public') is-public-error @enderror"><option value="0" @selected((string) old('is_public', (string) (int) $record->is_public) === '0')>非公開</option><option value="1" @selected((string) old('is_public', (string) (int) $record->is_public) === '1')>公開</option></select><p class="form-help">未選択時は非公開です。</p>@error('is_public')<p id="is-public-error" class="field-error">{{ $message }}</p>@enderror</div>
            @if ($record->image_path)
                <div class="form-field"><label>現在の画像</label><img class="detail-image" src="{{ asset('storage/' . $record->image_path) }}" alt="現在の日報画像"><label class="check-label"><input name="remove_image" type="checkbox" value="1"> 現在の画像を削除する</label>@error('remove_image')<p class="field-error">{{ $message }}</p>@enderror</div>
            @endif
            <div class="form-field"><label for="image">画像を変更する<span class="field-optional">任意</span></label><input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" aria-invalid="{{ $errors->has('image') ? 'true' : 'false' }}" aria-describedby="image-help @error('image') image-error @enderror"><p id="image-help" class="form-help">jpg / jpeg / png / webp、5MBまで</p>@error('image')<p id="image-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-actions"><a class="button-secondary" href="{{ route('records.show', $record) }}">キャンセル</a><button class="button" type="submit">更新する</button></div>
        </form>
    </section>
@endsection
