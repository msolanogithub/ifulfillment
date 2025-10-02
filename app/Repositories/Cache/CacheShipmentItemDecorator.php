<?php

namespace Modules\Ifulfillment\Repositories\Cache;

use Modules\Ifulfillment\Repositories\ShipmentItemRepository;
use Imagina\Icore\Repositories\Cache\CoreCacheDecorator;
use Illuminate\Database\Eloquent\Collection;

class CacheShipmentItemDecorator extends CoreCacheDecorator implements ShipmentItemRepository
{
  public function __construct(ShipmentItemRepository $shipmentitem)
  {
    parent::__construct();
    $this->entityName = 'ifulfillment.shipmentitems';
    $this->repository = $shipmentitem;
  }

  public function getGroupData(?object $params): Collection
  {
    return $this->remember(function () use ($params) {
      return $this->repository->getGroupData($params);
    }, $this->makeCacheKey(null, null, $params));
  }
}
