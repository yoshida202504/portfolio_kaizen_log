@extends('layouts.app')

@section('title', '改善結果 | Kaizen Log')

@section('content')
    <header class="page-header">
        <div><p class="eyebrow">LOOK BACK, MOVE FORWARD</p><h1>改善結果を記録する</h1><p class="page-lead">試した行動がどう変化につながったかを振り返ります。</p></div>
        <a class="button-secondary" href="{{ route('records.show', $record) }}">日報詳細へ戻る</a>
    </header>

    <section class="card form-card">
        <div class="profile-item"><span>{{ $record->record_date }} の改善策</span><strong>{{ $record->improvement_strategy }}</strong></div>
        @if ($errors->any())
            <div class="error-summary" role="alert"><p>入力内容を確認してください。</p><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('records.improvement.update', $record) }}">
            @csrf
            @method('PATCH')
            <div class="form-field"><label for="improvement_result">改善結果<span class="field-optional">任意</span></label><textarea id="improvement_result" name="improvement_result" maxlength="1000" placeholder="実行してみて分かったこと・次に活かしたいこと" aria-invalid="{{ $errors->has('improvement_result') ? 'true' : 'false' }}" aria-describedby="@error('improvement_result') improvement-result-error @enderror">{{ old('improvement_result', $record->improvement_result) }}</textarea>@error('improvement_result')<p id="improvement-result-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="improvement_rate">改善率<span class="field-optional">任意</span></label><select id="improvement_rate" name="improvement_rate" aria-invalid="{{ $errors->has('improvement_rate') ? 'true' : 'false' }}" aria-describedby="@error('improvement_rate') improvement-rate-error @enderror"><option value="">選択しない</option>@foreach ([0, 20, 40, 60, 80, 100] as $rate)<option value="{{ $rate }}" @selected((string) old('improvement_rate', $record->improvement_rate) === (string) $rate)>{{ $rate }}%</option>@endforeach</select>@error('improvement_rate')<p id="improvement-rate-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-actions"><button class="button" type="submit">保存する</button></div>
        </form>
    </section>
@endsection
