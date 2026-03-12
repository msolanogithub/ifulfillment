<?php

namespace Modules\Ifulfillment\Http\Controllers\Api;

use Imagina\Icore\Http\Controllers\CoreApiController;
//Model
use Modules\Ifulfillment\Models\Trace;
use Modules\Ifulfillment\Repositories\TraceRepository;

class TraceApiController extends CoreApiController
{
  public function __construct(Trace $model, TraceRepository $modelRepository)
  {
    parent::__construct($model, $modelRepository);
  }
}
