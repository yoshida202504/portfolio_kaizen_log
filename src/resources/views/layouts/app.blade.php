<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title', 'Kaizen Log')</title>
        <link rel="stylesheet" href="{{ asset('css/kaizen-log.css') }}">
        <script src="{{ asset('js/navigation.js') }}" defer></script>
    </head>
    <body class="app-body">
        <div class="app-shell">
            <button id="mobile-menu-toggle" class="mobile-menu-toggle" type="button" aria-controls="app-sidebar" aria-expanded="false">
                <span aria-hidden="true">☰</span>
                <span>メニュー</span>
            </button>
            <button id="sidebar-backdrop" class="sidebar-backdrop" type="button" aria-label="メニューを閉じる" hidden></button>

            <aside id="app-sidebar" class="sidebar" aria-label="メインナビゲーション">
                <div class="sidebar-header">
                    <a class="brand" href="{{ route('home') }}" aria-label="Kaizen Log ホーム">
                        <span class="brand-mark" aria-hidden="true">✦</span>
                        <span class="nav-label">Kaizen Log</span>
                    </a>
                </div>

                <nav class="side-nav" aria-label="主要メニュー">
                    <details class="nav-group" @if (request()->routeIs('home', 'records.*')) open @endif>
                        <summary class="nav-group-summary" aria-label="日報メニューを開閉">
                            <span class="nav-icon" aria-hidden="true">▤</span>
                            <span class="nav-label">日報</span>
                            <span class="nav-disclosure" aria-hidden="true">⌄</span>
                        </summary>
                        <div class="nav-submenu">
                            <a class="{{ request()->routeIs('home', 'records.search') ? 'is-active' : '' }}" href="{{ route('home') }}" aria-label="自分の日報一覧" title="自分の日報一覧">
                                <span class="nav-icon" aria-hidden="true">▤</span>
                                <span class="nav-label">自分の日報一覧</span>
                            </a>
                            <a class="{{ request()->routeIs('records.create') ? 'is-active' : '' }}" href="{{ route('records.create') }}" aria-label="日報を作成" title="日報を作成">
                                <span class="nav-icon" aria-hidden="true">＋</span>
                                <span class="nav-label">日報を作成</span>
                            </a>
                        </div>
                    </details>
                    <a class="{{ request()->routeIs('community.*') ? 'is-active' : '' }}" href="{{ route('community.index') }}" aria-label="Community" title="Community">
                        <span class="nav-icon" aria-hidden="true">◌</span>
                        <span class="nav-label">Community</span>
                    </a>
                    <a class="{{ request()->routeIs('mypage') ? 'is-active' : '' }}" href="{{ route('mypage') }}" aria-label="マイページ" title="マイページ">
                        <span class="nav-icon" aria-hidden="true">◉</span>
                        <span class="nav-label">マイページ</span>
                    </a>
                </nav>

                <form class="logout-form" method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="logout-button" type="submit" title="ログアウト">
                        <span class="nav-icon" aria-hidden="true">↪</span>
                        <span class="nav-label">ログアウト</span>
                    </button>
                </form>
            </aside>

            <div id="sidebar-resizer" class="sidebar-resizer" role="separator" aria-controls="app-sidebar" aria-label="サイドバーの幅を調整" aria-orientation="vertical" aria-valuemin="220" aria-valuemax="360" aria-valuenow="260" tabindex="0"></div>

            <main class="main-content">
                @if (session('success'))
                    <div class="flash-success" role="status">{{ session('success') }}</div>
                @endif
                @yield('content')
            </main>
        </div>
    </body>
</html>
