<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
  /**
   * Run the migrations.
   */
  public function up(): void
  {
    Schema::create('transactions', function (Blueprint $table) {
      $table->id();

      // 紐づく商品
      $table->unsignedBigInteger('item_id');

      // 購入者と出品者
      $table->unsignedBigInteger('buyer_id')->nullable();
      $table->unsignedBigInteger('seller_id')->nullable();

      // 支払い方法
      $table->string('payment_method')->nullable();

      // 配送先情報
      $table->string('shipping_postcode')->nullable();
      $table->string('shipping_address')->nullable();
      $table->string('shipping_building')->nullable();

      // ステータス（例: pending, completed, canceled）
      $table->string('status')->default('pending');

      // 購入日時
      $table->timestamp('purchased_at')->nullable();

      $table->timestamps();

      // 外部キー制約
      $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
      $table->foreign('buyer_id')->references('id')->on('users')->onDelete('set null');
      $table->foreign('seller_id')->references('id')->on('users')->onDelete('set null');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::dropIfExists('transactions');
  }
};
