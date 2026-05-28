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
        'icon' => 'factory'
      ],
      self::INVENTORY => [
        'id' => self::INVENTORY,
        'title' => 'Inventario',
        'icon' => 'widgets'
      ],
      self::EXTERNAL => [
        'id' => self::EXTERNAL,
        'title' => 'Externo',
        'icon' => 'handshake'
      ]
    ];
  }
}
