<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Comment extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'item_id',
    'content',
  ];

  // 🔗 ユーザーとの関係（コメントした人）
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  // 🔗 商品との関係（コメント対象の商品）
  public function item()
  {
    return $this->belongsTo(Item::class);
  }
}
