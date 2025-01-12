<?php

namespace Hiworld\LaravelIcbc\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static string execute(array $request, string $msgId, string $appAuthToken = null)
 * @method static \DefaultIcbcClient getClient()
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