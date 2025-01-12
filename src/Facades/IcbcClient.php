<?php

namespace IcbcSdk\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static mixed execute(array $request, string $msgId, string $appAuthToken = null)
 * @method static mixed pay(array $params)
 * @method static mixed query(array $params)
 * @method static mixed refund(array $params)
 * @method static mixed cancel(array $params)
 */
class IcbcClient extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'icbc';
    }
} 