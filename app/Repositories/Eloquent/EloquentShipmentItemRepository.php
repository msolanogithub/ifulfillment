<?php

namespace Modules\Ifulfillment\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Modules\Ifulfillment\Repositories\ShipmentItemRepository;
use Imagina\Icore\Repositories\Eloquent\EloquentCoreRepository;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class EloquentShipmentItemRepository extends EloquentCoreRepository implements ShipmentItemRepository
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

    if (isset($filter->shoeId)) {
      $query->select('si.*')
        ->from('ifulfillment__shipment_items as si')
        ->join('ifulfillment__order_items as oi', 'oi.id', '=', 'si.order_item_id')
        ->whereNull('si.shipping_id')
        ->where('oi.shoe_id', $filter->shoeId);
    }

    if (isset($filter->accountId)) {
      $query->select('si.*')
        ->from('ifulfillment__shipment_items as si')
        ->join('ifulfillment__order_items as oi', 'oi.id', '=', 'si.order_item_id')
        ->join('ifulfillment__orders as o', 'o.id', '=', 'oi.order_id')
        ->whereNull('si.shipping_id')
        ->where('o.account_id', $filter->accountId);
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

  public function getGroupData(?object $params): Collection
  {
    $filter = $params->filter ?? [];
    $response = new Collection();
    if (isset($filter->getUniqueShoes)) {
      $locale = app()->getLocale();
      $response = $this->model->query()
        ->from('ifulfillment__shipment_items as si')
        ->join('ifulfillment__order_items as oi', 'oi.id', '=', 'si.order_item_id')
        ->join('ishoe__shoes as s', 's.id', '=', 'oi.shoe_id')
        ->join('ishoe__shoe_translations as st', function ($join) use ($locale) {
          $join->on('st.shoe_id', '=', 's.id')->where('st.locale', '=', $locale);
        })
        ->whereNull('si.shipping_id')
        ->select([
          'oi.shoe_id as id',
          'st.title as title',
          DB::raw('SUM(si.quantity) AS quantity'),
        ])
        ->groupBy('oi.shoe_id', 'st.title')
        ->get();
    }
    if (isset($filter->getUniqueAccounts)) {
      $response = $this->model->query()
        ->from('ifulfillment__shipment_items as si')
        ->join('ifulfillment__order_items as oi', 'oi.id', '=', 'si.order_item_id')
        ->join('ifulfillment__orders as o', 'o.id', '=', 'oi.order_id')
        ->join('iaccount__accounts as a', 'a.id', '=', 'o.account_id')
        ->whereNull('si.shipping_id')
        ->select([
          'a.id as id',
          'a.title as title',
          DB::raw('SUM(si.quantity) as quantity')
        ])
        ->groupBy('a.id', 'a.title')
        ->get();
    }
    return $response;
  }
}
