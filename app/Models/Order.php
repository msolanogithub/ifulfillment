<?php

namespace Modules\Ifulfillment\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Imagina\Icore\Models\CoreModel;

class Order extends CoreModel
{

  protected $table = 'ifulfillment__orders';
  public string $transformer = 'Modules\Ifulfillment\Transformers\OrderTransformer';
  public string $repository = 'Modules\Ifulfillment\Repositories\OrderRepository';
  public array $requestValidation = [
    'create' => 'Modules\Ifulfillment\Http\Requests\CreateOrderRequest',
    'update' => 'Modules\Ifulfillment\Http\Requests\UpdateOrderRequest',
  ];
  public array $modelRelations = [
    //eg. 'relationName' => 'belongsToMany/hasMany',
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
    'external_id',
    'account_id',
    'due_date',
    'price',
    'quantity'
  ];

  public function account(): BelongsTo
  {
    return $this->belongsTo('Modules\Iaccount\Models\Account', 'account_id');
  }

  public function items(): HasMany
  {
    return $this->hasMany(OrderItem::class, 'order_id');
  }
}
