@extends('layouts.app')

@section('show-header') 1 @endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase_confirm.css') }}">
@endsection

<div class="page-header">
  <p class="page-title">商品購入画面</p>
</div>

@section('content')
<div class="purchase-container">
  <div class="purchase-left">
    <div class="product-info">
      @if ($item->img_url)
      @if (Str::startsWith($item->img_url, 'http'))
      <img src="{{ $item->img_url }}" alt="商品画像" class="item-img">
      @else
      <img src="{{ asset('storage/' . $item->img_url) }}" alt="商品画像" class="item-img">
      @endif
      @else
      <div class="no-image">商品画像</div>
      @endif
      <div class="product-details">
        <h2 class="product-name">{{ $item->name }}</h2>
        <p class="product-price">¥{{ number_format($item->price) }}</p>
      </div>
    </div>
    <div class="section-block payment-method">
      <h3 class="payment-title">支払い方法</h3>
      <div class="custom-select">
        <div class="selected placeholder">選択してください</div>
        <ul class="options">
          <li data-value="">選択してください</li>
          <li data-value="konbini">コンビニ払い</li>
          <li data-value="card">カード払い</li>
        </ul>
      </div>

      {{-- hidden input の name を payment_method に変更 --}}
      <input type="hidden" name="payment_method" id="payment-value" value="">

      {{-- エラーメッセージをセレクトの下に表示 --}}
      @error('payment_method')
      <p class="error">{{ $message }}</p>
      @enderror
    </div>

    <div class="section-block shipping-address">
      <h3 class="section-title">配送先</h3>
      <a href="{{ route('purchase.address', ['item_id' => $item->id]) }}" class="address-edit">変更する</a>
      <p class="shipping-text">
        {{ session('shipping_postcode', $user->postcode) }}<br>
        {{ session('shipping_address', $user->address) }}<br>
        {{ session('shipping_building', $user->building) }}
      </p>

      @error('address_id')
      <p class="error">{{ $message }}</p>
      @enderror
    </div>

    <div class="purchase-summary">
      <table>
        <tr>
          <td class="summary-label">商品代金</td>
          <td class="summary-value price">¥{{ number_format($item->price) }}</td>
        </tr>
        <tr>
          <td class="summary-label">支払い方法</td>
          <td class="summary-value payment">選択してください</td>
        </tr>
      </table>
      {{-- Stripeの決済画面に飛ばす --}}
      <form action="{{ route('purchase.store', ['item_id' => $item->id]) }}" method="POST">
        @csrf
        <input type="hidden" name="item_id" value="{{ $item->id }}">
        {{-- name を payment_method のまま送る --}}
        <input type="hidden" name="payment_method" id="payment-value-form" value="">
        <button type="submit" class="purchase-button">購入する</button>
      </form>
    </div>
  </div>
  @endsection

  @section('js')
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      const customSelects = document.querySelectorAll(".custom-select");

      customSelects.forEach(select => {
        const selected = select.querySelector(".selected");
        const options = select.querySelector(".options");
        const hiddenInput = select.querySelector("input[type='hidden']");
        const hiddenInputForm = document.getElementById("payment-value-form");
        const summaryPayment = document.querySelector(".summary-value.payment");

        // 初期状態は summaryPayment を空欄
        if (summaryPayment) summaryPayment.textContent = "";

        selected.addEventListener("click", () => {
          select.classList.toggle("open");
          options.style.display = select.classList.contains("open") ? "block" : "none";
        });

        options.querySelectorAll("li").forEach(option => {
          option.addEventListener("click", () => {
            options.querySelectorAll("li").forEach(li => li.classList.remove("selected"));
            option.classList.add("selected");

            selected.textContent = option.textContent;
            selected.classList.remove("placeholder");

            if (hiddenInput) hiddenInput.value = option.getAttribute("data-value");
            if (hiddenInputForm) hiddenInputForm.value = option.getAttribute("data-value");

            if (summaryPayment) summaryPayment.textContent = option.textContent;

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
    });
  </script>
  @endsection