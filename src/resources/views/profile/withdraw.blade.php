@extends('layouts.app')

@section('title', '退会の確認 | Kaizen Log')

@section('content')
    <header class="page-header">
        <div><p class="eyebrow">WITHDRAW</p><h1>退会の確認</h1><p class="page-lead">退会する前に、以下の内容をご確認ください。</p></div>
        <a class="button-secondary" href="{{ route('profile.edit') }}">プロフィール編集へ戻る</a>
    </header>

    <section class="card form-card danger-zone">
        <h2>退会すると、次のようになります</h2>
        <ul>
            <li>ログアウトし、同じアカウントではログインできなくなります</li>
            <li>同じメールアドレス・ユーザー名で、もう一度登録することはできません</li>
            <li>書いた日報は、ほかのユーザーには表示されなくなります</li>
            <li>書いたコメントも、ほかのユーザーには表示されなくなります</li>
        </ul>
        <form method="POST" action="{{ route('profile.destroy') }}">
            @csrf @method('DELETE')
            <label class="check-label"><input name="confirm_withdrawal" type="checkbox" value="1" @checked(old('confirm_withdrawal'))> 上記を確認し、退会することに同意します。</label>
            @error('confirm_withdrawal')<p class="field-error">{{ $message }}</p>@enderror
            <div class="form-actions">
                <button class="button-danger" type="submit">退会する</button>
                <a class="button-secondary" href="{{ route('profile.edit') }}">戻る</a>
            </div>
        </form>
    </section>
@endsection
