<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  public function up()
  {
    Schema::create('category_items', function (Blueprint $table) {
      $table->id();
      $table->foreignId('item_id')->constrained('items')->onDelete('cascade');
      $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
      $table->timestamps();
    });
  }

  public function down()
  {
    Schema::dropIfExists('category_items');
  }
};
