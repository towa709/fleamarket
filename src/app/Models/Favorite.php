<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'item_id',
  ];

  // 🔗 ユーザーとの関係（お気に入りした人）
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  // 🔗 商品との関係（お気に入りされた商品）
  public function item()
  {
    return $this->belongsTo(Item::class);
  }
}
