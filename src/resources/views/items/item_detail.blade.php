@extends('layouts.app')

@section('show-header') 1 @endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/item_detail.css') }}">
@endsection

<div class="page-header">
  @if(Auth::check())
  <p class="page-title">商品詳細画面-ログイン後</p>
  @else
  <p class="page-title">商品詳細画面-ログイン前</p>
  @endif
</div>

@section('content')
<div class="item-detail-container">
  {{-- 左：商品画像 --}}
  <div class="item-detail-left">
    <div class="item-image-wrapper">
      @if ($item->transaction)
      <div class="sold-ribbon">SOLD</div>
      @endif

      @if ($item->img_url)
      @if (Str::startsWith($item->img_url, 'http'))
      <img src="{{ $item->img_url }}" alt="商品画像" class="item-image">
      @else
      <img src="{{ asset('storage/' . $item->img_url) }}" alt="商品画像" class="item-image">
      @endif
      @else
      <div class="no-image">商品画像</div>
      @endif
    </div>
  </div>

  {{-- 右：詳細 --}}
  <div class="item-detail-right">
    <h2 class="item-name">{{ $item->name }}</h2>
    @if (!empty($item->brand))
    <p class="brand-name">{{ $item->brand }}</p>
    @else
    <p class="brand-name">ブランド名未登録</p>
    @endif
    <p class="price">¥{{ number_format($item->price) }} <span class="tax">（税込）</span></p>
    <div class="actions">
      <div class="icons">
        <div class="icon-wrapper">
          @if(Auth::check())
          <span class="icon icon-favorite {{ $isFavorited ? 'favorited' : '' }}"
            data-item-id="{{ $item->id }}">
            {{ $isFavorited ? '★' : '☆' }}
          </span>
          @else
          <span class="icon icon-favorite-disabled {{ $isFavorited ? 'favorited' : '' }}">
            {{ $isFavorited ? '★' : '☆' }}
          </span>
          @endif
          <span class="count favorite-count">{{ $item->favorites_count }}</span>
        </div>

        <div class="icon-wrapper">
          <a href="#comments" class="comment-icon-link" aria-label="コメントへ移動">
            <svg class="icon icon-comment" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" role="img">
              <path d="M20.5 11.5c0 4.3-4.05 7.5-8.5 7.5c-1.36 0-2.63-.23-3.78-.68L4 20l1.66-4.13
               C4.61 14.51 3.5 13.08 3.5 11.5C3.5 7.2 7.55 4 12 4s8.5 3.2 8.5 7.5Z"
                fill="none" stroke="currentColor" stroke-width="2"
                stroke-linecap="round" stroke-linejoin="round" />
            </svg>
          </a>
          <span class="count comment-count">{{ $item->comments_count }}</span>
        </div>

      </div>
      {{-- 購入ボタン --}}
      @if ($item->transaction)
      {{-- SOLD時は押せない灰色ボタン --}}
      <button class="buy-button disabled" disabled>売り切れ</button>
      @else
      @if(Auth::check())
      <a href="{{ route('purchase.show', ['item_id' => $item->id]) }}" class="buy-button">購入手続きへ</a>
      @else
      <a href="{{ route('login') }}" class="buy-button">購入手続きへ</a>
      @endif
      @endif

    </div>

    <div class="section">
      <h3>商品説明</h3>
      <p>{!! nl2br(e($item->description)) !!}</p>
    </div>
    <div class="section">
      <h3>商品の情報</h3>
      <div class="info-row">
        <div class="info-label">カテゴリー</div>
        <div class="item-categories">
          @foreach ($item->categories as $category)
          <span class="category-tag">{{ $category->name }}</span>
          @endforeach
        </div>
      </div>

      <div class="info-row">
        <div class="info-label">商品の状態</div>
        <div class="info-value">{{ $item->condition }}</div>
      </div>
    </div>

    <div class="section" id="comments">
      <h3>コメント({{ $item->comments_count }})</h3>
      @foreach ($item->comments as $comment)
      <div class="comment">
        <div class="comment-user">
          @if($comment->user->profile_image)
          <img src="{{ asset('storage/' . $comment->user->profile_image) }}"
            alt="{{ $comment->user->name }}のプロフィール画像"
            class="profile-icon">
          @else
          {{-- プロフィール画像が無い場合はデフォルト画像 --}}
          <img src="{{ asset('images/default-profile.png') }}"
            alt="デフォルトプロフィール画像"
            class="profile-icon">
          @endif
          <strong>{{ $comment->user->name }}</strong>
        </div>
        <div class="comment-content">{{ $comment->content }}</div>
      </div>
      @endforeach
    </div>

    {{-- コメントフォーム --}}
    @if(Auth::check())
    <form action="{{ route('comments.store', ['item_id' => $item->id]) }}" method="POST" class="comment-form">
      @csrf
      <label for="comment" class="comment-label">商品へのコメント</label>
      <textarea id="comment" name="content" class="comment-textarea"></textarea>
      @error('content')
      <p class="error">{{ $message }}</p>
      @enderror
      <button type="submit" class="comment-button" {{ $item->transaction ? 'disabled' : '' }}>コメントを送信する</button>
    </form>
    @else
    <form action="{{ route('login') }}" method="GET" class="comment-form">
      <label for="comment" class="comment-label">商品へのコメント</label>
      {{-- 入力はできるが保存はされない --}}
      <textarea id="comment" name="content" class="comment-textarea"></textarea>
      {{-- ボタンを押すとログイン画面に飛ぶ --}}
      <button type="submit" class="comment-button">コメントを送信する</button>
    </form>
    @endif
  </div>
</div>
@endsection

@section('js')
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const favoriteBtn = document.querySelector('.icon-favorite[data-item-id]');

    if (favoriteBtn) {
      favoriteBtn.addEventListener('click', () => {
        const itemId = favoriteBtn.dataset.itemId;

        fetch(`/items/${itemId}/favorite`, {
            method: 'POST',
            headers: {
              'Content-Type': 'application/json',
              'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({})
          })
          .then(response => response.json())
          .then(data => {
            const countSpan = document.querySelector('.favorite-count');
            countSpan.textContent = data.count;

            if (data.status === 'added') {
              favoriteBtn.classList.add('favorited');
              favoriteBtn.textContent = '★';
            } else {
              favoriteBtn.classList.remove('favorited');
              favoriteBtn.textContent = '☆';
            }
          });
      });
    }
  });
</script>
@endsection