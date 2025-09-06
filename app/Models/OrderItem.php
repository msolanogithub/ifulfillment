<?php

namespace Modules\Ifulfillment\Models;

use Imagina\Icore\Models\CoreModel;

class OrderItem extends CoreModel
{

  protected $table = 'ifulfillment__order_items';
  public string $transformer = 'Modules\Ifulfillment\Transformers\OrderItemTransformer';
  public string $repository = 'Modules\Ifulfillment\Repositories\OrderItemRepository';
  public array $requestValidation = [
      'create' => 'Modules\Ifulfillment\Http\Requests\CreateOrderItemRequest',
      'update' => 'Modules\Ifulfillment\Http\Requests\UpdateOrderItemRequest',
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
    'order_id',
    'entity_id',
    'entity_type',
    'entity_data',
    'quantity',
  ];

  public function order(){
    return $this->belongsTo(Order::class, 'order_id');
  }

  public function entity()
  {
    return $this->morphTo(__FUNCTION__, 'entity_type', 'entity_id');
  }
}
