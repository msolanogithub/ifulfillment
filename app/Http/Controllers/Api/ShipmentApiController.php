<?php

namespace Modules\Ifulfillment\Http\Controllers\Api;

use Imagina\Icore\Http\Controllers\CoreApiController;
//Model
use Modules\Ifulfillment\Models\Shipment;
use Modules\Ifulfillment\Repositories\ShipmentRepository;

class ShipmentApiController extends CoreApiController
{
  public function __construct(Shipment $model, ShipmentRepository $modelRepository)
  {
    parent::__construct($model, $modelRepository);
  }
}
