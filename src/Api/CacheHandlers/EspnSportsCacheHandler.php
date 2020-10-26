<?php

namespace Ingelby\Espn\Api\CacheHandlers;

use Ingelby\Espn\Api\EspnSportsHandler;
use Ingelby\Espn\Models\SportModel;


class EspnSportsCacheHandler extends CacheHandler
{
    public function __construct(EspnSportsHandler $handler, ?int $ttl = null)
    {
        parent::__construct($handler, $ttl);
    }
}
