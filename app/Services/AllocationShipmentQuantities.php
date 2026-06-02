<?php

namespace Modules\Ifulfillment\Services;


use Illuminate\Support\Collection;
use Modules\Ifulfillment\Models\OrderItem;
use Modules\Ifulfillment\Models\Shipment;
use Modules\Ifulfillment\Models\ShipmentItem;
use Illuminate\Support\Facades\DB;

class AllocationShipmentQuantities
{
  public function updateShipment(Shipment $shipment, array $items): void
  {
    DB::transaction(function () use ($shipment, $items) {
      $totalShipped = 0;
      $affectedOrderItemIds = [];

      foreach ($items as $itemData) {
        // 1. Load current ShipmentItem from DB (originalSizes = planned qty baseline)
        $shipmentItem = ShipmentItem::find($itemData['id']);
        if (!$shipmentItem) continue;

        $originalSizes = collect($shipmentItem->sizes)->keyBy('size');
        $incomingSizes = collect($itemData['sizes'] ?? [])->keyBy('size');

        // 2. Compute total for this item
        $itemQty = $incomingSizes->sum('quantity');

        // 3. Persist the new sizes on this ShipmentItem
        $shipmentItem->update([
          'sizes' => $incomingSizes->values()->toArray(),
          'quantity' => $itemQty,
          'options' => $itemData['options'] ?? null,
        ]);

        $diffs = $this->calculateDiffs($originalSizes, $incomingSizes);

        $this->redistributeDiffs(
          $shipmentItem,
          $this->capDiffsToOrderedQuantity($shipmentItem, $diffs, false)
        );

        $totalShipped += $itemQty;
        $affectedOrderItemIds[] = $shipmentItem->order_item_id;
      }

      $shipment->update(['total_items' => $totalShipped]);
      $this->refreshOrderItems(array_unique($affectedOrderItemIds));
    });
  }

  public function restoreShipment(Shipment $shipment): void
  {
    DB::transaction(function () use ($shipment) {
      $shipmentItems = ShipmentItem::query()->where('shipping_id', $shipment->id)->get();
      $affectedOrderItemIds = [];

      foreach ($shipmentItems as $shipmentItem) {
        $affectedOrderItemIds[] = $shipmentItem->order_item_id;

        $diffs = collect($shipmentItem->sizes)
          ->mapWithKeys(fn($size) => [
            (string)$size['size'] => (int)$size['quantity']
          ])
          ->toArray();

        $this->redistributeDiffs(
          $shipmentItem,
          $this->capDiffsToOrderedQuantity($shipmentItem, $diffs, true)
        );
      }

      $shipmentItems->each->delete();
      $this->refreshOrderItems(array_unique($affectedOrderItemIds));
    });
  }

  // Compute per-size diff: positive = under-production, negative = over-production
  private function calculateDiffs(
    Collection $originalSizes,
    Collection $incomingSizes
  ): array
  {
    $diffs = [];

    foreach (
      $originalSizes->keys()
        ->merge($incomingSizes->keys())
        ->unique() as $size
    ) {
      $originalQty = (int)($originalSizes->get($size)['quantity'] ?? 0);
      $incomingQty = (int)($incomingSizes->get($size)['quantity'] ?? 0);

      $diffs[(string)$size] = $originalQty - $incomingQty;
    }

    return $diffs;
  }

  /**
   * Cap positive diffs so that extra (over-produced) units never return to
   * open planning. For each size the maximum that may flow back into open
   * ShipmentItems is:
   *
   *   max_in_open = ordered - total_still_in_assigned_shipments
   *   can_add     = max_in_open - already_in_open_items
   *   capped_diff = min(diff, can_add)
   *
   * Negative diffs (quantity being added to a shipment) pass through unchanged.
   *
   * @param bool $beingDeleted true when the shipmentItem itself will be
   *                            removed and must be excluded from the assigned
   *                            total (restoreShipment). false when it has
   *                            already been saved with its new quantities
   *                            and should be counted (updateShipment).
   */
  private function capDiffsToOrderedQuantity(
    ShipmentItem $shipmentItem,
    array        $diffs,
    bool         $beingDeleted = false
  ): array
  {
    $orderItem = OrderItem::find($shipmentItem->order_item_id);
    if (!$orderItem) {
      return $diffs;
    }

    $orderedPerSize = collect($orderItem->sizes)
      ->keyBy('size')
      ->map(fn($s) => (int)$s['quantity']);

    // Total quantities locked in assigned (non-open) ShipmentItems.
    $assignedQuery = ShipmentItem::query()
      ->where('order_item_id', $shipmentItem->order_item_id)
      ->whereNotNull('shipping_id');

    if ($beingDeleted) {
      $assignedQuery->where('id', '!=', $shipmentItem->id);
    }

    $totalInAssigned = [];
    $assignedQuery->get()->each(function (ShipmentItem $item) use (&$totalInAssigned) {
      foreach ($item->sizes as $size) {
        $key = (string)$size['size'];
        $totalInAssigned[$key] = ($totalInAssigned[$key] ?? 0) + (int)$size['quantity'];
      }
    });

    // Total quantities already sitting in open (unshipped) ShipmentItems.
    $totalInOpen = [];
    ShipmentItem::query()
      ->where('order_item_id', $shipmentItem->order_item_id)
      ->whereNull('shipping_id')
      ->where('id', '!=', $shipmentItem->id)
      ->get()
      ->each(function (ShipmentItem $item) use (&$totalInOpen) {
        foreach ($item->sizes as $size) {
          $key = (string)$size['size'];
          $totalInOpen[$key] = ($totalInOpen[$key] ?? 0) + (int)$size['quantity'];
        }
      });

    $capped = [];
    foreach ($diffs as $size => $diff) {
      if ($diff <= 0) {
        // Negative diff = shipment gained quantity; no capping needed.
        $capped[$size] = $diff;
        continue;
      }

      $ordered = $orderedPerSize->get((string)$size, 0);
      $inAssigned = $totalInAssigned[(string)$size] ?? 0;
      $inOpen = $totalInOpen[(string)$size] ?? 0;

      // How much room is there for open items before we'd exceed the ordered qty?
      $maxCanBeInOpen = max(0, $ordered - $inAssigned);
      $canAdd = max(0, $maxCanBeInOpen - $inOpen);

      $capped[$size] = min($diff, $canAdd);
    }

    return $capped;
  }

