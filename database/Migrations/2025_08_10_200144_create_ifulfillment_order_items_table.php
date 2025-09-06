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
      $table->integer('entity_id');
      $table->string('entity_type');
      $table->json('entity_data')->nullable();
      $table->integer('quantity')->default(1);
      //foreign keys
      $table->foreign('order_id')->references('id')->on('ifulfillment__orders')->onDelete('cascade');
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
    });
    Schema::dropIfExists('ifulfillment__order_items');
  }
};
