<?php

namespace Modules\Ifulfillment\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Ifulfillment\Models\DynamicOptions;
use Modules\Ifulfillment\Models\Shipment;

class ShipmentPackagingMigrationSeeder extends Seeder
{
  /**
   * One-time data migration seeder.
   *
   * For every Shipment that has a non-null `options` column:
   *
   *  1. shippedWith  → ensure DynamicOptions row (type='carrier', value=shippedWith)
   *                   → rename key in options: replace 'shippedWith' with 'carrier'
   *
   *  2. packagedBy   → ensure DynamicOptions row (type='packer', value=packagedBy)
   *                   → for each ShipmentItem: if packaging['packer'] is not set,
   *                     set it to the packagedBy value
   *                   → remove 'packagedBy' from shipment.options
   */
  public function run(): void
  {
    $shipments = Shipment::whereNotNull('options')->with('items')->get();

    foreach ($shipments as $shipment) {
      $options = $shipment->options ?? [];

      if (empty($options)) {
        continue;
      }

      $optionsDirty = false;

      // ── 1. shippedWith ────────────────────────────────────────────────────
      if (!empty($options['shippedWith'])) {
        $shippedWith = $options['shippedWith'];

        DynamicOptions::firstOrCreate(
          ['type' => 'carrier', 'value' => $shippedWith]
        );

        // Rename the key from 'shippedWith' to 'carrier' in options
        $options['carrier'] = $shippedWith;
        unset($options['shippedWith']);
        $optionsDirty = true;
      }

      // ── 2. packagedBy ─────────────────────────────────────────────────────
      $packagedBy = null;

      if (!empty($options['packagedBy'])) {
        $packagedBy = $options['packagedBy'];

        DynamicOptions::firstOrCreate(
          ['type' => 'packer', 'value' => $packagedBy]
        );

        // Set packer on each ShipmentItem that does not already have one
        foreach ($shipment->items as $item) {
          $itemPackaging = $item->packaging ?? [];

          if (empty($itemPackaging['packer'])) {
            $itemPackaging['packer'] = $packagedBy;
            $item->packaging = $itemPackaging;
            $item->save();
          }
        }

        // Remove packagedBy from shipment options
        unset($options['packagedBy']);
        $optionsDirty = true;
      }

      if ($optionsDirty) {
        $shipment->options = $options;
        $shipment->save();
      }
    }
  }
}
