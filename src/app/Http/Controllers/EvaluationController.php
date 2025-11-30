<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Evaluation;
use App\Models\Transaction;
use App\Mail\TransactionCompletedMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;

class EvaluationController extends Controller
{
  public function store(Request $request, Transaction $transaction)
  {
    $userId = Auth::id();

    $evaluatedId = $transaction->buyer_id == $userId
      ? $transaction->seller_id
      : $transaction->buyer_id;

    Evaluation::create([
      'transaction_id' => $transaction->id,
      'evaluator_id'   => $userId,
      'evaluated_id'   => $evaluatedId,
      'score'          => $request->score,
    ]);

    if ($userId == $transaction->buyer_id) {
      $product = $transaction->item;
      $buyer   = $transaction->buyer;
      $seller  = $transaction->seller;

      Mail::to($seller->email)->send(
        new TransactionCompletedMail($product, $buyer)
      );
    }

    $evalCount = Evaluation::where('transaction_id', $transaction->id)->count();
    if ($evalCount >= 2) {
      $transaction->completed_at = now();
      $transaction->save();
    }

    return response()->json(['status' => 'ok']);
  }
}
