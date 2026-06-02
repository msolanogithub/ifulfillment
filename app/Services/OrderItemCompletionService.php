<?php

namespace Modules\Ifulfillment\Services;

use Modules\Ifulfillment\Models\OrderItem;
use Modules\Ifulfillment\Models\ShipmentItem;

class OrderItemCompletionService
{
  /**
   * Recalculate and persist the is_completed flag for a single OrderItem.
   *
   * Always reads fresh data from the database, so the result is always
   * consistent regardless of whether the triggering ShipmentItem was
   * created, updated, or deleted.
   */
  public function recalculate(int $orderItemId): void
  {
    $orderItem = OrderItem::find($orderItemId);
    if (!$orderItem) return;

    $orderItem->updateQuietly([
      'is_completed' => $this->checkIsCompleted($orderItem),
    ]);
  }

  /**
   * An OrderItem is completed when every ordered size has been fully covered
   * by the combined quantities across all its ShipmentItems.
   *
   * - Completion is per-size, NOT based on total quantity.
   * - Extra sizes that were not ordered are ignored.
   * - An OrderItem with no ordered sizes is not considered completed.
   */
  private function checkIsCompleted(OrderItem $orderItem): bool
  {
    $orderedSizes = collect($orderItem->sizes)->keyBy('size');

    if ($orderedSizes->isEmpty()) {
      return false;
    }

    $producedPerSize = $this->sumProducedPerSize($orderItem->id);

    return $orderedSizes->every(
      fn($entry, $size) => ($producedPerSize[(string)$size] ?? 0) >= (int)$entry['quantity']
    );
  }

  /**
   * Sum ShipmentItem quantities grouped by size for the given OrderItem.
   *
   * @return array<string, int>  e.g. ['37' => 10, '38' => 25]
   */
  private function sumProducedPerSize(int $orderItemId): array
  {
    $produced = [];

    ShipmentItem::where('order_item_id', $orderItemId)
      ->get()
      ->each(function (ShipmentItem $shipmentItem) use (&$produced) {
        foreach ($shipmentItem->sizes as $entry) {
          $size = (string)$entry['size'];
          $produced[$size] = ($produced[$size] ?? 0) + (int)$entry['quantity'];
        }
      });

    return $produced;
  }
}
