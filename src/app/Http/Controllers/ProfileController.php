<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\ProfileRequest;
use App\Models\Transaction;
use App\Models\Item;


class ProfileController extends Controller
{
  // プロフィール設定画面
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

    return view('mypage.mypage', [
      'user' => $user,
      'sales' => $sales,
      'soldItems' => $soldItems,
      'purchases' => $purchases,
      'page' => $page,
    ]);
  }
}
