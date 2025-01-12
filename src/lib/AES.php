<?php

namespace IcbcSdk\lib;

class AES
{
    private static $method = 'AES-128-CBC';
    private static $ivLength = 16;

    public static function aesEncrypt($plaintext, $key)
    {
        if (empty($plaintext)) return '';
        $key = substr($key, 0, 16);
        $iv = str_repeat("\0", self::$ivLength);
        $padded = self::pkcs7Pad($plaintext);
        return base64_encode(openssl_encrypt($padded, self::$method, $key, OPENSSL_RAW_DATA, $iv));
    }

    public static function aesDecrypt($encrypted, $key)
    {
        if (empty($encrypted)) return '';
        $key = substr($key, 0, 16);
        $iv = str_repeat("\0", self::$ivLength);
        $decrypted = openssl_decrypt(base64_decode($encrypted), self::$method, $key, OPENSSL_RAW_DATA, $iv);
        return self::pkcs7Unpad($decrypted);
    }

    private static function pkcs7Pad($data)
    {
        $blockSize = self::$ivLength;
        $padding = $blockSize - (strlen($data) % $blockSize);
        return $data . str_repeat(chr($padding), $padding);
    }

    private static function pkcs7Unpad($data)
    {
        $padding = ord($data[strlen($data) - 1]);
        return substr($data, 0, -$padding);
    }
}