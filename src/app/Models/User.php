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
use App\Models\Evaluation;

class User extends Authenticatable implements MustVerifyEmail
{
  use HasApiTokens, HasFactory, Notifiable;

  protected $fillable = [
    'name',
    'email',
    'password',
    'postcode',
    'address',
    'building',
    'profile_image',
  ];

  protected $hidden = [
    'password',
    'remember_token',
  ];

  protected $casts = [
    'email_verified_at' => 'datetime',
  ];

  public function purchases()
  {
    return $this->hasMany(Transaction::class, 'buyer_id');
  }

  public function sales()
  {
    return $this->hasMany(Transaction::class, 'seller_id');
  }

  public function favorites()
  {
    return $this->belongsToMany(Item::class, 'favorites', 'user_id', 'item_id')
      ->withTimestamps();
  }

  public function receivedEvaluations()
  {
    return $this->hasMany(Evaluation::class, 'evaluated_id');
  }

  public function averageScore()
  {
    $avg = $this->receivedEvaluations()->avg('score');
    return $avg ? round($avg) : null;
  }
}
