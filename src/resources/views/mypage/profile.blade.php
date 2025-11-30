@extends('layouts.app')

@section('show-header') 1 @endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/profile.css') }}">
@endsection

<div class="page-header">
  @if(session('first_login'))
  <p class="page-title">プロフィール設定画面_初回ログイン時</p>
  @else
  <p class="page-title">プロフィール編集画面</p>
  @endif
</div>

@section('content')
<div class="container">
  <h2 class="profile-title">プロフィール設定</h2>
  <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <div class="profile-image-area">
      <div class="profile-avatar" id="avatar-preview">
        @if(Auth::user()->profile_image ?? false)
        <img src="{{ asset('storage/' . Auth::user()->profile_image) }}" id="preview-img">
        @else
        <img src="{{ asset('images/avatar-placeholder.png') }}" id="preview-img">
        @endif
      </div>
      <label class="file-button">
        画像を選択する
        <input type="file" name="image" id="image-input" accept="image/*">
      </label>
      @error('image') <p class="error">{{ $message }}</p> @enderror
    </div>

    {{-- 入力フォーム --}}
    <div class="form-group">
      <label for="name">ユーザー名</label>
      <input id="name" type="text" name="name" value="{{ old('name', Auth::user()->name) }}">
      @error('name') <p class="error">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
      <label for="postcode">郵便番号</label>
      <input id="postcode" type="text" name="postcode" value="{{ old('postcode', Auth::user()->postcode) }}">
      @error('postcode') <p class="error">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
      <label for="address">住所</label>
      <input id="address" type="text" name="address" value="{{ old('address', Auth::user()->address) }}">
      @error('address') <p class="error">{{ $message }}</p> @enderror
    </div>

    <div class="form-group">
      <label for="building">建物名</label>
      <input id="building" type="text" name="building" value="{{ old('building', Auth::user()->building) }}">
      @error('building') <p class="error">{{ $message }}</p> @enderror
    </div>

    <button type="submit" class="btn-primary">更新する</button>
  </form>
</div>
@endsection

@section('js')
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const input = document.getElementById("image-input");
    const previewImg = document.getElementById("preview-img");

    input.addEventListener("change", function(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          previewImg.src = e.target.result; 
        }
        reader.readAsDataURL(file);
      }
    });
  });
</script>
@endsection