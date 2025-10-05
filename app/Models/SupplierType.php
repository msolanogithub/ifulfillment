<?php

namespace Modules\Ifulfillment\Models;

use Imagina\Icore\Models\CoreStaticModel;

class SupplierType extends CoreStaticModel
{
  const MANUFACTURING = 0;
  const INVENTORY = 1;
  const EXTERNAL = 2;

  public function __construct()
  {
    $this->records = [
      self::MANUFACTURING => [
        'id' => self::MANUFACTURING,
        'title' => 'Fabricación',
        'icon' => 'fa-light fa-industry-windows'
      ],
      self::INVENTORY => [
        'id' => self::INVENTORY,
        'title' => 'Inventario',
        'icon' => 'fa-light fa-shelves'
      ],
      self::EXTERNAL => [
        'id' => self::EXTERNAL,
        'title' => 'Externo',
        'icon' => 'fa-light fa-boxes-packing'
      ]
    ];
  }
}
