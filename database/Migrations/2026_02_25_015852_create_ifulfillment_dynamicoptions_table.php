<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('ifulfillment__dynamic_options', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->increments('id');
      // Your fields...
      $table->string('type');
      $table->string('value');
      $table->unique(['type', 'value']);
      // Audit fields
      $table->timestamps();
      $table->auditStamps();
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('ifulfillment__dynamic_options');
  }
};
