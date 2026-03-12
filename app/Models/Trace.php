<?php

namespace Modules\Ifulfillment\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Imagina\Icore\Models\CoreModel;

class Trace extends CoreModel
{

  protected $table = 'ifulfillment__traces';
  public string $transformer = 'Modules\Ifulfillment\Transformers\TraceTransformer';
  public string $repository = 'Modules\Ifulfillment\Repositories\TraceRepository';
  public array $requestValidation = [
    'create' => 'Modules\Ifulfillment\Http\Requests\CreateTraceRequest',
    'update' => 'Modules\Ifulfillment\Http\Requests\UpdateTraceRequest',
  ];
  //Instance external/internal events to dispatch with extraData
  public array $dispatchesEventsWithBindings = [
    'created'  => [],
    'creating' => [],
    'updated'  => [],
    'updating' => [],
    'deleting' => [],
    'deleted'  => []
  ];

  protected $fillable = [
    'traceable_type',
    'traceable_id',
    'type',
    'payload',
    'created_by',
  ];

  protected $casts = [
    'payload' => 'array',
  ];

  // ─── Scopes ───────────────────────────────────────────────────────────────

  public function scopeOfType($query, string $type)
  {
    return $query->where('type', $type);
  }

  // ─── Relations ────────────────────────────────────────────────────────────

  /**
   * The parent model (Order, Shipment, etc.)
   */
  public function traceable(): MorphTo
  {
    return $this->morphTo();
  }

  /**
   * The user who triggered the trace (null = system-generated)
   */
  public function author(): BelongsTo
  {
    return $this->belongsTo('Modules\Iauth\Models\User', 'created_by');
  }

  // ─── Helpers ──────────────────────────────────────────────────────────────

  /**
   * Create a trace record for any model.
   *
   * Usage:
   *   Trace::log($order, 'comment', ['text' => 'Some note']);
   *   Trace::log($shipment, 'production_adjusted', [...]);
   */
  public static function log(CoreModel $model, string $type, array $payload = []): static
  {
    return static::create([
      'traceable_type' => get_class($model),
      'traceable_id'   => $model->getKey(),
      'type'           => $type,
      'payload'        => $payload ?: null,
      'created_by'     => auth()->id(),
    ]);
  }
}
