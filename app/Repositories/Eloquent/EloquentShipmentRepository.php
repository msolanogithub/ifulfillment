<?php

namespace Modules\Ifulfillment\Repositories\Eloquent;

use Illuminate\Database\Eloquent\Collection;
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
      ShipmentItem::whereIn('id', $data['items'])
        ->update(['shipping_id' => $model->id]);
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
