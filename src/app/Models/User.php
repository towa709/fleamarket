<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Item;
use App\Models\Transaction;
use App\Models\Favorite;

class User extends Authenticatable implements MustVerifyEmail
{
  use HasApiTokens, HasFactory, Notifiable;

  /**
   * The attributes that are mass assignable.
   *
   * @var array<int, string>
   */
  protected $fillable = [
    'name',
    'email',
    'password',
    'postcode',
    'address',
    'building',
    'profile_image',
  ];

  /**
   * The attributes that should be hidden for serialization.
   *
   * @var array<int, string>
   */
  protected $hidden = [
    'password',
    'remember_token',
  ];

  /**
   * The attributes that should be cast.
   *
   * @var array<string, string>
   */
  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  // 🔹 購入履歴（transactions.buyer_id 経由）
  public function purchases()
  {
    return $this->hasMany(Transaction::class, 'buyer_id');
  }

  // 🔹 出品履歴（transactions.seller_id 経由）
  public function sales()
  {
    return $this->hasMany(Transaction::class, 'seller_id');
  }

  // 🔹 お気に入り（Favorite モデル経由）
  public function favorites()
  {
    return $this->belongsToMany(Item::class, 'favorites', 'user_id', 'item_id')
      ->withTimestamps();
  }
}
