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
            <div class="form-field">
                <label for="age">年齢<span class="field-optional">任意</span></label>
                <input id="age" name="age" type="number" value="{{ old('age') }}" min="15" max="99" step="1" aria-invalid="{{ $errors->has('age') ? 'true' : 'false' }}" aria-describedby="@error('age') age-error @enderror">
                @error('age')<p id="age-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-field">
                <label for="gender">性別<span class="field-required">必須</span></label>
                <select id="gender" name="gender" required aria-invalid="{{ $errors->has('gender') ? 'true' : 'false' }}" aria-describedby="@error('gender') gender-error @enderror">
                    <option value="" disabled @selected(old('gender') === null)>選択してください</option>
                    <option value="男性" @selected(old('gender') === '男性')>男性</option>
                    <option value="女性" @selected(old('gender') === '女性')>女性</option>
                    <option value="回答しない" @selected(old('gender') === '回答しない')>回答しない</option>
                </select>
                @error('gender')<p id="gender-error" class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-actions">
                <button class="button" type="submit">登録する</button>
            </div>
        </form>

        <p class="guest-footer">すでにアカウントをお持ちの方は、<a href="{{ route('login') }}">ログイン</a></p>
    </section>
@endsection
