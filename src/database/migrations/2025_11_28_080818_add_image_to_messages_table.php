<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddImageToMessagesTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('messages', function (Blueprint $table) {
      $table->renameColumn('image_path', 'image');
    });
  }

  public function down()
  {
    Schema::table('messages', function (Blueprint $table) {
      $table->renameColumn('image', 'image_path');
    });
  }
}
