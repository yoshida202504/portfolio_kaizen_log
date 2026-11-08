@extends('layouts.app')

@section('title', 'プロフィール編集 | Kaizen Log')

@section('content')
    <header class="page-header">
        <div><p class="eyebrow">PROFILE SETTINGS</p><h1>プロフィール編集</h1><p class="page-lead">自分らしく続けるための情報を整えます。</p></div>
        <a class="button-secondary" href="{{ route('mypage') }}">マイページへ戻る</a>
    </header>

    @if ($user->isGuest())
        <p class="notice" role="note">ゲストユーザーはプロフィールの変更と退会ができません。日報の作成・改善結果の記録・コメントなどはお試しいただけます。</p>
    @endif

    <section class="card form-card">
        @if ($errors->any())
            <div class="error-summary" role="alert"><p>入力内容を確認してください。</p><ul>@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>
        @endif
        <form method="POST" action="{{ route('profile.update') }}">
            @csrf @method('PATCH')
            <fieldset class="plain-fieldset" @disabled($user->isGuest())>
            <div class="form-field"><label for="name">ユーザー名<span class="field-required">必須</span></label><input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="30" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="@error('name') name-error @enderror">@error('name')<p id="name-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-field"><label for="email">メールアドレス<span class="field-required">必須</span></label><input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" aria-describedby="@error('email') email-error @enderror">@error('email')<p id="email-error" class="field-error">{{ $message }}</p>@enderror</div>
            <div class="form-actions"><button class="button" type="submit">保存する</button></div>
            </fieldset>
        </form>
    </section>

    <section class="card form-card danger-zone">
        <h2>アカウント退会</h2>
        <p>退会するとログアウトします。退会後はログインできなくなります。</p>
        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf @method('DELETE')
            <fieldset class="plain-fieldset" @disabled($user->isGuest())>
            <label class="check-label"><input name="confirm_withdrawal" type="checkbox" value="1" @checked(old('confirm_withdrawal'))> 退会することに同意します。</label>
            @error('confirm_withdrawal')<p class="field-error">{{ $message }}</p>@enderror
            <div class="form-actions"><button class="button-danger" type="submit">退会する</button></div>
            </fieldset>
        </form>
    </section>
@endsection
