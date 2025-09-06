<?php

namespace Modules\Ifulfillment\Http\Controllers\Api;

use Imagina\Icore\Http\Controllers\CoreApiController;
//Model
use Modules\Ifulfillment\Models\OrderItem;
use Modules\Ifulfillment\Repositories\OrderItemRepository;

class OrderItemApiController extends CoreApiController
{
  public function __construct(OrderItem $model, OrderItemRepository $modelRepository)
  {
    parent::__construct($model, $modelRepository);
  }
}
