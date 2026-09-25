<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>ユーザー登録</title>
        <style>
            body { max-width: 480px; margin: 40px auto; padding: 0 16px; font-family: sans-serif; line-height: 1.5; }
            .form-group { margin-bottom: 16px; }
            label { display: block; margin-bottom: 4px; font-weight: bold; }
            input, select, button { box-sizing: border-box; width: 100%; padding: 8px; font: inherit; }
            .error { margin: 4px 0 0; color: #b91c1c; font-size: 0.9rem; }
            .error-summary { margin-bottom: 16px; padding: 12px 16px; border: 1px solid #fecaca; background: #fef2f2; color: #991b1b; }
            button { border: 0; background: #2563eb; color: #fff; cursor: pointer; }
        </style>
    </head>
    <body>
        <h1>ユーザー登録</h1>

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

            <div class="form-group">
                <label for="name">ユーザー名</label>
                <input id="name" name="name" type="text" value="{{ old('name') }}" required autocomplete="name">
                @error('name')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email">
                @error('email')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password">パスワード</label>
                <input id="password" name="password" type="password" required autocomplete="new-password">
                @error('password')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="password_confirmation">パスワード確認</label>
                <input id="password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password">
            </div>

            <div class="form-group">
                <label for="age">年齢（任意）</label>
                <input id="age" name="age" type="number" value="{{ old('age') }}" min="15" max="99" step="1">
                @error('age')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <div class="form-group">
                <label for="gender">性別</label>
                <select id="gender" name="gender" required>
                    <option value="" disabled @selected(old('gender') === null)>選択してください</option>
                    <option value="男性" @selected(old('gender') === '男性')>男性</option>
                    <option value="女性" @selected(old('gender') === '女性')>女性</option>
                    <option value="回答しない" @selected(old('gender') === '回答しない')>回答しない</option>
                </select>
                @error('gender')
                    <p class="error">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit">登録する</button>
        </form>
    </body>
</html>
