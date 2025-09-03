<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProfileColumnsToUsersTable extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::table('users', function (Blueprint $table) {
      $table->string('postcode')->nullable();
      $table->string('address')->nullable();
      $table->string('building')->nullable();
      $table->string('profile_image')->nullable();
    });
  }

  public function down()
  {
    Schema::table('users', function (Blueprint $table) {
      if (Schema::hasColumn('users', 'postcode')) {
        $table->dropColumn('postcode');
      }
      if (Schema::hasColumn('users', 'address')) {
        $table->dropColumn('address');
      }
      if (Schema::hasColumn('users', 'building')) {
        $table->dropColumn('building');
      }
      if (Schema::hasColumn('users', 'profile_image')) {
        $table->dropColumn('profile_image');
      }
    });
  }


  /**
   * Reverse the migrations.
   *
   * @return void
   */
}
