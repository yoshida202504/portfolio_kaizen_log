@extends('layouts.guest')

@section('title', '新規登録 | Kaizen Log')

@section('content')
    <section class="guest-card">
        <h1>新規登録</h1>
        <p>さっそく始めましょう。日々の記録が、次の自分をつくります。</p>

        @if ($errors->any())
            <div class="error-summary" role="alert">
                <p>入力内容を確認してください。</p>
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.store') }}">
            @csrf
            <div class="form-field">
                <label for="name">ユーザー名<span class="field-required">必須</span></label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required maxlength="30" autocomplete="name" aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}" aria-describedby="@error('name') name-error @enderror">
                @error('name')<p id="name-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-field">
                <label for="email">メールアドレス<span class="field-required">必須</span></label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email" aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}" aria-describedby="@error('email') email-error @enderror">
                @error('email')<p id="email-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-field">
                <label for="password">パスワード<span class="field-required">必須</span></label>
                <input id="password" name="password" type="password" required autocomplete="new-password" aria-invalid="{{ $errors->has('password') ? 'true' : 'false' }}" aria-describedby="password-help @error('password') password-error @enderror">
                <p id="password-help" class="form-help">8文字以上で、英大文字・英小文字・数字をそれぞれ含めてください。</p>
                @error('password')<p id="password-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-field">
                <label for="password_confirmation">パスワード確認<span class="field-required">必須</span></label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
            </div>
            <div class="form-actions">
                <button class="button" type="submit">登録する</button>
            </div>
        </form>

        <p class="guest-footer">すでにアカウントをお持ちの方は、<a href="{{ route('login') }}">ログイン</a></p>
    </section>
@endsection
