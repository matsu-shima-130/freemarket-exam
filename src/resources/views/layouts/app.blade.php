<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>COACHTECH フリマアプリ</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/common.css') }}">
    @yield('css')
</head>
<body>
    <header class="header">
        <div class="header__inner">

            {{-- ロゴ部分 --}}
            <div class="header__logo">
                <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH ロゴ">
            </div>

            {{-- 現在のページが login または register の場合はヘッダー非表示 --}}
            @if (!Request::is('login') && !Request::is('register'))

                {{-- ログイン後（@auth） --}}
                @auth
                    <form class="header__search">
                        <input type="text" name="keyword" placeholder="なにをお探しですか？">
                    </form>
                    <nav class="header__nav">
                    <form method="POST" action="{{ route('logout') }}" class="logout-form">
                        @csrf
                        <button type="submit" class="header-link">ログアウト</button>
                    </form>

                    <a href="{{ route('mypage.index') }}" class="header-link">マイページ</a>
                    <a href="{{ route('sell.create') }}" class="btn-sell">出品</a>
                </nav>
                @endauth

                {{-- ログイン前（@guest） --}}
                @guest
                    <form class="header__search">
                        <input type="text" name="keyword" placeholder="なにをお探しですか？">
                    </form>
                    <nav class="header__nav">
                        <a href="/login">ログイン</a>
                        <a href="/register">マイページ</a>
                        <a href="#" class="btn-sell">出品</a>
                    </nav>
                @endguest
            @endif
        </div>
    </header>

    <main class="main container">
        @yield('content')
    </main>
</body>
</html>
