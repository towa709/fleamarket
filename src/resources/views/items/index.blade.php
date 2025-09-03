@extends('layouts.app')

@section('show-header') 1 @endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/item.css') }}">
@endsection

<div class="page-header">
  @if(Auth::check())
  <p class="page-title">商品一覧画面（トップ）-ログイン後</p>
  @else
  <p class="page-title">商品一覧画面（トップ）-ログイン前</p>
  @endif
</div>

@section('content')
<div class="tab-wrapper">
  <div class="tab-menu">
    <span class="tab {{ request()->get('tab') == 'mylist' ? '' : 'active' }}"
      data-tab="recommend-items"
      data-url="{{ url('/') }}">
      おすすめ
    </span>
    <span class="tab {{ request()->get('tab') == 'mylist' ? 'active' : '' }}"
      data-tab="favorite-items"
      data-url="{{ url('/?tab=mylist') }}"
      data-auth="{{ Auth::check() ? '1' : '0' }}">
      マイリスト
    </span>
  </div>

  {{-- おすすめ --}}
  <div class="tab-content {{ request()->get('tab') == 'mylist' ? 'hide' : 'show' }}" id="recommend-items">
    <div class="item-list">
      @foreach ($items as $item)
      <a href="{{ route('items.show', ['id' => $item->id]) }}">
        <div class="item-card">
          <div class="item-image-wrapper">
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
            <div class="no-image">No Image</div>
            @endif
          </div>
          <p class="item-name">{{ $item->name }}</p>
        </div>
      </a>
      @endforeach
    </div>
  </div>

  {{-- マイリスト --}}
  <div class="tab-content {{ request()->get('tab') == 'mylist' ? 'show' : 'hide' }}" id="favorite-items">
    <div class="item-list">
      @if (!Auth::check())
      {{-- 未ログインなら空表示 --}}
      @elseif ($favoriteItems->isEmpty())
      <p class="favorite-empty-message">お気に入り商品はまだありません。</p>
      @else
      @foreach ($favoriteItems as $item)
      <a href="{{ route('items.show', ['id' => $item->id]) }}" class="item-card-link">
        <div class="item-card">
          <div class="item-image-wrapper">
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
            <div class="no-image">No Image</div>
            @endif
          </div>
          <p class="item-name">{{ $item->name }}</p>
        </div>
      </a>
      @endforeach
      @endif
    </div>
  </div>
</div>
@endsection

@section('js')
<script>
  document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.tab');
    const contents = document.querySelectorAll('.tab-content');
    const tabInput = document.getElementById('tabInput');

    // 起動時：URLのクエリからtabを反映（/ ?tab=mylist なら mylist）
    const paramsOnLoad = new URLSearchParams(window.location.search);
    const currentTab = paramsOnLoad.get('tab');
    tabInput.value = currentTab || '';

    tabs.forEach(tab => {
      tab.addEventListener('click', () => {
        const targetId = tab.dataset.tab;
        let targetUrl = tab.dataset.url;

        // keywordの引き継ぎ
        const params = new URLSearchParams(window.location.search);
        const keyword = params.get('keyword');
        if (keyword) {
          const hasQuery = targetUrl.includes('?');
          targetUrl += (hasQuery ? '&' : '?') + 'keyword=' + encodeURIComponent(keyword);
        }

        // URLだけ更新
        window.history.pushState({}, '', targetUrl);

        // ★ hiddenのtabも更新（favorite-itemsならmylist、それ以外は空）
        tabInput.value = (targetId === 'favorite-items') ? 'mylist' : '';

        // 見た目の切替
        tabs.forEach(t => t.classList.remove('active'));
        tab.classList.add('active');

        contents.forEach(content => {
          content.classList.add('hide');
          content.classList.remove('show');
        });
        const targetContent = document.getElementById(targetId);
        targetContent.classList.remove('hide');
        targetContent.classList.add('show');
      });
    });
  });
  window.addEventListener('pageshow', function(event) {
    if (event.persisted || (window.performance && window.performance.getEntriesByType("navigation")[0].type === "back_forward")) {
      window.location.reload();
    }
  });
</script>
@endsection