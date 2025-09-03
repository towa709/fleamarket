<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateItemsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('items', function (Blueprint $table) {
      $table->id();
      $table->unsignedBigInteger('user_id');
      $table->string('name'); // 商品名
      $table->integer('price'); // 価格
      $table->string('brand')->nullable(); // ブランド名
      $table->text('description')->nullable(); // 商品説明
      $table->string('img_url')->nullable(); // 画像URL
      $table->string('condition'); // コンディション（状態）
      $table->timestamps();


      $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
    });
  }


  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('items');
  }
}
