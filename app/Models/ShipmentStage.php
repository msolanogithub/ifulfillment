<?php

namespace Modules\Ifulfillment\Models;

use Imagina\Icore\Models\CoreStaticModel;

class ShipmentStage extends CoreStaticModel
{
  const PREPARATION = 0;
  const DONE = 1;

  public function __construct()
  {
    $this->records = [
      self::PREPARATION => [
        'id' => self::PREPARATION,
        'title' => 'En preparación',
        'icon' => 'fa-light fa-spinner-scale',
        'color' => 'orange',
      ],
      self::DONE => [
        'id' => self::DONE,
        'title' => 'Completado',
        'icon' => 'fa-light fa-truck-fast',
        'color' => 'green',
      ],
    ];
  }
}
