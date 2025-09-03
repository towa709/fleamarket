@extends('layouts.app')

@section('show-header') 1 @endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/item_create.css') }}">
@endsection

<div class="page-header">
  <p class="page-title">商品出品画面</p>
</div>

@section('content')
<div class="item-create-container">
  <h2 class="form-title">商品の出品</h2>

  <form action=" {{ route('items.store') }}" method="POST" enctype="multipart/form-data">
    @csrf

    {{-- 商品画像 --}}
    <div class="form-group">
      <label for="image">商品画像</label>
      <div class="image-upload-box">
        <label for="image" class="image-label">画像を選択する</label>
        <input type="file" name="image" id="image" class="image-input" accept="image/*">

        {{-- プレビュー表示エリア（枠内に移動） --}}
        <div id="image-preview" class="image-preview">
          @if(old('image'))
          <img src="{{ old('image') }}" alt="プレビュー">
          @endif
        </div>
      </div>
      @error('image')
      <p class="error">{{ $message }}</p>
      @enderror
    </div>

    <div class="section-block">
      <h3 class="section-title">商品詳細</h3>
      <hr>
    </div>

    {{-- 商品カテゴリー --}}
    <div class="form-group">
      <label>カテゴリー</label>
      <div class="category-list">
        @foreach ($categories as $category)
        <input
          type="checkbox"
          id="category_{{ $category->id }}"
          name="category_id[]"
          value="{{ $category->id }}"
          class="category-checkbox"
          {{ is_array(old('category_id')) && in_array($category->id, old('category_id')) ? 'checked' : '' }}>
        <label for="category_{{ $category->id }}" class="category-label">
          {{ $category->name }}
        </label>
        @endforeach
      </div>
      @error('category_id')
      <p class="error">{{ $message }}</p>
      @enderror
    </div>

    {{-- 商品の状態 --}}
    <div class="form-group">
      <label for="condition">商品の状態</label>
      <div class="custom-select" id="conditionSelect">
        {{-- 初期表示用 --}}
        <div class="selected placeholder">
          {{ old('condition') ?: '選択してください' }}
        </div>
        <ul class="options">
          {{-- 選択してくださいもリストに追加 --}}
          <li data-value="">選択してください</li>
          <li data-value="良好">良好</li>
          <li data-value="目立った傷や汚れなし">目立った傷や汚れなし</li>
          <li data-value="やや傷や汚れあり">やや傷や汚れあり</li>
          <li data-value="状態が悪い">状態が悪い</li>
        </ul>
      </div>
      <input type="hidden" name="condition" id="condition" value="{{ old('condition') }}">
      @error('condition')
      <p class="error">{{ $message }}</p>
      @enderror
    </div>

    {{-- 商品名 --}}
    <div class="form-group">
      <label for="name">商品名</label>
      <input type="text" name="name" id="name" maxlength="255" value="{{ old('name') }}">
      @error('name')
      <p class="error">{{ $message }}</p>
      @enderror
    </div>

    {{-- ブランド名 --}}
    <div class="form-group">
      <label for="brand">ブランド名</label>
      <input type="text" name="brand" id="brand" value="{{ old('brand') }}">
    </div>

    {{-- 商品説明 --}}
    <div class="form-group">
      <label for="description">商品の説明</label>
      <textarea name="description" id="description">{{ old('description') }}</textarea>
      @error('description')
      <p class="error">{{ $message }}</p>
      @enderror
    </div>

    {{-- 価格 --}}
    <div class="form-group">
      <label for="price">販売価格</label>
      <div class="price-field">
        <span>¥</span>
        <input type="text" name="price" id="price" value="{{ old('price') }}">
      </div>
      @error('price')
      <p class="error">{{ $message }}</p>
      @enderror
    </div>

    <button type="submit" class="btn-submit">出品する</button>
  </form>
</div>
@endsection

@section('js')
<script>
  document.addEventListener("DOMContentLoaded", function() {
    const customSelects = document.querySelectorAll(".custom-select");

    customSelects.forEach(select => {
      const selected = select.querySelector(".selected");
      const options = select.querySelector(".options");
      const hiddenInput = select.parentElement.querySelector("input[type='hidden']");

      selected.addEventListener("click", () => {
        select.classList.toggle("open");
        options.style.display = select.classList.contains("open") ? "block" : "none";
      });

      options.querySelectorAll("li").forEach(option => {
        option.addEventListener("click", () => {
          options.querySelectorAll("li").forEach(li => li.classList.remove("selected"));
          option.classList.add("selected");

          selected.textContent = option.textContent;
          selected.classList.toggle("placeholder", option.getAttribute("data-value") === "");

          if (hiddenInput) hiddenInput.value = option.getAttribute("data-value");

          select.classList.remove("open");
          options.style.display = "none";
        });
      });

      document.addEventListener("click", (e) => {
        if (!select.contains(e.target)) {
          select.classList.remove("open");
          options.style.display = "none";
        }
      });
    });

    // ===== 商品画像プレビュー処理 =====
    const imageInput = document.getElementById("image");
    const preview = document.getElementById("image-preview");

    imageInput.addEventListener("change", function(event) {
      const file = event.target.files[0];
      if (file) {
        const reader = new FileReader();
        reader.onload = function(e) {
          preview.innerHTML = "";
          const img = document.createElement("img");
          img.src = e.target.result;
          img.classList.add("preview-img");
          preview.appendChild(img);
        }
        reader.readAsDataURL(file);
      }
    });
  });
</script>
@endsection