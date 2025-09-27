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
  protected $fillable = [
    'account_id',
    'locatable_id',
    'external_id',
    'comment',
    'due_date',
    'quantity',
    'price'
  ];
  public array $dispatchesEventsWithBindings = [
    //eg. ['path' => 'path/module/event', 'extraData' => [/*...optional*/]]
    'created' => [
      ['path' => 'Modules\Imedia\Events\CreateMedia']
    ],
    'creating' => [],
    'updated' => [
      ['path' => 'Modules\Imedia\Events\UpdateMedia']
    ],
    'updating' => [],
    'deleting' => [
      ['path' => 'Modules\Imedia\Events\DeleteMedia']
    ],
    'deleted' => []
  ];
  public array $mediaFillable = [
    'mainfile' => 'single'
  ];

  public function account(): BelongsTo
  {
    return $this->belongsTo('Modules\Iaccount\Models\Account', 'account_id');
  }

  public function locatable(): BelongsTo
  {
    return $this->belongsTo('Modules\Ilocation\Models\Locatable', 'locatable_id');
  }

  public function items(): HasMany
  {
    return $this->hasMany(OrderItem::class, 'order_id');
  }

  public function files()
  {
    if (isModuleEnabled('Imedia')) {
      return app(\Modules\Imedia\Relations\FilesRelation::class)->resolve($this);
    }
    return new \Imagina\Icore\Relations\EmptyRelation();
  }
}
