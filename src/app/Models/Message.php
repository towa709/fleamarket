<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Message extends Model
{
  use HasFactory, SoftDeletes;

  protected $fillable = [
    'transaction_id',
    'user_id',
    'message',
    'image',
    'is_read',
  ];

  public function transaction()
  {
    return $this->belongsTo(Transaction::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class);
  }

  public function scopeUnreadBy($query, $userId)
  {
    return $query->where('user_id', '!=', $userId)
      ->where('is_read', false);
  }
}
