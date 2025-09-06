<?php

namespace Modules\Ifulfillment\Models;

use Imagina\Icore\Models\CoreModel;

class Shipment extends CoreModel
{

  protected $table = 'ifulfillment__shipments';
  public string $transformer = 'Modules\Ifulfillment\Transformers\ShipmentTransformer';
  public string $repository = 'Modules\Ifulfillment\Repositories\ShipmentRepository';
  public array $requestValidation = [
    'create' => 'Modules\Ifulfillment\Http\Requests\CreateShipmentRequest',
    'update' => 'Modules\Ifulfillment\Http\Requests\UpdateShipmentRequest',
  ];
  public array $modelRelations = [
    //eg. 'relationName' => 'belongsToMany/hasMany',
    'children' => 'hasMany',
    'items' => 'hasMany',
  ];
  //Instance external/internal events to dispatch with extraData
  public array $dispatchesEventsWithBindings = [
    //eg. ['path' => 'path/module/event', 'extraData' => [/*...optional*/]]
    'created' => [],
    'creating' => [],
    'updated' => [],
    'updating' => [],
    'deleting' => [],
    'deleted' => []
  ];
  protected $fillable = [
    'order_id',
    'parent_id',
    'total_items',
    'shipped_at',
    'comments',
    'quantity_per_index',
    'index',
    'total_index'
  ];

  public function order()
  {
    return $this->belongsTo(Order::class, 'order_id');
  }

  public function parent()
  {
    return $this->belongsTo(Shipment::class, 'parent_id');
  }

  public function children()
  {
    return $this->hasMany(Shipment::class, 'parent_id');
  }

  public function items()
  {
    return $this->hasMany(ShipmentItem::class, 'shipping_id');
  }
}
