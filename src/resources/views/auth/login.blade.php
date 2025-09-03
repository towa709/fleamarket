@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

<div class="page-header">
  <p class="page-title">ログイン画面</p>
</div>

@section('content')
<div class="container">
  <h1 class="login-title">ログイン</h1>
  <form method="POST" action="{{ route('login') }}">
    @csrf
    <div class="form-group">
      <label for="email">メールアドレス</label>
      <input type="text" id="email" name="email" value="{{ old('email') }}">
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

    <button type="submit" class="btn-login">ログインする</button>

    @error('email-login')
    <p class="error" style="text-align:center; margin-top:1rem;">
      {{ $message }}
    </p>
    @enderror

    <p class="register-link"><a href="{{ route('register') }}">会員登録はこちら</a></p>
  </form>
</div>
@endsection