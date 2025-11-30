<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use App\Models\Transaction;
use App\Models\Item;
use App\Models\Evaluation;
use Illuminate\Support\Str;

class ProfileController extends Controller
{
  public function edit()
  {
    return view('mypage.profile');
  }

  public function update(ProfileRequest $request)
  {
    $user = Auth::user();

    $user->name = $request->name;
    $user->postcode = $request->postcode;
    $user->address = $request->address;
    $user->building = $request->building;

    if ($request->hasFile('image')) {
      $user->profile_image = $request->file('image')->store('profiles', 'public');
    }

    $user->profile_completed = true;

    $user->save();

    session()->forget('first_login');

    return redirect('mypage/profile');
  }

  public function mypage(Request $request)
  {
    $user = Auth::user();
    $page = $request->query('page', 'sell');

    if ($request->has('success')) {
      session()->flash('status', '購入が完了しました');
    }

    $sales = Item::where('user_id', $user->id)->get();

    $soldItems = Transaction::where('seller_id', $user->id)
      ->with('item')
      ->get();

    $purchases = Transaction::where('buyer_id', $user->id)
      ->with('item')
      ->get();

    $buyerProgress = Transaction::where('buyer_id', $user->id)
      ->whereNull('buyer_completed_at')
      ->with('item')
      ->leftJoin('messages', 'transactions.id', '=', 'messages.transaction_id')
      ->select('transactions.*')
      ->selectRaw('MAX(messages.created_at) as last_message_time')
      ->groupBy('transactions.id')
      ->get();

    $sellerProgress = Transaction::where('seller_id', $user->id)
      ->whereNull('seller_completed_at')
      ->with('item')
      ->leftJoin('messages', 'transactions.id', '=', 'messages.transaction_id')
      ->select('transactions.*')
      ->selectRaw('MAX(messages.created_at) as last_message_time')
      ->groupBy('transactions.id')
      ->get();

    $progress = $buyerProgress->merge($sellerProgress)
      ->sortByDesc('last_message_time')
      ->values();

    foreach ($progress as $transaction) {
      $transaction->unread_count = \App\Models\Message::where('transaction_id', $transaction->id)
        ->where('user_id', '!=', $user->id)
        ->where('is_read', false)
        ->count();
    }

    $unreadTotal = $progress->sum('unread_count');

    $evaluationAvg = Evaluation::where('evaluated_id', $user->id)->avg('score');
    $evaluationAvg = $evaluationAvg ? round($evaluationAvg) : null;

    return view('mypage.mypage', [
      'user' => $user,
      'sales' => $sales,
      'soldItems' => $soldItems,
      'purchases' => $purchases,
      'page' => $page,
      'progress' => $progress,
      'unreadTotal' => $unreadTotal,
      'evaluationAvg' => $evaluationAvg,
    ]);
  }

  public function unreadTotal()
  {
    $userId = Auth::id();

    $buyerProgress = Transaction::where('buyer_id', $userId)
      ->whereNull('buyer_completed_at')
      ->get();

    $sellerProgress = Transaction::where('seller_id', $userId)
      ->whereNull('seller_completed_at')
      ->get();

    $progress = $buyerProgress->merge($sellerProgress);

    $total = 0;

    foreach ($progress as $transaction) {
      $count = \App\Models\Message::where('transaction_id', $transaction->id)
        ->where('user_id', '!=', $userId)
        ->where('is_read', false)
        ->count();

      $total += $count;
    }

    return response()->json(['unread_total' => $total]);
  }

  public function unreadList()
  {
    $userId = Auth::id();

    $buyerProgress = Transaction::where('buyer_id', $userId)
      ->whereNull('buyer_completed_at')
      ->get();

    $sellerProgress = Transaction::where('seller_id', $userId)
      ->whereNull('seller_completed_at')
      ->get();

    $progress = $buyerProgress->merge($sellerProgress);

    $list = [];

    foreach ($progress as $transaction) {
      $count = \App\Models\Message::where('transaction_id', $transaction->id)
        ->where('user_id', '!=', $userId)
        ->where('is_read', false)
        ->count();

      $list[] = [
        'transaction_id' => $transaction->id,
        'unread_count'   => $count,
      ];
    }

    return response()->json($list);
  }

  public function progressList()
  {
    $userId = Auth::id();

    $buyerProgress = Transaction::where('buyer_id', $userId)
      ->whereNull('buyer_completed_at')
      ->with('item')
      ->get();

    $sellerProgress = Transaction::where('seller_id', $userId)
      ->whereNull('seller_completed_at')
      ->with('item')
      ->get();

    $progress = $buyerProgress->merge($sellerProgress);

    $result = [];

    foreach ($progress as $transaction) {
      $latest = \App\Models\Message::where('transaction_id', $transaction->id)
        ->orderByDesc('created_at')
        ->first();

      $lastTime = $latest ? $latest->created_at : null;

      $unread = \App\Models\Message::where('transaction_id', $transaction->id)
        ->where('user_id', '!=', $userId)
        ->where('is_read', false)
        ->count();

      $result[] = [
        'transaction_id' => $transaction->id,
        'item_name'      => $transaction->item ? $transaction->item->name : '商品名なし',
        'item_image'     => ($transaction->item && $transaction->item->img_url)
          ? (Str::startsWith($transaction->item->img_url, 'http')
            ? $transaction->item->img_url
            : asset('storage/' . $transaction->item->img_url))
          : asset('images/no-image.png'),
        'unread_count'   => $unread,
        'last_time'      => $lastTime,
      ];
    }

    usort($result, function ($a, $b) {
      return strtotime($b['last_time']) <=> strtotime($a['last_time']);
    });

    return response()->json($result);
  }
}
