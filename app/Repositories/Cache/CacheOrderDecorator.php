<?php

namespace Modules\Ifulfillment\Repositories\Cache;

use Illuminate\Database\Eloquent\Collection;
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

  public function getGroupData(?object $params): Collection
  {
    return $this->remember(function () use ($params) {
      return $this->repository->getGroupData($params);
    }, $this->makeCacheKey(null, null, $params));
  }
}
