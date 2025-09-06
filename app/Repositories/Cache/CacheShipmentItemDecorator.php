<?php

namespace Modules\Ifulfillment\Repositories\Cache;

use Modules\Ifulfillment\Repositories\ShipmentItemRepository;
use Imagina\Icore\Repositories\Cache\CoreCacheDecorator;

class CacheShipmentItemDecorator extends CoreCacheDecorator implements ShipmentItemRepository
{
    public function __construct(ShipmentItemRepository $shipmentitem)
    {
        parent::__construct();
        $this->entityName = 'ifulfillment.shipmentitems';
        $this->repository = $shipmentitem;
    }
}
