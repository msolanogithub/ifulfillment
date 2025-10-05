<?php

namespace Modules\Ifulfillment\Models;

use Imagina\Icore\Models\CoreStaticModel;

class ShipmentItemStage extends CoreStaticModel
{
  const PREPARATION = 0;
  const READY_TO_SHIP = 1;

  public function __construct()
  {
    $this->records = [
      self::PREPARATION => [
        'id' => self::PREPARATION,
        'title' => 'En preparación',
        'icon' => 'fa-light fa-spinner-scale',
        'color' => 'orange',
      ],
      self::READY_TO_SHIP => [
        'id' => self::READY_TO_SHIP,
        'title' => 'Listo para despacho',
        'icon' => 'fa-light fa-truck-fast',
        'color' => 'green',
      ],
    ];
  }
}
