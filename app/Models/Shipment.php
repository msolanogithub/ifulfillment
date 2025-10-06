<?php

namespace Modules\Ifulfillment\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Imagina\Icore\Models\CoreModel;
use Modules\Iaccount\Models\Account;

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
    'account_id',
    'parent_id',
    'total_items',
    'shipped_at',
    'comments',
    'units_per_package',
    'packages_total'
  ];

  public function account(): BelongsTo
  {
    return $this->belongsTo(Account::class, 'account_id');
  }

  public function parent(): BelongsTo
  {
    return $this->belongsTo(Shipment::class, 'parent_id');
  }

  public function children(): HasMany
  {
    return $this->hasMany(Shipment::class, 'parent_id');
  }

  public function items(): HasMany
  {
    return $this->hasMany(ShipmentItem::class, 'shipping_id', 'id');
  }
}
