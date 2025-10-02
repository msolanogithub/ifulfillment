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
    Schema::create('ifulfillment__shipments', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->increments('id');
      // Your fields...
      $table->integer('account_id')->unsigned();
      $table->integer('parent_id')->nullable();
      $table->integer('total_items')->default(0);
      $table->timestamp('shipped_at')->nullable();
      $table->text('comments')->nullable();
      $table->integer('quantity_per_index')->default(0);
      $table->integer('index')->default(0);
      $table->integer('total_index')->default(0);
      // Foreign keys
      $table->foreign('account_id')->references('id')->on('iaccount__accounts')->onDelete('cascade');
      $table->foreign('parent_id')->references('id')->on('ifulfillment__shipments')->onDelete('cascade');
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
    Schema::table('ifulfillment__shipments', function (Blueprint $table) {
      $table->dropForeign(['account_id']);
      $table->dropForeign(['parent_id']);
    });
    Schema::dropIfExists('ifulfillment__shipments');
  }
};
