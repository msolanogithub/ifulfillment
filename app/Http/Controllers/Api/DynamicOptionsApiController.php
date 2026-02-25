<?php

namespace Modules\Ifulfillment\Http\Controllers\Api;

use Imagina\Icore\Http\Controllers\CoreApiController;
//Model
use Modules\Ifulfillment\Models\DynamicOptions;
use Modules\Ifulfillment\Repositories\DynamicOptionsRepository;

class DynamicOptionsApiController extends CoreApiController
{
  public function __construct(DynamicOptions $model, DynamicOptionsRepository $modelRepository)
  {
    parent::__construct($model, $modelRepository);
  }
}
