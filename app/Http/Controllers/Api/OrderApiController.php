<?php

namespace Modules\Ifulfillment\Http\Controllers\Api;

use Imagina\Icore\Http\Controllers\CoreApiController;
//Model
use Modules\Ifulfillment\Models\Order;
use Modules\Ifulfillment\Repositories\OrderRepository;

class OrderApiController extends CoreApiController
{
  public function __construct(Order $model, OrderRepository $modelRepository)
  {
    parent::__construct($model, $modelRepository);
  }
}
