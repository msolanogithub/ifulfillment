<?php

namespace Modules\Ifulfillment\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Modules\Ifulfillment\Models\OrderItem;
use Modules\Ifulfillment\Models\ShipmentItem;
use Modules\Ifulfillment\Repositories\ShipmentRepository;
use Imagina\Icore\Repositories\Eloquent\EloquentCoreRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EloquentShipmentRepository extends EloquentCoreRepository implements ShipmentRepository
{
  /**
   * Filter names to replace
   * @var array
   */
  protected array $replaceFilters = [];

  /**
   * Relation names to replace
   * @var array
   */
  protected array $replaceSyncModelRelations = [];

  /**
   * Attribute to define default relations
   * all apply to index and show
   * index apply in the getItemsBy
   * show apply in the getItem
   * @var array
   */
  protected array $with = [/*all => [] ,index => [],show => []*/];

  /**
   * @param Builder $query
   * @param object $filter
   * @param object $params
   * @return Builder
   */
  public function filterQuery(Builder $query, object $filter, object $params): Builder
  {

    /**
     * Note: Add filter name to replaceFilters attribute before replace it
     *
     * Example filter Query
     * if (isset($filter->status)) $query->where('status', $filter->status);
     *
     */

    if (isset($filter->cityId)) {
      $query->whereHas('locatable', function ($q) use ($filter) {
        $q->where('city_id', $filter->cityId);
      });
    }

    //Response
    return $query;
  }

  /**
   * @param Model $model
   * @param array $data
   * @return Model
   */
  public function syncModelRelations(Model $model, array $data): Model
  {
    //Get model relations data from model attributes
    //$modelRelationsData = ($model->modelRelations ?? []);

    /**
     * Note: Add relation name to replaceSyncModelRelations attribute before replace it
     *
     * Example to sync relations
     * if (array_key_exists(<relationName>, $data)){
     *    $model->setRelation(<relationName>, $model-><relationName>()->sync($data[<relationName>]));
     * }
     *
     */


    //Response
    return $model;
  }

  protected function afterCreate(Model &$model, array &$data): void
  {
    if (isset($data['items']) && is_array($data['items'])) {
      foreach ($data['items'] as $item) {
        ShipmentItem::where('id', $item['id'])
          ->update(['shipping_id' => $model->id, 'options' => $item['options'] ?? null]);
      }
    }
  }

  protected function afterUpdate(&$model, &$data): void
  {
    if (!isset($data['items']) || !is_array($data['items'])) return;

    $totalShipped = 0;
    $affectedOrderItemIds = [];

    foreach ($data['items'] as $item) {
      // 1. Load current ShipmentItem from DB (originalSizes = planned qty baseline)
      $shipmentItem = ShipmentItem::find($item['id']);
      if (!$shipmentItem) continue;

      $orderItemId = $shipmentItem->order_item_id;
      $originalSizes = collect($shipmentItem->sizes)->keyBy('size'); // DB baseline
      $incomingSizes = collect($item['sizes'] ?? [])->keyBy('size'); // what's being shipped

      // 2. Compute total for this item
      $itemQty = $incomingSizes->sum('quantity');

      // 3. Persist the new sizes on this ShipmentItem
      $shipmentItem->update([
        'sizes' => $incomingSizes->values()->toArray(),
        'quantity' => $itemQty,
        'options' => $item['options'] ?? null,
      ]);

      $totalShipped += $itemQty;
      $affectedOrderItemIds[] = $orderItemId;

      // 4. Compute per-size diff: positive = under-production, negative = over-production
      $hasDiff = false;
      $diffs = [];
      foreach ($originalSizes->keys()->merge($incomingSizes->keys())->unique() as $size) {
        $originalQty = (int)($originalSizes->get($size)['quantity'] ?? 0);
        $incomingQty = (int)($incomingSizes->get($size)['quantity'] ?? 0);
        $diff = $originalQty - $incomingQty;
        $diffs[$size] = $diff;
        if ($diff !== 0) $hasDiff = true;
      }

      if (!$hasDiff) continue;

      // 5. Find ALL open ShipmentItems for the same OrderItem (oldest first)
      $openItems = ShipmentItem::where('order_item_id', $orderItemId)
        ->whereNull('shipping_id')
        ->where('id', '!=', $shipmentItem->id)
        ->orderBy('id')
        ->get();

      if ($openItems->isEmpty()) continue;

      // 6. Redistribute diff across open items (first-come-first-served per size)
      $remainingDiff = $diffs; // per-size remaining diff to distribute

      foreach ($openItems as $openItem) {
        // Check if there is still any diff left to distribute
        $anyLeft = false;
        foreach ($remainingDiff as $diff) {
          if ($diff !== 0) { $anyLeft = true; break; }
        }
        if (!$anyLeft) break;

        $openSizes = collect($openItem->sizes)->keyBy('size');
        $changed = false;

        foreach ($remainingDiff as $size => $diff) {
          if ($diff === 0) continue;

          $currentQty = (int)($openSizes->get((string)$size)['quantity'] ?? 0);
          $newQty     = max(0, $currentQty + $diff);
          $applied    = $newQty - $currentQty; // actual change (may be less than diff for over-prod)

          $openSizes->put((string)$size, ['size' => (string)$size, 'quantity' => $newQty]);
          $remainingDiff[$size] -= $applied;
          $changed = true;
        }

        if (!$changed) continue;

        $newOpenSizes = $openSizes->values()->toArray();
        $newQtyTotal  = collect($newOpenSizes)->sum('quantity');

        // Delete open item if it reaches zero — no production left to plan
        if ($newQtyTotal === 0) {
          $openItem->delete();
        } else {
          $openItem->update([
            'sizes'    => $newOpenSizes,
            'quantity' => $newQtyTotal,
          ]);
        }
      }
    }

    // 7. Recompute Shipment.total_items
    $model->total_items = $totalShipped;
    $model->save();

    // 8. For each affected OrderItem: compute extraQuantity per size
    foreach (array_unique($affectedOrderItemIds) as $orderItemId) {
      $orderItem = OrderItem::find($orderItemId);
      if (!$orderItem) continue;

      // Sum quantities per size across ALL ShipmentItems for this OrderItem
      $allShipmentItems = ShipmentItem::where('order_item_id', $orderItemId)->get();
      $totalPerSize = [];
      foreach ($allShipmentItems as $si) {
        foreach ($si->sizes as $entry) {
          $sz = (string)($entry['size']);
          $totalPerSize[$sz] = ($totalPerSize[$sz] ?? 0) + (int)($entry['quantity']);
        }
      }

      // Compare with OrderItem.sizes and write extraQuantity where applicable
      $updatedOrderSizes = [];
      foreach ($orderItem->sizes as $entry) {
        $sz = (string)($entry['size']);
        $ordered = (int)($entry['quantity']);
        $produced = (int)($totalPerSize[$sz] ?? 0);
        $extra = max(0, $produced - $ordered);

        $newEntry = ['size' => $entry['size'], 'quantity' => $ordered];
        if ($extra > 0) $newEntry['extraQuantity'] = $extra;

        $updatedOrderSizes[] = $newEntry;
      }

      $orderItem->update(['sizes' => $updatedOrderSizes]);
    }
  }

  public function getGroupData(?object $params): Collection
  {
    $filter = $params->filter ?? [];
    $response = new Collection();
    if (isset($filter->getUniqueAccounts)) {
      $response = $this->model->query()
        ->from('ifulfillment__shipments as s')
        ->join('iaccount__accounts as a', 'a.id', '=', 's.account_id')
        ->select([
          'a.id as id',
          'a.title as title'
        ])
        ->groupBy('a.id', 'a.title')
        ->get();
    }
    if (isset($filter->getUniqueCities)) {
      $response = $this->model->query()
        ->from('ifulfillment__shipments as s')
        ->join('ilocation__locatables as lt', 'lt.id', '=', 's.locatable_id')
        ->join('ilocation__city_translations as lc', 'lc.city_id', '=', 'lt.city_id')
        ->select([
          'lc.id as id',
          'lc.title as title'
        ])
        ->groupBy('lc.id', 'lc.title')
        ->get();
    }

    return $response;
  }
}
