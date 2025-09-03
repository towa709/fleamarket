@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/verify.css') }}">
@endsection

<div class="page-header">
  <p class="page-title">メール認証誘導画面</p>
</div>

@section('content')
<div class="verify-container">

  <div class="verify-message">
    <h2>
      登録していただいたメールアドレスに認証メールを送付しました。<br>
      メール内のリンクをクリックして、認証を完了してください。
    </h2>
  </div>

  <div>
    @if(session('verificationUrl'))
    <a href="{{ session('verificationUrl') }}" class="verify-button">
      認証はこちらから
    </a>
    @else
    <a href="#" class="verify-button" aria-disabled="true">認証はこちらから</a>
    @endif
  </div>

  <div>
    <form method="POST" action="{{ route('verification.send') }}">
      @csrf
      <button type="submit" class="verify-resend">
        認証メールを再送する
      </button>
      @if (session('message'))
      <p class="verify-alert">{{ session('message') }}</p>
      @endif
    </form>
  </div>

</div>
@endsection