@extends('layouts.app')

@section('title', '改善結果 | Kaizen Log')

@section('content')
    <header class="page-header">
        <div><p class="eyebrow">LOOK BACK, MOVE FORWARD</p><h1>改善結果を記録する</h1><p class="page-lead">試した行動がどう変化につながったかを振り返ります。</p></div>
        <a class="button-secondary" href="{{ route('records.show', $record) }}">日報詳細へ戻る</a>
    </header>

    <section class="card form-card">
        <div class="profile-item"><span>{{ $record->record_date }} の改善策</span><strong>{{ $record->improvement_strategy }}</strong></div>
        <div class="profile-item"><span>想定する結果</span><strong>{{ $record->expected_result ?: '既存の日報には想定結果がありません。日報編集で追加できます。' }}</strong></div>
        @if ($errors->any())
            <div class="error-summary" role="alert"><p>入力内容を確認してください。</p><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('records.improvement.update', $record) }}" data-improvement-form>
            @csrf
            @method('PATCH')
            @php($improvementRecord = $record->improvementRecord)
            <fieldset>
                <legend>評価<span class="field-required">必須</span></legend>
                <p class="form-help">A〜Cは改善策を実施した結果、Dは実施できなかった場合に選択します。</p>
                <div class="choice-group">
                    @foreach (['A' => '想定より良い結果が出た', 'B' => '想定どおりの結果が出た', 'C' => '想定より結果が良くなかった', 'D' => '実施できなかった'] as $value => $label)
                        <label><input name="evaluation" type="radio" value="{{ $value }}" @checked(old('evaluation', $improvementRecord?->evaluation) === $value)> {{ $value }}：{{ $label }}</label>
                    @endforeach
                </div>
                @error('evaluation')<p class="field-error">{{ $message }}</p>@enderror
            </fieldset>
            <div data-improvement-group="executed">
            <div class="form-field"><label for="executed_at">実施日<span class="field-required">A〜Cの場合は必須</span></label><input id="executed_at" name="executed_at" type="date" value="{{ old('executed_at', $improvementRecord?->executed_at?->toDateString()) }}" max="{{ now()->toDateString() }}" aria-invalid="{{ $errors->has('executed_at') ? 'true' : 'false' }}" aria-describedby="@error('executed_at') executed-at-error @enderror">@error('executed_at')<p id="executed-at-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="actual_result">実際の結果<span class="field-required">A〜Cの場合は必須</span></label><textarea id="actual_result" name="actual_result" maxlength="1000" placeholder="実行してみて分かったこと・次に活かしたいこと" aria-invalid="{{ $errors->has('actual_result') ? 'true' : 'false' }}" aria-describedby="@error('actual_result') actual-result-error @enderror">{{ old('actual_result', $improvementRecord?->actual_result) }}</textarea>@error('actual_result')<p id="actual-result-error" class="field-error">{{ $message }}</p>@enderror</div>
            </div>
            <div data-improvement-group="not-executed">
            <div class="form-field"><label for="not_executed_reason">未実施の理由<span class="field-required">Dの場合は必須</span></label><select id="not_executed_reason" name="not_executed_reason" aria-invalid="{{ $errors->has('not_executed_reason') ? 'true' : 'false' }}" aria-describedby="@error('not_executed_reason') not-executed-reason-error @enderror"><option value="">選択してください</option><option value="forgot" @selected(old('not_executed_reason', $improvementRecord?->not_executed_reason) === 'forgot')>忘れた</option><option value="no_time" @selected(old('not_executed_reason', $improvementRecord?->not_executed_reason) === 'no_time')>時間がなかった</option><option value="unnecessary" @selected(old('not_executed_reason', $improvementRecord?->not_executed_reason) === 'unnecessary')>不要になった</option><option value="other" @selected(old('not_executed_reason', $improvementRecord?->not_executed_reason) === 'other')>その他</option></select>@error('not_executed_reason')<p id="not-executed-reason-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field" data-improvement-group="other-reason"><label for="not_executed_note">その他の理由<span class="field-required">「その他」の場合は必須</span></label><textarea id="not_executed_note" name="not_executed_note" maxlength="1000" placeholder="実施できなかった事情を記録します" aria-invalid="{{ $errors->has('not_executed_note') ? 'true' : 'false' }}" aria-describedby="@error('not_executed_note') not-executed-note-error @enderror">{{ old('not_executed_note', $improvementRecord?->not_executed_note) }}</textarea>@error('not_executed_note')<p id="not-executed-note-error" class="field-error">{{ $message }}</p>@enderror</div>
            </div>
            <div class="form-actions"><button class="button" type="submit">保存する</button></div>
        </form>
    </section>
    <script src="{{ asset('js/improvement-form.js') }}" defer></script>
@endsection
