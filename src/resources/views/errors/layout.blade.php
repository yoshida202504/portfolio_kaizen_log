<!DOCTYPE html>
<html lang="ja">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title') | Kaizen Log</title>
        <link rel="stylesheet" href="{{ asset('css/kaizen-log.css') }}">
    </head>
    <body class="guest-body">
        <main class="guest-main">
            <a class="brand guest-brand" href="{{ url('/') }}">
                <span class="brand-mark" aria-hidden="true">✦</span>
                <span>Kaizen Log</span>
            </a>
            <section class="guest-card error-page">
                <p class="eyebrow">ERROR @yield('code')</p>
                <h1>@yield('title')</h1>
                <p>@yield('message')</p>
                <a class="button" href="{{ url('/') }}">自分の日報一覧へ戻る</a>
            </section>
        </main>
    </body>
</html>
