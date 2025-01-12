<?php

namespace Hiworld\LaravelIcbc\lib;

class IcbcCa
{
    public static function sign($data, $pfxPath, $password)
    {
        throw new \Exception("CA Sign Not Supported");
    }

    public static function verify($data, $cerPath, $sign)
    {
        throw new \Exception("CA Verify Not Supported");
    }
} 