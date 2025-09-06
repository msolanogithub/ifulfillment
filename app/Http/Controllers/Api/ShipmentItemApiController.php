<?php

namespace Modules\Ifulfillment\Http\Controllers\Api;

use Imagina\Icore\Http\Controllers\CoreApiController;
//Model
use Modules\Ifulfillment\Models\ShipmentItem;
use Modules\Ifulfillment\Repositories\ShipmentItemRepository;

class ShipmentItemApiController extends CoreApiController
{
  public function __construct(ShipmentItem $model, ShipmentItemRepository $modelRepository)
  {
    parent::__construct($model, $modelRepository);
  }
}
