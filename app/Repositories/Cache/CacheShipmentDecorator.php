<?php

namespace Modules\Ifulfillment\Repositories\Cache;

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
}
