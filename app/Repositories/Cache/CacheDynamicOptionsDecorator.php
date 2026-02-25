<?php

namespace Modules\Ifulfillment\Repositories\Cache;

use Modules\Ifulfillment\Repositories\DynamicOptionsRepository;
use Imagina\Icore\Repositories\Cache\CoreCacheDecorator;

class CacheDynamicOptionsDecorator extends CoreCacheDecorator implements DynamicOptionsRepository
{
    public function __construct(DynamicOptionsRepository $dynamicoptions)
    {
        parent::__construct();
        $this->entityName = 'ifulfillment.dynamicoptions';
        $this->repository = $dynamicoptions;
    }
}
