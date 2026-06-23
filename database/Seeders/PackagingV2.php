<?php

namespace Modules\Ifulfillment\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Ifulfillment\Models\Shipment;
use Modules\Ifulfillment\Models\ShipmentItem;
use Modules\Ishoe\Models\Shoe;

class PackagingV2 extends Seeder
{
  public function run(): void
  {
    $this->migrateRules();
    $this->migrateBoxes();
  }

  public function migrateRules(): void
  {
    ShipmentItem::query()->chunkById(500, function ($items) {
      foreach ($items as $item) {
        if (data_get($item->packaging, 'rules')) continue;

        $sizePackaging = data_get($item->packaging, 'sizePackaging', []);
        $quantityBySize = collect($item->sizes)->pluck('quantity', 'size');

        $rules = collect($sizePackaging)
          ->map(fn($perBox, $size) => [
            'size' => (int)$size,
            'qty' => $quantityBySize->get($size, 0),
            'perBox' => $perBox,
          ])
          ->filter(fn($rule) => $rule['qty'] > 0)
          ->values()
          ->all();

        $packaging = $item->packaging;
        if (count($rules)) $packaging['rules'] = $rules;

        $item->update(['packaging' => $packaging]);
      }
    });
  }

  public function migrateBoxes(): void
  {
    $shoes = Shoe::query()->get()->keyBy('reference');
    Shipment::query()->with('items.orderItem')->chunkById(500, function ($shipments) use ($shoes) {
      foreach ($shipments as $shipment) {
        $packaging = $shipment->packaging;
        if (empty($packaging)) continue;

        $newBoxes = $this->mapShipmentBoxes($shipment, $packaging, $shoes);

        if (count($newBoxes)) {
          if (is_object($packaging)) {
            $packaging = (array)$packaging;
          }
          $packaging['boxes'] = $newBoxes;
          unset($packaging['manualBoxes']);
          unset($packaging['fullBoxGroups']);
          $shipment->update(['packaging' => $packaging]);
        }
      }
    });
  }

  public function mapShipmentBoxes($shipment, $packaging, $shoes): array
  {
    $newBoxes = [];
    $hasBoxesToMigrate = false;

    $manualBoxes = data_get($packaging, 'manualBoxes');
    if (!empty($manualBoxes) && is_array($manualBoxes)) {
      $hasBoxesToMigrate = true;
      $newBoxes = array_merge($newBoxes, $this->mapManualBoxes($manualBoxes, $shoes, $shipment));
    }

    $fullBoxGroups = data_get($packaging, 'fullBoxGroups');
    if (!empty($fullBoxGroups) && is_array($fullBoxGroups)) {
      $hasBoxesToMigrate = true;
      $newBoxes = array_merge($newBoxes, $this->mapFullBoxGroups($fullBoxGroups, $shoes, $shipment));
    }

    return $hasBoxesToMigrate ? $newBoxes : [];
  }

  private function findShipmentItemId(string $refName, $shoes, $shipment): ?int
  {
    if (preg_match('/\[([^\]]+)\]/', $refName, $matches)) {
      $reference = $matches[1];
      $shoe = $shoes->get($reference);
      if ($shoe) {
        $shipmentItem = $shipment->items->first(fn($item) => $item->orderItem && $item->orderItem->shoe_id == $shoe->id);
        if ($shipmentItem) {
          return $shipmentItem->id;
        }
      }
    }
    return null;
  }

  private function mapManualBoxes(array $manualBoxes, $shoes, $shipment): array
  {
    $newBoxes = [];
    foreach ($manualBoxes as $box) {
      $mappedItems = [];
      $shipmentItemsIds = [];
      $totalQty = 0;

      $lines = data_get($box, 'lines', []);
      foreach ($lines as $line) {
        $qty = (int)data_get($line, 'qty', 0);
        $size = (string)data_get($line, 'size');
        $refName = (string)data_get($line, 'refName');

        $shipmentItemId = $this->findShipmentItemId($refName, $shoes, $shipment);

        $mappedItems[] = [
          'qty' => $qty,
          'size' => $size,
          'shipmentItemId' => $shipmentItemId,
        ];

        if ($shipmentItemId) {
          $shipmentItemsIds[] = $shipmentItemId;
        }
        $totalQty += $qty;
      }
      if (count($mappedItems)) {
        $newBoxes[] = [
          'id' => Str::random(10),
          'items' => $mappedItems,
          'totalQty' => $totalQty,
          'isFullBox' => false,
          'shipmentItemsId' => array_values(array_unique($shipmentItemsIds)),
        ];
      }
    }
    return $newBoxes;
  }

  private function mapFullBoxGroups(array $fullBoxGroups, $shoes, $shipment): array
  {
    $newBoxes = [];
    foreach ($fullBoxGroups as $group) {
      $pkgQty = (int)data_get($group, 'pkgQty', 0);
      $fullBoxCount = (int)data_get($group, 'fullBoxCount', 0);
      $size = (string)data_get($group, 'size');
      $refName = (string)data_get($group, 'refName');

      $shipmentItemId = $this->findShipmentItemId($refName, $shoes, $shipment);

      for ($i = 0; $i < $fullBoxCount; $i++) {
        $newBoxes[] = [
          'id' => Str::random(10),
          'items' => [
            [
              'qty' => $pkgQty,
              'size' => $size,
              'shipmentItemId' => $shipmentItemId,
            ]
          ],
          'totalQty' => $pkgQty,
          'isFullBox' => true,
          'shipmentItemsId' => $shipmentItemId ? [$shipmentItemId] : [],
        ];
      }
    }
    return $newBoxes;
  }

}
