@extends('layouts.app')

@section('title', 'プロフィール編集 | Kaizen Log')

@section('content')
    <header class="page-header">
        <div><p class="eyebrow">PROFILE SETTINGS</p><h1>プロフィール編集</h1><p class="page-lead">自分らしく続けるための情報を整えます。</p></div>
        <a class="button-secondary" href="{{ route('mypage') }}">マイページへ戻る</a>
    </header>

    <section class="card form-card">
        @if ($errors->any())
            <div class="error-summary" role="alert"><p>入力内容を確認してください。</p><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PATCH')
            <div class="form-field"><label for="name">ユーザー名<span class="field-required">必須</span></label><input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="30" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="@error('name') name-error @enderror">@error('name')<p id="name-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="email">メールアドレス<span class="field-required">必須</span></label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" aria-describedby="@error('email') email-error @enderror">@error('email')<p id="email-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="age">年齢<span class="field-optional">任意</span></label><input id="age" name="age" type="number" value="{{ old('age', $user->age) }}" min="15" max="99" step="1" aria-invalid="{{ $errors->has('age') ? 'true' : 'false' }}" aria-describedby="@error('age') age-error @enderror">@error('age')<p id="age-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="gender">性別<span class="field-required">必須</span></label><select id="gender" name="gender" required aria-invalid="{{ $errors->has('gender') ? 'true' : 'false' }}" aria-describedby="@error('gender') gender-error @enderror"><option value="男性" @selected(old('gender', $user->gender) === '男性')>男性</option><option value="女性" @selected(old('gender', $user->gender) === '女性')>女性</option><option value="回答しない" @selected(old('gender', $user->gender) === '回答しない')>回答しない</option></select>@error('gender')<p id="gender-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-actions"><button class="button" type="submit">保存する</button></div>
        </form>
    </section>

    <section class="card form-card danger-zone">
        <h2>アカウント退会</h2>
        <p>退会するとログアウトします。退会後はログインできなくなります。</p>
        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf @method('DELETE')
            <label class="check-label"><input name="confirm_withdrawal" type="checkbox" value="1" @checked(old('confirm_withdrawal'))> 退会することに同意します。</label>
            @error('confirm_withdrawal')<p class="field-error">{{ $message }}</p>@enderror
            <div class="form-actions"><button class="button-danger" type="submit">退会する</button></div>
        </form>
    </section>
@endsection
