<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;

Route::post('/register', [RegisterController::class, 'store'])
  ->middleware(['guest'])
  ->name('register');

Route::middleware(['auth'])->group(function () {
  Route::get('/mypage/profile', [ProfileController::class, 'edit'])->name('profile.edit');
  Route::post('/mypage/profile', [ProfileController::class, 'update'])->name('profile.update');
  Route::get('/mypage', [ProfileController::class, 'mypage'])->name('mypage');
});

Route::get('/', [ItemController::class, 'index'])->name('items.index');

Route::get('/item/{id}', [ItemController::class, 'show'])->name('items.show');
Route::post('/items/{item_id}/comments', [ItemController::class, 'storeComment'])->name('comments.store');
Route::post('/items/{item}/favorite', [ItemController::class, 'toggleFavorite'])->name('favorites.toggle');

Route::middleware('auth')->group(function () {
  Route::get('/purchase/{item_id}', [PurchaseController::class, 'show'])->name('purchase.show');
  Route::get('/purchase/address/{item_id}', [PurchaseController::class, 'address'])->name('purchase.address');
  Route::post('/purchase/address/{item_id}', [PurchaseController::class, 'updateAddress'])->name('purchase.updateAddress');
  Route::post('/purchase/{item_id}', [PurchaseController::class, 'store'])->name('purchase.store');
  Route::get('/purchase/success/{item_id}', [PurchaseController::class, 'success'])->name('purchase.success');
});

// 決済画面
Route::post('/checkout', [App\Http\Controllers\PurchaseController::class, 'checkout'])->name('checkout');

Route::middleware(['auth', 'verified'])->group(function () {
  Route::get('/sell', [ItemController::class, 'create'])->name('items.create');
  Route::post('/sell', [ItemController::class, 'store'])->name('items.store');
});

// 認証メール送信後の案内画面
Route::get('/email/verify', function () {
  return view('auth.verify');
})->middleware('auth')->name('verification.notice');

// 認証リンクをクリックした後の処理
Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
  $request->fulfill();
  return redirect()->route('items.index'); // 商品一覧へ遷移
})->middleware(['auth', 'signed', 'throttle:6,1'])->name('verification.verify');

// 認証メール再送
Route::post('/email/verification-notification', function (Request $request) {
  $request->user()->sendEmailVerificationNotification();
  return back()->with('message', '認証メールを再送しました。');
})->middleware(['auth', 'throttle:6,1'])->name('verification.send');
