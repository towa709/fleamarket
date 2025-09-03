<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property-read bool $is_sold
 */
class Item extends Model
{
  use HasFactory;

  protected $fillable = [
    'user_id',
    'name',
    'price',
    'brand',
    'description',
    'img_url',
    'condition'
  ];


  // ユーザーとの関係（出品者）
  public function user()
  {
    return $this->belongsTo(User::class);
  }

  // カテゴリとの関係
  public function categories()
  {
    return $this->belongsToMany(Category::class, 'category_items', 'item_id', 'category_id');
  }

  // 画像（複数）との関係
  public function images()
  {
    return $this->hasMany(Image::class);
  }

  // コメント（複数）との関係
  public function comments()
  {
    return $this->hasMany(Comment::class)->with('user');
  }

  // お気に入り（複数）との関係
  public function favorites()
  {
    return $this->belongsToMany(User::class, 'favorites');
  }

  // 購入情報（1対1）との関係（transactions に統合）
  public function transaction()
  {
    return $this->hasOne(Transaction::class);
  }

  public function getIsSoldAttribute()
  {
    return $this->transaction()->exists();
  }
}
