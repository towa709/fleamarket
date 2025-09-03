<!DOCTYPE html>
<html lang="ja">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>@yield('subtitle', 'coachtechフリマ')</title>
  <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
  <link rel="stylesheet" href="{{ asset('css/common.css') }}">
  @yield('css')
</head>

<body>
  {{-- ヘッダー（ログイン/会員登録/メール認証誘導ページではロゴのみ） --}}
  <header class="header">
    {{-- 左：ロゴ（常に表示） --}}
    <div class="header-left">
      <a href="{{ route('items.index') }}">
        <img src="{{ asset('images/logo.svg') }}" alt="COACHTECH ロゴ" class="logo">
      </a>
    </div>

    {{-- 中央：検索ボックス（ログイン前でも常に表示。ただし login/register/verify では非表示） --}}
    @if(!in_array(Route::currentRouteName(), ['login', 'register', 'verification.notice']))
    <div class="header-center">
      <form method="GET" action="{{ url('/') }}" class="search-form">
        <input
          type="text"
          name="keyword"
          class="search-box"
          placeholder="なにをお探しですか？"
          value="{{ request('keyword') }}">
        <input type="hidden" name="tab" id="tabInput" value="">
        <button type="submit" class="search-submit" aria-label="検索"></button>
      </form>
    </div>
    @endif

    {{-- 右：メニュー --}}
    <div class="header-right">
      @if(!in_array(Route::currentRouteName(), ['login', 'register', 'verification.notice']))
      @if(Auth::check())
      {{-- ログイン後 --}}
      <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit" class="menu-button">ログアウト</button>
      </form>
      <a href="{{ route('mypage') }}" class="menu-button">マイページ</a>
      <a href="{{ route('items.create') }}" class="menu-button listing-button">出品</a>
      @else
      {{-- ログイン前 --}}
      <a href="{{ route('login') }}" class="menu-button">ログイン</a>
      <a href="{{ route('login') }}" class="menu-button">マイページ</a>
      <a href="{{ route('login') }}" class="menu-button listing-button">出品</a>
      @endif
      @endif
    </div>

  </header>
  {{-- コンテンツ表示 --}}
  @yield('content')
  @yield('js')
</body>

</html>