<?php

namespace Modules\Ifulfillment\Repositories\Cache;

use Illuminate\Database\Eloquent\Collection;
use Modules\Ifulfillment\Repositories\ShipmentRepository;
use Imagina\Icore\Repositories\Cache\CoreCacheDecorator;

class CacheShipmentDecorator extends CoreCacheDecorator implements ShipmentRepository
{
  public function __construct(ShipmentRepository $shipment)
  {
    parent::__construct();
    $this->entityName = 'ifulfillment.shipments';
    $this->repository = $shipment;
  }

  public function getGroupData(?object $params): Collection
  {
    return $this->remember(function () use ($params) {
      return $this->repository->getGroupData($params);
    }, $this->makeCacheKey(null, null, $params));
  }
}
