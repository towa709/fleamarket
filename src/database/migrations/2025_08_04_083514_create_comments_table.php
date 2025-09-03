<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCommentsTable extends Migration
{
  public function up()
  {
    Schema::create('comments', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id'); // コメント投稿者
      $table->foreignId('item_id')->constrained()->onDelete('cascade'); // 商品への外部キー
      $table->text('content'); // コメント本文
      $table->timestamps();

      // 外部キー制約
      $table->foreign('user_id')
        ->references('id')
        ->on('users')
        ->onDelete('cascade');
    });
  }

  public function down()
  {
    Schema::dropIfExists('comments');
  }
}
