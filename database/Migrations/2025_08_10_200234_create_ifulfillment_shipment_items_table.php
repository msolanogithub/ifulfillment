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
    Schema::create('ifulfillment__shipment_items', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->increments('id');
      // Your fields...
      $table->integer('shipping_id')->unsigned();
      $table->integer('order_item_id')->unsigned();
      $table->integer('quantity')->default(0);
      //foreign keys
      $table->foreign('shipping_id')->references('id')->on('ifulfillment__shipments')->onDelete('cascade');
      $table->foreign('order_item_id')->references('id')->on('ifulfillment__order_items')->onDelete('cascade');
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
    Schema::table('ifulfillment__shipment_items', function (Blueprint $table) {
      $table->dropForeign(['shipping_id']);
      $table->dropForeign(['order_item_id']);
    });
    Schema::dropIfExists('ifulfillment__shipment_items');
  }
};
