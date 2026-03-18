<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
  /**
   * Run the migrations.
   * - Drops legacy columns: units_per_package, packages_total
   * - Adds packaging JSON column
   */
  public function up(): void
  {
    Schema::table('ifulfillment__shipments', function (Blueprint $table) {
      $table->dropColumn(['units_per_package', 'packages_total']);
      $table->json('packaging')->nullable()->after('options');
    });
  }

  /**
   * Reverse the migrations.
   */
  public function down(): void
  {
    Schema::table('ifulfillment__shipments', function (Blueprint $table) {
      $table->dropColumn('packaging');
      $table->integer('units_per_package')->default(0);
      $table->integer('packages_total')->default(0);
    });
  }
};
