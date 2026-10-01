@extends('layouts.guest')
@section('title', '登録完了 | Kaizen Log')

@section('content')
    <section class="guest-card">
        <div class="complete-icon" aria-hidden="true">✓</div>
        <h1>ユーザー登録が完了しました。</h1>
        <p>アカウントの作成が完了し、現在はログイン済みです。今日の振り返りを、次の行動につなげていきましょう。</p>
        <div class="form-actions" style="justify-content: center;">
            <a class="button" href="{{ route('home') }}">自分の日報一覧へ</a>
        </div>
    </section>
@endsection
