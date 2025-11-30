<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
  protected $fillable = [
    'buyer_id',
    'seller_id',
    'item_id',
    'payment_method',
    'shipping_postcode',
    'shipping_address',
    'shipping_building',
    'purchased_at',
  ];


  // 🔗 商品（この取引に関連する商品）
  public function item()
  {
    return $this->belongsTo(Item::class);
  }

  public function buyer()
  {
    return $this->belongsTo(User::class, 'buyer_id');
  }

  public function seller()
  {
    return $this->belongsTo(User::class, 'seller_id');
  }

  public function scopeInProgress($query)
  {
    return $query->whereNull('completed_at');
  }

  public function scopeCompleted($query)
  {
    return $query->whereNotNull('completed_at');
  }

  public function messages()
  {
    return $this->hasMany(Message::class);
  }

  public function evaluations()
  {
    return $this->hasMany(Evaluation::class);
  }
}
