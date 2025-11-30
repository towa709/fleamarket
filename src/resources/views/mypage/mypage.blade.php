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

    @if (session('status'))
    <div class="flash-message" id="flash-message">
      {{ session('status') }}
    </div>
    @endif

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

    @if (!is_null($evaluationAvg))
    <div class="star-rating" data-score="{{ $evaluationAvg }}">
        @for ($i = 1; $i <= 5; $i++)
            <span class="star {{ $i <= $evaluationAvg ? 'filled' : '' }}">★</span>
        @endfor
    </div>
    @endif
  </div>

  <div class="tab-menu">
    <a href="{{ route('mypage', ['page' => 'sell']) }}"
      class="tab tab-sell {{ $page === 'sell' || $page === null ? 'active' : '' }}">
      出品した商品
    </a>

    <a href="{{ route('mypage', ['page' => 'buy']) }}"
      class="tab tab-buy {{ $page === 'buy' ? 'active' : '' }}">
      購入した商品
    </a>

    <a href="{{ route('mypage', ['page' => 'progress']) }}"
       class="tab tab-progress {{ $page === 'progress' ? 'active' : '' }}">
      取引中の商品
      <span id="unread-total-badge"
            class="badge"
            style="{{ ($unreadTotal ?? 0) > 0 ? '' : 'display:none;' }}">
            {{ $unreadTotal ?? '' }}
      </span>
    </a>
  </div>

  @if ($page === 'sell')
  <div class="tab-content">
    <div class="item-list">
      @foreach ($sales as $item)
      <div class="item-card">
        <div class="item-image" style="position: relative;">

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

  @if ($page === 'buy')
  <div class="tab-content">
    <div class="item-list">
      @foreach ($purchases as $transaction)
      @if ($transaction->item)
      <div class="item-card">
        <div class="item-image" style="position: relative;">

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

@if ($page === 'progress')
<div class="tab-content">
  <div class="item-list">
    @foreach ($progress as $transaction)
    @if ($transaction->item)

      <a href="{{ route('chat.show', ['transaction_id' => $transaction->id]) }}" class="item-link">
        <div class="item-card">
          <div class="item-image" style="position: relative;">

            <div class="unread-badge"
                 id="badge-{{ $transaction->id }}"
                 style="{{ $transaction->unread_count > 0 ? '' : 'display:none;' }}">
                {{ $transaction->unread_count }}
            </div>

            @if (Str::startsWith($transaction->item->img_url, 'http'))
              <img src="{{ $transaction->item->img_url }}" class="item-img">
            @else
              <img src="{{ asset('storage/' . $transaction->item->img_url) }}" class="item-img">
            @endif

          </div>
          <div class="item-name">{{ $transaction->item->name }}</div>
        </div>
      </a>

    @endif
    @endforeach
  </div>
</div>
@endif

@endsection

@section('js')
<script>
document.addEventListener("DOMContentLoaded", () => {

    const isProgressPage = window.location.href.includes('page=progress');

    async function reloadProgressList() {
        if (!isProgressPage) return;

        const res = await fetch('/api/mypage/progress-list');
        const list = await res.json();

        const container = document.querySelector('.item-list');
        container.innerHTML = '';

        list.forEach(t => {
            container.insertAdjacentHTML('beforeend', `
                <a href="/chat/${t.transaction_id}" class="item-link">
                    <div class="item-card">
                        <div class="item-image" style="position: relative;">
                            <div class="unread-badge"
                                 id="badge-${t.transaction_id}"
                                 style="${t.unread_count > 0 ? '' : 'display:none;'}">
                                 ${t.unread_count}
                            </div>
                            <img src="${t.item_image}" class="item-img">
                        </div>
                        <div class="item-name">${t.item_name}</div>
                    </div>
                </a>
            `);
        });
    }

    async function updateTotalBadge() {
        const res = await fetch('/api/notifications/unread-total');
        const data = await res.json();

        const badge = document.getElementById("unread-total-badge");

        if (data.unread_total > 0) {
            badge.textContent = data.unread_total;
            badge.style.display = "inline-block";
        } else {
            badge.style.display = "none";
        }
    }

    async function updateItemBadges() {
        if (!isProgressPage) return;

        const res = await fetch('/api/notifications/unread-list');
        const list = await res.json();

        list.forEach(item => {
            const badge = document.querySelector(`#badge-${item.transaction_id}`);

            if (badge) {
                if (item.unread_count > 0) {
                    badge.textContent = item.unread_count;
                    badge.style.display = "block";
                } else {
                    badge.style.display = "none";
                }
            }
        });
    }

    updateTotalBadge();
    updateItemBadges();
    reloadProgressList();

    setInterval(() => {
        updateTotalBadge();
        updateItemBadges();
        reloadProgressList();
    }, 2000);

});
</script>

<script>
  const flash = document.getElementById("flash-message");
  if (flash) {
    setTimeout(() => {
      flash.classList.add("hide");
      setTimeout(() => flash.remove(), 500);
    }, 3000);
  }
</script>
@endsection
