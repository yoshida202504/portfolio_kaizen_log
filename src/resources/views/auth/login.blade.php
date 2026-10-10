@extends('layouts.guest')
@section('title', 'ログイン | Kaizen Log')

@section('content')
    <section class="guest-card">
        <h1>おかえりなさい</h1>
        <p>今日の振り返りから、次の小さな行動を見つけましょう。</p>

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

        <form method="POST" action="{{ route('login.store') }}">
            @csrf
            <div class="form-field">
                <label for="email">メールアドレス</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-field">
                <label for="password">パスワード</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
                @error('password')<p class="field-error">{{ $message }}</p>@enderror
            </div>
            <div class="form-actions">
                <button class="button" type="submit">ログイン</button>
            </div>
        </form>

        <div class="guest-login">
            <p>登録せずに機能を試したい方は、デモデータ入りのゲストアカウントをご利用ください。</p>
            <form method="POST" action="{{ route('login.guest') }}">
                @csrf
                <button class="button-secondary" type="submit">ゲストとして試す</button>
            </form>
            @error('guest')<p class="field-error">{{ $message }}</p>@enderror
        </div>

        <p class="guest-footer">アカウントをお持ちでない方は、<a href="{{ route('register') }}">新規登録はこちら</a></p>
    </section>
@endsection
