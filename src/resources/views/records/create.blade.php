@extends('layouts.app')

@section('title', '日報作成 | Kaizen Log')

@section('content')
    <header class="page-header">
        <div>
            <p class="eyebrow">DAILY REFLECTION</p>
            <h1>日報を作成する</h1>
            <p class="page-lead">できたこと、つまずき、次に変える小さな行動を書き残します。</p>
        </div>
        <a class="button-secondary" href="{{ route('home') }}">一覧へ戻る</a>
    </header>

    <section class="card form-card">
        @if ($errors->any())
            <div class="error-summary" role="alert"><p>入力内容を確認してください。</p><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif

        <form method="POST" action="{{ route('records.store') }}" enctype="multipart/form-data">
            @csrf
            <div class="form-field">
                <label for="record_date">日付<span class="field-required">必須</span></label>
                <input id="record_date" name="record_date" type="date" value="{{ old('record_date', now()->toDateString()) }}" required aria-invalid="{{ $errors->has('record_date') ? 'true' : 'false' }}" aria-describedby="@error('record_date') record-date-error @enderror">
                @error('record_date')<p id="record-date-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-field">
                <label for="actions">今日やったこと<span class="field-required">必須</span></label>
                <textarea id="actions" name="actions" required maxlength="1000" placeholder="今日取り組んだことを記録します" aria-invalid="{{ $errors->has('actions') ? 'true' : 'false' }}" aria-describedby="@error('actions') actions-error @enderror">{{ old('actions') }}</textarea>
                @error('actions')<p id="actions-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-field">
                <label for="good_points">良かったこと<span class="field-required">必須</span></label>
                <textarea id="good_points" name="good_points" required maxlength="1000" placeholder="できたこと・うまくいったこと" aria-invalid="{{ $errors->has('good_points') ? 'true' : 'false' }}" aria-describedby="@error('good_points') good-points-error @enderror">{{ old('good_points') }}</textarea>
                @error('good_points')<p id="good-points-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-field">
                <label for="improvement_points">改善点<span class="field-required">必須</span></label>
                <textarea id="improvement_points" name="improvement_points" required maxlength="1000" placeholder="つまずいたこと・見直したいこと" aria-invalid="{{ $errors->has('improvement_points') ? 'true' : 'false' }}" aria-describedby="@error('improvement_points') improvement-points-error @enderror">{{ old('improvement_points') }}</textarea>
                @error('improvement_points')<p id="improvement-points-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-field">
                <label for="improvement_strategy">改善策<span class="field-required">必須</span></label>
                <textarea id="improvement_strategy" name="improvement_strategy" required maxlength="1000" placeholder="明日から試す、次の小さな行動" aria-invalid="{{ $errors->has('improvement_strategy') ? 'true' : 'false' }}" aria-describedby="@error('improvement_strategy') improvement-strategy-error @enderror">{{ old('improvement_strategy') }}</textarea>
                @error('improvement_strategy')<p id="improvement-strategy-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <fieldset>
                <legend>この日報を公開しますか？<span class="field-optional">任意</span></legend>
                <p class="form-help">未選択時は非公開です。公開すると、他のユーザーが閲覧できるようになります。</p>
                <div class="choice-group">
                    <label><input name="is_public" type="radio" value="0" @checked((string) old('is_public', '0') === '0')> 非公開</label>
                    <label><input name="is_public" type="radio" value="1" @checked((string) old('is_public') === '1')> 公開</label>
                </div>
                @error('is_public')<p class="field-error">{{ $message }}</p>@enderror
            </fieldset>
            <div class="form-field">
                <label for="image">画像<span class="field-optional">任意</span></label>
                <input id="image" name="image" type="file" accept=".jpg,.jpeg,.png,.webp" aria-invalid="{{ $errors->has('image') ? 'true' : 'false' }}" aria-describedby="image-help @error('image') image-error @enderror">
                <p id="image-help" class="form-help">jpg / jpeg / png / webp、5MBまで</p>
                @error('image')<p id="image-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-actions"><button class="button" type="submit">日報を登録する</button></div>
        </form>
    </section>
@endsection
