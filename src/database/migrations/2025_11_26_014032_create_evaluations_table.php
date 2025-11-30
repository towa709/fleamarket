<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEvaluationsTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('evaluations', function (Blueprint $table) {
      $table->bigIncrements('id');
      $table->unsignedBigInteger('transaction_id');
      $table->unsignedBigInteger('evaluator_id');
      $table->unsignedBigInteger('evaluated_id');
      $table->integer('score');
      $table->timestamps();

      $table->foreign('transaction_id')->references('id')->on('transactions')->onDelete('cascade');
      $table->foreign('evaluator_id')->references('id')->on('users')->onDelete('cascade');
      $table->foreign('evaluated_id')->references('id')->on('users')->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('evaluations');
  }
}
