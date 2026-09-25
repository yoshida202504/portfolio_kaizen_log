<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ログイン</title>
        <style>
            body { max-width: 480px; margin: 40px auto; padding: 0 16px; font-family: sans-serif; line-height: 1.5; }
            .form-group { margin-bottom: 16px; }
            label { display: block; margin-bottom: 4px; font-weight: bold; }
            input, button { box-sizing: border-box; width: 100%; padding: 8px; font: inherit; }
            .error { margin: 4px 0 0; color: #b91c1c; font-size: 0.9rem; }
            .error-summary { margin-bottom: 16px; padding: 12px 16px; border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; }
            button { border: 0; background: #2563eb; color: #fff; cursor: pointer; }
        </style>
    </head>
    <body>
        <h1>ログイン</h1>

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

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input id="password" name="password" type="password" required autocomplete="current-password">
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit">ログイン</button>
        </form>

        <p>アカウントをお持ちでない方は、<a href="{{ route('register') }}">ユーザー登録はこちら</a></p>
    </body>
</html>
