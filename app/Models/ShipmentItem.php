<?php

namespace Modules\Ifulfillment\Models;

use Imagina\Icore\Models\CoreModel;

class ShipmentItem extends CoreModel
{

  protected $table = 'ifulfillment__shipment_items';
  public string $transformer = 'Modules\Ifulfillment\Transformers\ShipmentItemTransformer';
  public string $repository = 'Modules\Ifulfillment\Repositories\ShipmentItemRepository';
  public array $requestValidation = [
      'create' => 'Modules\Ifulfillment\Http\Requests\CreateShipmentItemRequest',
      'update' => 'Modules\Ifulfillment\Http\Requests\UpdateShipmentItemRequest',
    ];
  public array $modelRelations = [
    //eg. 'relationName' => 'belongsToMany/hasMany',
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
    'shipping_id',
    'order_item_id',
    'quantity',
  ];

  public function shipment(){
    return $this->belongsTo(Shipment::class, 'shipping_id');
  }

  public function orderItem(){
    return $this->belongsTo(OrderItem::class, 'order_item_id');
  }
}
