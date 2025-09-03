@extends('layouts.app')

@php
use Illuminate\Support\Str;
@endphp

@section('show-header') 1 @endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/mypage.css') }}">
@endsection

<div class="page-header">
  <p class="page-title">プロフィール画面</p>
</div>

@section('content')

<div class="mypage-container">
  <div class="profile-area">

    {{-- 🔹 フラッシュメッセージ --}}
    @if (session('status'))
    <div class="flash-message" id="flash-message">
      {{ session('status') }}
    </div>
    @endif

    {{-- プロフィール画像 --}}
    @if ($user->profile_image)
    @if (Str::startsWith($user->profile_image, 'http'))
    <img src="{{ $user->profile_image }}" class="avatar">
    @else
    <img src="{{ asset('storage/' . $user->profile_image) }}" class="avatar">
    @endif
    @else
    <img src="{{ asset('images/default-avatar.png') }}" class="avatar">
    @endif

    <div class="username">{{ $user->name }}</div>
    <a href="{{ route('profile.edit') }}" class="edit-button">プロフィールを編集</a>
  </div>

  {{-- 🔹 タブメニュー --}}
  <div class="tab-menu">
    {{-- 出品した商品タブ --}}
    <a href="{{ route('mypage', ['page' => 'sell']) }}"
      class="tab tab-sell {{ $page === 'sell' || $page === null ? 'active' : '' }}">
      出品した商品
    </a>

    {{-- 購入した商品タブ --}}
    <a href="{{ route('mypage', ['page' => 'buy']) }}"
      class="tab tab-buy {{ $page === 'buy' ? 'active' : '' }}">
      購入した商品
    </a>
  </div>
  {{-- 出品した商品 --}}
  @if ($page === 'sell')
  <div class="tab-content">
    <div class="item-list">
      @foreach ($sales as $item)
      <div class="item-card">
        <div class="item-image" style="position: relative;">
          {{-- 🔹 SOLDリボン --}}
          @if ($item->is_sold)
          <div class="sold-ribbon">SOLD</div>
          @endif

          @if ($item->img_url)
          @if (Str::startsWith($item->img_url, 'http'))
          <img src="{{ $item->img_url }}" alt="商品画像" class="item-img">
          @else
          <img src="{{ asset('storage/' . $item->img_url) }}" alt="商品画像" class="item-img">
          @endif
          @else
          <img src="{{ asset('images/no-image.png') }}" alt="商品画像" class="item-img">
          @endif
        </div>
        <div class="item-name">{{ $item->name }}</div>
      </div>
      @endforeach
    </div>
  </div>
  @endif


  {{-- 購入した商品 --}}
  @if ($page === 'buy')
  <div class="tab-content">
    <div class="item-list">
      @foreach ($purchases as $transaction)
      @if ($transaction->item)
      <div class="item-card">
        <div class="item-image" style="position: relative;">
          {{-- 🔹 SOLDリボン --}}
          @if ($transaction->item->is_sold)
          <div class="sold-ribbon">SOLD</div>
          @endif

          @if ($transaction->item->img_url)
          @if (Str::startsWith($transaction->item->img_url, 'http'))
          <img src="{{ $transaction->item->img_url }}" alt="商品画像" class="item-img">
          @else
          <img src="{{ asset('storage/' . $transaction->item->img_url) }}" alt="商品画像" class="item-img">
          @endif
          @else
          <img src="{{ asset('images/no-image.png') }}" alt="商品画像" class="item-img">
          @endif
        </div>
        <div class="item-name">{{ $transaction->item->name }}</div>
      </div>
      @endif
      @endforeach
    </div>
  </div>
  @endif
</div>
@endsection

@section('js')
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const flash = document.getElementById("flash-message");
    if (flash) {
      setTimeout(() => {
        flash.classList.add("hide");
        setTimeout(() => flash.remove(), 500);
      }, 3000);
    }
  });
</script>
@endsection