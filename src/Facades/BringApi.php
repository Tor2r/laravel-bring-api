<?php

namespace Tor2r\BringApi\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Tor2r\BringApi\BringApi
 */
class BringApi extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \Tor2r\BringApi\BringApi::class;
    }
}
