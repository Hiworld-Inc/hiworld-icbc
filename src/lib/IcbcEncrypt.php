<?php

namespace Hiworld\LaravelIcbc\lib;

class IcbcEncrypt
{
    public static function encryptContent($content, $encryptType, $encryptKey, $charset)
    {
        if ($encryptType == IcbcConstants::$ENCRYPT_TYPE_AES) {
            return AES::aesEncrypt($content, $encryptKey, $charset);
        }
        throw new \Exception("Encrypt Type is Not Support : " . $encryptType);
    }

    public static function decryptContent($content, $encryptType, $encryptKey, $charset)
    {
        if ($encryptType == IcbcConstants::$ENCRYPT_TYPE_AES) {
            return AES::aesDecrypt($content, $encryptKey, $charset);
        }
        throw new \Exception("Encrypt Type is Not Support : " . $encryptType);
    }
} 