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
    Schema::create('ifulfillment__orders', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->increments('id');
      // Your fields...
      $table->string('external_id')->nullable();
      $table->integer('customer_id')->unsigned();
      $table->timestamp('due_date')->nullable();
      $table->float('total_price')->default(0);
      $table->float('total_items')->default(0);
      // Foreign keys
      $table->foreign('customer_id')->references('id')->on('iuser__users')->onDelete('restrict');
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
    Schema::table('ifulfillment__orders', function (Blueprint $table) {
      $table->dropForeign(['customer_id']);
    });
    Schema::dropIfExists('ifulfillment__orders');
  }
};
