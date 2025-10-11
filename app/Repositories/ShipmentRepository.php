<?php

namespace Modules\Ifulfillment\Repositories;

use Illuminate\Database\Eloquent\Collection;
use Imagina\Icore\Repositories\CoreRepository;

interface ShipmentRepository extends CoreRepository
{
  public function getGroupData(?object $params): Collection;
}
