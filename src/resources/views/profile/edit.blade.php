<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>プロフィール編集</title>
        <style>
            body { max-width: 560px; margin: 40px auto; padding: 0 16px; font-family: sans-serif; line-height: 1.5; }
            .form-group, .danger-zone { margin: 16px 0; }
            label { display: block; margin-bottom: 4px; font-weight: bold; }
            input, select, button { box-sizing: border-box; width: 100%; padding: 8px; font: inherit; }
            .error { color: #b91c1c; }
            .danger-zone { padding: 16px; border: 1px solid #fecaca; background: #fef2f2; }
            .danger-zone button { background: #b91c1c; color: #fff; border: 0; }
        </style>
    </head>
    <body>
        <h1>プロフィール編集</h1>

        <form method="POST" action="{{ route('profile.update') }}">
            @csrf
            @method('PATCH')

            <div class="form-group">
                <label for="name">ユーザー名</label>
                <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required maxlength="30">
                @error('name')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="email">メールアドレス</label>
                <input id="email" name="email" type="email" value="{{ old('email', $user->email) }}" required>
                @error('email')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="age">年齢（任意）</label>
                <input id="age" name="age" type="number" value="{{ old('age', $user->age) }}" min="15" max="99" step="1">
                @error('age')<p class="error">{{ $message }}</p>@enderror
            </div>

            <div class="form-group">
                <label for="gender">性別</label>
                <select id="gender" name="gender" required>
                    <option value="男性" @selected(old('gender', $user->gender) === '男性')>男性</option>
                    <option value="女性" @selected(old('gender', $user->gender) === '女性')>女性</option>
                    <option value="回答しない" @selected(old('gender', $user->gender) === '回答しない')>回答しない</option>
                </select>
                @error('gender')<p class="error">{{ $message }}</p>@enderror
            </div>

            <button type="submit">保存する</button>
        </form>

        <section class="danger-zone">
            <h2>アカウント退会</h2>
            <p>退会するとログアウトします。退会後はログインできなくなります。</p>

            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')
                <label>
                    <input name="confirm_withdrawal" type="checkbox" value="1" @checked(old('confirm_withdrawal'))>
                    退会することに同意します。
                </label>
                @error('confirm_withdrawal')<p class="error">{{ $message }}</p>@enderror
                <button type="submit">退会する</button>
            </form>
        </section>

        <a href="{{ route('mypage') }}">マイページへ戻る</a>
    </body>
</html>
