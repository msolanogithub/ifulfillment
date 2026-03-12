<?php

namespace Modules\Ifulfillment\Repositories\Cache;

use Modules\Ifulfillment\Repositories\TraceRepository;
use Imagina\Icore\Repositories\Cache\CoreCacheDecorator;

class CacheTraceDecorator extends CoreCacheDecorator implements TraceRepository
{
    public function __construct(TraceRepository $trace)
    {
        parent::__construct();
        $this->entityName = 'ifulfillment.traces';
        $this->repository = $trace;
    }
}
