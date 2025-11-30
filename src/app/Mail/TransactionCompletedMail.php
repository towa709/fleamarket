<?php

namespace App\Mail;

use App\Models\Item;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionCompletedMail extends Mailable
{
  use Queueable, SerializesModels;

  public $product;
  public $buyer;

  public function __construct(Item $product, $buyer)
  {
    $this->product = $product;
    $this->buyer = $buyer;
  }

  public function build()
  {
    return $this
      ->subject('【Fleamarket】取引が完了しました')
      ->markdown('emails.transaction_completed');
  }
}
