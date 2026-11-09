<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Kaizen Log | 振り返りを習慣化できる日報アプリ</title>
        <link rel="stylesheet" href="{{ asset('css/kaizen-log.css') }}">
    </head>
    <body class="top-body">
        <div class="top-page">
            <header class="top-header">
                <a class="brand" href="{{ route('top') }}">
                    <span class="brand-mark" aria-hidden="true">✦</span>
                    <span>Kaizen Log</span>
                </a>
            </header>

            <main>
                <section class="top-hero">
                    <h1>振り返りを習慣化できる日報アプリ</h1>
                    <p>改善策の結果を「想定」と比べて、A〜Dで振り返ろう</p>
                    <div class="top-actions">
                        <a class="button" href="{{ route('register') }}">新規登録</a>
                        <a class="button-secondary" href="{{ route('login') }}">ログイン</a>
                    </div>
                    <form class="top-guest" method="POST" action="{{ route('login.guest') }}">
                        @csrf
                        <button class="button-secondary" type="submit">ゲストとして試す（登録不要）</button>
                    </form>
                    @error('guest')<p class="field-error">{{ $message }}</p>@enderror
                </section>

                <section class="top-features" aria-label="主な機能">
                    <div>
                        <span aria-hidden="true">✉</span>
                        <strong>朝8時の通知</strong>
                        <p>改善結果の入力期限が近い日報をメールでお知らせ</p>
                    </div>
                    <div>
                        <span aria-hidden="true">A〜D</span>
                        <strong>想定と比べて評価</strong>
                        <p>改善策をやってみた結果を、想定と比べてA〜Dで記録</p>
                    </div>
                    <div>
                        <span aria-hidden="true">↗</span>
                        <strong>改善記録</strong>
                        <p>週ごとの改善行動数とA〜Dの内訳で、成長を確かめる</p>
                    </div>
                </section>

                <section class="top-cycle" aria-label="振り返りのサイクル">
                    改善策を考える → <em>結果を想定</em> → <em>実行</em> → <em>A〜Dで振り返る</em> → 次の改善策
                </section>
            </main>
        </div>
    </body>
</html>
