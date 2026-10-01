<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Kaizen Log')</title>
        <link rel="stylesheet" href="{{ asset('css/kaizen-log.css') }}">
    </head>
    <body class="guest-body">
        <main class="guest-main">
            <a class="brand guest-brand" href="{{ route('login') }}">
                <span class="brand-mark" aria-hidden="true">✦</span>
                <span>Kaizen Log</span>
            </a>
            @yield('content')
        </main>
    </body>
</html>