  private function redistributeDiffs(
    ShipmentItem $shipmentItem,
    array        $diffs
  ): void
  {
    if (collect($diffs)->every(fn($qty) => $qty === 0)) {
      return;
    }
    // 5. Find ALL open ShipmentItems for the same OrderItem (oldest first)
    $openItems = ShipmentItem::query()
      ->where('order_item_id', $shipmentItem->order_item_id)
      ->whereNull('shipping_id')
      ->where('id', '!=', $shipmentItem->id)
      ->orderBy('id')
      ->get();

    $remainingDiff = $diffs;
    // 6. Redistribute diff across open items (first-come-first-served per size)
    foreach ($openItems as $openItem) {

      if (collect($remainingDiff)->every(fn($qty) => $qty === 0)) break;

      $openSizes = collect($openItem->sizes)->keyBy('size');
      $changed = false;

      foreach ($remainingDiff as $size => $diff) {
        if ($diff === 0) continue;

        $currentQty = (int)($openSizes->get((string)$size)['quantity'] ?? 0);
        $newQty = max(0, $currentQty + $diff);
        $applied = $newQty - $currentQty; // actual change (maybe less than diff for over-prod)

        $openSizes->put((string)$size, ['size' => (string)$size, 'quantity' => $newQty,]);
        $remainingDiff[$size] -= $applied;
        $changed = true;
      }

      if (!$changed) continue;

      $newSizes = $openSizes->values()->toArray();
      $totalQty = collect($newSizes)->sum('quantity');

      if ($totalQty === 0) {
        $openItem->delete();
        continue;
      }

      $openItem->update(['sizes' => $newSizes, 'quantity' => $totalQty]);
    }

    $remainingDiff = array_filter(
      $remainingDiff,
      fn($qty) => $qty !== 0
    );

    if (!empty($remainingDiff)) {
      $this->createOpenShipmentItem(
        $shipmentItem,
        $remainingDiff
      );
    }
  }

  // 8. For each affected OrderItem: compute extraQuantity per size
  private function refreshOrderItems(array $orderItemIds): void
  {
    foreach ($orderItemIds as $orderItemId) {
      $orderItem = OrderItem::find($orderItemId);
      if (!$orderItem) continue;

      // Sum quantities per size across ALL ShipmentItems for this OrderItem
      $totalPerSize = [];
      ShipmentItem::query()
        ->where('order_item_id', $orderItemId)
        ->get()
        ->each(function (ShipmentItem $shipmentItem) use (&$totalPerSize) {

          foreach ($shipmentItem->sizes as $size) {
            $sizeKey = (string)$size['size'];
            $totalPerSize[$sizeKey] = ($totalPerSize[$sizeKey] ?? 0) + (int)$size['quantity'];
          }
        });

      // Compare with OrderItem.sizes and write extraQuantity where applicable
      $sizes = [];
      foreach ($orderItem->sizes as $size) {
        $sizeKey = (string)$size['size'];
        $ordered = (int)$size['quantity'];
        $produced = (int)($totalPerSize[$sizeKey] ?? 0);
        $extra = max(0, $produced - $ordered);

        $entry = ['size' => $size['size'], 'quantity' => $ordered,];
        if ($extra > 0) $entry['extraQuantity'] = $extra;

        $sizes[] = $entry;
      }

      $orderItem->update(['sizes' => $sizes,]);
    }
  }

  private function createOpenShipmentItem(
    ShipmentItem $source,
    array        $quantities
  ): void
  {
    $sizes = [];
    $total = 0;

    foreach ($quantities as $size => $quantity) {
      if ($quantity <= 0) continue;
      $sizes[] = ['size' => (string)$size, 'quantity' => $quantity,];
      $total += $quantity;
    }

    if ($total === 0) return;

    ShipmentItem::create([
      'order_item_id' => $source->order_item_id,
      'shipping_id' => null,
      'sizes' => $sizes,
      'quantity' => $total,
      'options' => $source->options,
    ]);
  }
}
