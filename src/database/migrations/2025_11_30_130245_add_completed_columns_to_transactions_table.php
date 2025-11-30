<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCompletedColumnsToTransactionsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('transactions', function (Blueprint $table) {
      $table->boolean('buyer_requested_complete')->default(0);
      $table->boolean('seller_requested_complete')->default(0);
    });
  }

  public function down()
  {
    Schema::table('transactions', function (Blueprint $table) {
      $table->dropColumn([
        'buyer_requested_complete',
        'seller_requested_complete',
      ]);
    });
  }
}
