<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PurchaseRequest;
use App\Http\Requests\AddressRequest;
use App\Models\Item;
use App\Models\Transaction;
use Stripe\Stripe;
use Illuminate\Support\Facades\Auth;
use Stripe\Checkout\Session as StripeSession;

class PurchaseController extends Controller
{
  // 購入画面表示
  public function show($item_id)
  {
    $item = Item::findOrFail($item_id);
    $user = Auth::user();

    return view('items.purchase_confirm', compact('item', 'user'));
  }

  // 住所変更画面表示
  public function address($item_id)
  {
    $item = Item::findOrFail($item_id);
    $user = Auth::user();

    return view('purchase_address', compact('item', 'user'));
  }

  // 住所更新処理
  public function updateAddress(AddressRequest $request, $item_id)
  {
    session([
      'shipping_postcode' => $request->postcode,
      'shipping_address'  => $request->address,
      'shipping_building' => $request->building,
    ]);

    return redirect()->route('purchase.show', $item_id);
  }

  // バリデーション入口
  public function store(PurchaseRequest $request, $item_id)
  {
    $item = Item::findOrFail($item_id);

    if ($item->transaction) {
      return redirect()->route('items.show', $item_id);
    }

    $validated = $request->validated();

    // checkout に処理を渡す
    return $this->checkout(new Request([
      'item_id' => $item_id,
      'payment' => $validated['payment_method'],
    ]));
  }

  // Stripe 決済処理
  public function checkout(Request $request)
  {
    Stripe::setApiKey(env('STRIPE_SECRET'));

    $paymentMethod = $request->input('payment', 'card');
    $item = Item::findOrFail($request->item_id);

    $session = StripeSession::create([
      'payment_method_types' => [$paymentMethod],
      'line_items' => [[
        'price_data' => [
          'currency' => 'jpy',
          'product_data' => [
            'name' => $item->name,
          ],
          'unit_amount' => $item->price,
        ],
        'quantity' => 1,
      ]],
      'mode' => 'payment',
      'success_url' => route('purchase.success', ['item_id' => $item->id, 'payment' => $paymentMethod]) . '?session_id={CHECKOUT_SESSION_ID}',
      'cancel_url'  => route('purchase.show', ['item_id' => $item->id]),
    ]);

    // Stripeの決済画面へリダイレクト
    return redirect($session->url);
  }

  // 決済成功後
  public function success(Request $request, $item_id)
  {
    $item = Item::findOrFail($item_id);
    $user = Auth::user();

    if ($item->transaction) {
      return redirect()->route('items.index');
    }

    $paymentMethod = $request->get('payment', 'card');

    // セッション優先、なければプロフィール住所
    $postcode = session('shipping_postcode', $user->postcode);
    $address  = session('shipping_address', $user->address);
    $building = session('shipping_building', $user->building);

    Transaction::create([
      'buyer_id'          => $user->id,
      'seller_id'         => $item->user_id,
      'item_id'           => $item->id,
      'payment_method'    => $paymentMethod,
      'shipping_postcode' => $postcode,
      'shipping_address'  => $address,
      'shipping_building' => $building,
      'purchased_at'      => now(),
    ]);

    if (in_array($paymentMethod, ['card', 'konbini'])) {
      $item->is_sold = true;
      $item->save();
    }

    // 購入完了後にセッションをリセット
    session()->forget(['shipping_postcode', 'shipping_address', 'shipping_building']);

    return redirect()->route('items.index');
  }
}
