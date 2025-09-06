<?php

namespace Modules\Ifulfillment\Repositories\Cache;

use Modules\Ifulfillment\Repositories\OrderRepository;
use Imagina\Icore\Repositories\Cache\CoreCacheDecorator;

class CacheOrderDecorator extends CoreCacheDecorator implements OrderRepository
{
    public function __construct(OrderRepository $order)
    {
        parent::__construct();
        $this->entityName = 'ifulfillment.orders';
        $this->repository = $order;
    }
}
