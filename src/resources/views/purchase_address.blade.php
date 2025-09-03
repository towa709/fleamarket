@extends('layouts.app')

@section('show-header') 1 @endsection

@section('css')
<link rel="stylesheet" href="{{ asset('css/purchase_address.css') }}">
@endsection

<div class="page-header">
  <p class="page-title">送付先住所変更画面</p>
</div>

@section('content')
<div class="address-form-container">
  <h2 class="form-title">住所の変更</h2>

  <form action="{{ route('purchase.updateAddress', ['item_id' => $item->id]) }}" method="POST">
    @csrf

    <div class="form-group">
      <label for="postcode">郵便番号</label>
      <input type="text" name="postcode" id="postcode"
        value="{{ old('postcode', $user->postcode) }}">
      @error('postcode')
      <p class="error-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-group">
      <label for="address">住所</label>
      <input type="text" name="address" id="address"
        value="{{ old('address', $user->address) }}">
      @error('address')
      <p class="error-text">{{ $message }}</p>
      @enderror
    </div>

    <div class="form-group">
      <label for="building">建物名</label>
      <input type="text" name="building" id="building"
        value="{{ old('building', $user->building) }}">
    </div>

    <div class="form-actions">
      <button type="submit" class="btn-submit">更新する</button>
    </div>
  </form>
</div>
@endsection