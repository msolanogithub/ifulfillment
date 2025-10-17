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
    Schema::create('ifulfillment__order_items', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->increments('id');
      // Your fields...
      $table->integer('order_id')->unsigned();
      $table->integer('shoe_id')->unsigned();
      $table->integer('quantity')->unsigned();
      $table->json('options');
      $table->json('sizes');
      //foreign keys
      $table->foreign('order_id')->references('id')->on('ifulfillment__orders')->onDelete('cascade');
      $table->foreign('shoe_id')->references('id')->on('ishoe__shoes')->onDelete('cascade');
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
    Schema::table('ifulfillment__order_items', function (Blueprint $table) {
      $table->dropForeign(['order_id']);
      $table->dropForeign(['shoe_id']);
    });
    Schema::dropIfExists('ifulfillment__order_items');
  }
};
