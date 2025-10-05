<?php

namespace Modules\Ifulfillment\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    'sizes',
    'supplier_id',
    'stage_id',
  ];

  protected $casts = [
    'sizes' => 'array',
  ];

  protected $appends = [
    'supplier',
    'stage'
  ];

  public function shipment(): BelongsTo
  {
    return $this->belongsTo(Shipment::class, 'shipping_id');
  }

  public function orderItem(): BelongsTo
  {
    return $this->belongsTo(OrderItem::class, 'order_item_id');
  }

  public function supplier(): Attribute
  {
    return Attribute::get(function () {
      $model = new SupplierType();
      return $model->show($this->supplier_id);
    });
  }

  public function stage(): Attribute
  {
    return Attribute::get(function () {
      $model = new ShipmentItemStage();
      return $model->show($this->stage_id);
    });
  }
}
