<?php

namespace App\Http\Controllers;

use App\Http\Requests\ChatMessageRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use App\Models\Transaction;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
  public function show($transaction_id)
  {
    $transaction = Transaction::with(['item'])->findOrFail($transaction_id);

    $me = Auth::id();

    $partnerId = ($transaction->buyer_id == $me)
      ? $transaction->seller_id
      : $transaction->buyer_id;

    $userEvaluated = \App\Models\Evaluation::where('transaction_id', $transaction_id)
      ->where('evaluator_id', $me)
      ->exists();

    $partnerEvaluated = \App\Models\Evaluation::where('transaction_id', $transaction_id)
      ->where('evaluator_id', $partnerId)
      ->exists();

    $shouldEvaluate = (!$userEvaluated && $partnerEvaluated);

    return view('chat.show', [
      'transaction_id' => $transaction_id,
      'transaction' => $transaction,
      'item' => $transaction->item,
      'shouldEvaluate' => $shouldEvaluate,
    ]);
  }

  public function getPartner($transactionId)
  {
    $transaction = Transaction::with(['buyer', 'seller'])->findOrFail($transactionId);
    $myId = Auth::id();

    if ($transaction->buyer_id == $myId) {
      $partner = $transaction->seller;
    } elseif ($transaction->seller_id == $myId) {
      $partner = $transaction->buyer;
    } else {
      abort(404);
    }

    return response()->json([
      'name' => $partner->name,
      'image' => $partner->profile_image
        ? asset('storage/' . $partner->profile_image)
        : asset('images/default-avatar.png'),
    ]);
  }

  public function getMessages($transactionId)
  {
    return Message::where('transaction_id', $transactionId)
      ->orderBy('created_at')
      ->with('user:id,name,profile_image')
      ->get()
      ->map(function ($msg) {
        return [
          'id' => $msg->id,
          'user_id' => $msg->user_id,
          'user_name' => $msg->user->name,
          'user_image' => $msg->user->profile_image
            ? asset('storage/' . $msg->user->profile_image)
            : asset('images/default-avatar.png'),
          'message' => $msg->message,
          'image' => $msg->image ? asset('storage/' . $msg->image) : null,
          'created_at' => $msg->created_at,
        ];
      });
  }

  public function storeMessage(ChatMessageRequest $request, $transactionId)
  {
    $imagePath = null;

    if ($request->hasFile('image')) {
      $imagePath = $request->file('image')->store('chat_images', 'public');
    }

    $msg = Message::create([
      'transaction_id' => $transactionId,
      'user_id' => Auth::id(),
      'message' => $request->message,
      'image' => $imagePath,
    ]);

    return response()->json([
      'id' => $msg->id,
      'user_id' => $msg->user_id,
      'user_name' => $msg->user->name,
      'user_image' => $msg->user->profile_image
        ? asset('storage/' . $msg->user->profile_image)
        : asset('images/default-avatar.png'),
      'message' => $msg->message,
      'image' => $msg->image ? asset('storage/' . $msg->image) : null,
      'created_at' => $msg->created_at,
    ]);
  }

  public function markAsRead($transactionId)
  {
    $userId = Auth::id();

    Message::where('transaction_id', $transactionId)
      ->where('user_id', '!=', $userId)
      ->where('is_read', false)
      ->update(['is_read' => true]);

    return response()->json(['status' => 'ok']);
  }

  public function list()
  {
    $userId = Auth::id();

    $transactions = Transaction::where(function ($q) use ($userId) {
      $q->where('buyer_id', $userId)
        ->orWhere('seller_id', $userId);
    })
      ->whereNull('completed_at')
      ->with(['item'])
      ->get();

    $result = [];

    foreach ($transactions as $transaction) {
      $latest = Message::where('transaction_id', $transaction->id)
        ->orderByDesc('created_at')
        ->first();

      $unread = Message::where('transaction_id', $transaction->id)
        ->where('user_id', '!=', $userId)
        ->where('is_read', false)
        ->count();

      $result[] = [
        'transaction_id' => $transaction->id,
        'item_name' => $transaction->item ? $transaction->item->name : '商品名なし',
        'item_image' => ($transaction->item && $transaction->item->img_url)
          ? (Str::startsWith($transaction->item->img_url, 'http')
            ? $transaction->item->img_url
            : asset('storage/' . $transaction->item->img_url))
          : asset('images/no-image.png'),
        'unread_count' => $unread,
        'latest_message_time' => $latest ? $latest->created_at : null,
      ];
    }

    usort($result, function ($a, $b) {
      return strtotime($b['latest_message_time']) <=> strtotime($a['latest_message_time']);
    });

    return response()->json($result);
  }

  public function update(Request $req, Message $message)
  {
    $this->authorizeMessage($message);

    $message->update([
      'message' => $req->message,
    ]);

    return response()->json(['status' => 'ok']);
  }

  public function destroy(Message $message)
  {
    $this->authorizeMessage($message);

    $message->delete();

    return response()->json(['status' => 'ok']);
  }

  private function authorizeMessage($message)
  {
    if ($message->user_id !== Auth::id()) {
      abort(403);
    }
  }
}
