<?php

namespace Modules\Ifulfillment\Repositories\Cache;

use Modules\Ifulfillment\Repositories\OrderItemRepository;
use Imagina\Icore\Repositories\Cache\CoreCacheDecorator;

class CacheOrderItemDecorator extends CoreCacheDecorator implements OrderItemRepository
{
    public function __construct(OrderItemRepository $orderitem)
    {
        parent::__construct();
        $this->entityName = 'ifulfillment.orderitems';
        $this->repository = $orderitem;
    }
}
