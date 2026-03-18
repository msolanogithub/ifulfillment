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
    Schema::create('ifulfillment__traces', function (Blueprint $table) {
      $table->engine = 'InnoDB';
      $table->increments('id');
      // Polymorphic parent (Order, Shipment, etc.)
      $table->string('traceable_type');
      $table->unsignedInteger('traceable_id');
      // Event type: created | updated | production_adjusted | document_generated | comment
      $table->string('type');
      // Contextual data for the event
      $table->json('payload')->nullable();
      // Indexes
      $table->index(['traceable_type', 'traceable_id']);
      $table->index('type');
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
    Schema::table('ifulfillment__traces', function (Blueprint $table) {
      $table->dropForeign(['created_by']);
    });
    Schema::dropIfExists('ifulfillment__traces');
  }
};
