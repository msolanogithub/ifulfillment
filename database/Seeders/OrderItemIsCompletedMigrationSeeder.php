<?php

namespace Modules\Ifulfillment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Ifulfillment\Models\OrderItem;

class OrderItemIsCompletedMigrationSeeder extends Seeder
{
  public function run(): void
  {
    if (OrderItem::query()->where('is_completed', true)->exists()) {
      return;
    }

    DB::statement("
        UPDATE ifulfillment__order_items oi
        SET is_completed = (
            COALESCE(
                (
                    SELECT SUM(si.quantity)
                    FROM ifulfillment__shipment_items si
                    WHERE si.order_item_id = oi.id
                ),
                0
            ) >= oi.quantity
        )
    ");
  }
}
