<?php

namespace Modules\Ifulfillment\Models;

use Imagina\Icore\Models\CoreModel;

class DynamicOptions extends CoreModel
{
  protected $table = 'ifulfillment__dynamic_options';
  public string $transformer = 'Modules\Ifulfillment\Transformers\DynamicOptionsTransformer';
  public string $repository = 'Modules\Ifulfillment\Repositories\DynamicOptionsRepository';
  public array $requestValidation = [
    'create' => 'Modules\Ifulfillment\Http\Requests\CreateDynamicOptionsRequest',
    'update' => 'Modules\Ifulfillment\Http\Requests\UpdateDynamicOptionsRequest',
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
    'type',
    'value'
  ];

  //Attributes used by the search filter. Already added by default: id,title; but you can add more attributes
  //public $searchable = []
}
