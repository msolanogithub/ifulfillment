<?php

namespace Modules\Ifulfillment\Repositories\Eloquent;

use Modules\Ifulfillment\Models\Order;
use Modules\Ifulfillment\Repositories\OrderItemRepository;
use Imagina\Icore\Repositories\Eloquent\EloquentCoreRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EloquentOrderItemRepository extends EloquentCoreRepository implements OrderItemRepository
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

    if (isset($filter->orderByDueDate)) {
      $query->with('order')->orderBy(
        Order::select('due_date')->whereColumn('ifulfillment__orders.id', 'ifulfillment__order_items.order_id'),
        $filter->orderByDueDate
      );
    }

    if (isset($filter->getUniqueShoes)) {
      $query->select('shoe_id')
        ->selectRaw('SUM(quantity) as shoes_quantity')
        ->groupBy('shoe_id');
    }

    if (isset($filter->accountId)) {
      $query->whereHas('order', function ($q) use ($filter) {
        $q->where('account_id', $filter->accountId);
      });
    }

    if (isset($filter->cityId)) {
      $query->whereHas('order.locatable', function ($q) use ($filter) {
        $q->where('city_id', $filter->cityId);
      });
    }

    if (isset($filter->withPendingQuantity)) {
      $query->whereRaw(
        'COALESCE((
          SELECT SUM(si.quantity)
          FROM ifulfillment__shipment_items si
          WHERE si.order_item_id = ifulfillment__order_items.id
       ), 0) <> ifulfillment__order_items.quantity'
      );
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
}
