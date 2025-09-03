@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

<div class="page-header">
  <p class="page-title">会員登録画面</p>
</div>

@section('content')
<div class="container">
  <h1 class="register-title">会員登録</h1>
  <form method="POST" action="{{ route('register') }}">
    @csrf

    <div class="form-group">
      <label for="name">ユーザー名</label>
      <input type="text" id="name" name="name" value="{{ old('name') }}">
      @error('name')
      <p class="error">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-group">
      <label for="email">メールアドレス</label>
      <input
        type="text"
        id="email"
        name="email"
        value="{{ old('email') }}"
        inputmode="email"
        autocomplete="email">
      @error('email')
      <p class="error">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-group">
      <label for="password">パスワード</label>
      <input type="password" id="password" name="password">
      @error('password')
      <p class="error">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-group">
      <label for="password_confirmation">確認用パスワード</label>
      <input type="password" id="password_confirmation" name="password_confirmation">
      @error('password_confirmation')
      <p class="error">{{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="btn-login">登録する</button>
    <p class="register-link"><a href="{{ route('login') }}">ログインはこちら</a></p>
  </form>
</div>
@endsection