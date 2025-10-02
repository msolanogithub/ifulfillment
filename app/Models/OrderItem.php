<?php

namespace Modules\Ifulfillment\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Imagina\Icore\Models\CoreModel;
use Modules\Ishoe\Models\Shoe;

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
    'shoe_id',
    'quantity',
    'options',
    'sizes'
  ];

  protected $casts = [
    'options' => 'array',
    'sizes' => 'array'
  ];

  public function order(): BelongsTo
  {
    return $this->belongsTo(Order::class, 'order_id');
  }

  public function shoe(): BelongsTo
  {
    return $this->belongsTo(Shoe::class);
  }

  public function shipmentItems(): hasMany
  {
    return $this->hasMany(ShipmentItem::class, 'order_item_id');
  }
}
