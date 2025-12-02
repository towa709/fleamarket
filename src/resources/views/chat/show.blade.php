@extends('layouts.app')

@section('css')
<link rel="stylesheet" href="{{ asset('css/chat.css') }}">
@endsection

@section('show-header') 1 @endsection

@section('content')

<div id="evaluation-modal" class="evaluation-modal {{ $shouldEvaluate ? '' : 'hide' }}">
    <div class="modal-content">
        <p class="modal-title">取引が完了しました。</p>
        <p class="modal-subtitle">今回の取引相手はどうでしたか？</p>

        <div class="star-select">
            <span data-score="1">★</span>
            <span data-score="2">★</span>
            <span data-score="3">★</span>
            <span data-score="4">★</span>
            <span data-score="5">★</span>
        </div>

        <button id="send-evaluation" class="send-btn">送信する</button>
    </div>
</div>

<div class="chat-wrapper">

  <aside class="chat-sidebar">
    <h2 class="sidebar-title">その他の取引</h2>
    <ul class="sidebar-list" id="transaction-list"></ul>
  </aside>

  <section class="chat-main">

    <div class="chat-header">
      <img src="" id="partner-image" class="partner-icon" alt="">
      <div class="partner-info">
        <span id="partner-name"></span> さんとの取引画面
      </div>

      @if ($transaction->buyer_id === Auth::id())
      <button class="finish-button" id="finish-transaction">取引を完了する</button>
      @endif
    </div>

    <div class="product-box">
      <img src="" id="product-image" class="product-img" alt="">
      <div class="product-detail">
        <h3 id="product-name"></h3>
        <p id="product-price"></p>
      </div>
    </div>

    <div class="message-area" id="message-area"></div>

    <div id="chat-error-box" class="chat-error-box"></div>

    <form id="chat-form" enctype="multipart/form-data">

      <input type="file" name="image" id="image-input" style="display:none">

      <div class="input-area">

        <div class="input-wrapper">
          <div id="image-preview" class="image-preview" style="display:none;">
            <img id="preview-img" class="preview-img" alt="">
          </div>

          <textarea
             name="message"
             id="message-input"
             placeholder="取引メッセージを記入してください"
          ></textarea>

          </div>

        <button type="button" id="add-image" class="add-image-button">
          画像を追加
        </button>

        <button type="submit" id="send-message" class="send-button">
          <img src="{{ asset('images/send-icon.png') }}" class="send-icon" alt="">
        </button>
      </div>

    </form>

    <div id="delete-modal" class="delete-modal hide">
      <div class="delete-modal-content">
        <p class="delete-message">本当に削除しますか？</p>
        <div class="delete-buttons">
          <button id="delete-cancel" class="delete-cancel">キャンセル</button>
          <button id="delete-confirm" class="delete-confirm">削除する</button>
        </div>
      </div>
    </div>

  </section>

</div>

@endsection

@section('js')
<script>
  window.TRANSACTION_ID = @json($transaction_id);
  window.AUTH_USER_ID = @json(Auth::id());
  window.AUTH_USER_NAME = @json(Auth::user()->name);
  window.PRODUCT = {
    image: @json($item->img_url),
    name: @json($item->name),
    price: @json($item->price),
  };
  window.SHOULD_EVALUATE = @json($shouldEvaluate ?? false);
  window.AUTH_USER_IMAGE = @json(
    Auth::user()->profile_image
      ? asset('storage/' . Auth::user()->profile_image)
      : asset('images/default-avatar.png')
  );
</script>
<script src="{{ asset('js/chat.js') }}"></script>
<script>
  document.getElementById('add-image').addEventListener('click', () => {
    document.getElementById('image-input').click();
  });
</script>
@endsection
