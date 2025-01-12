<?php

namespace Hiworld\LaravelIcbc\lib;

class AES
{
    public static function aesEncrypt($content, $encryptKey, $charset)
    {
        try {
            if (empty($content)) {
                return null;
            }
            if ($charset) {
                $content = mb_convert_encoding($content, "UTF-8", $charset);
            }
            $key = base64_decode($encryptKey);
            $iv = str_repeat("\0", 16);
            $encrypted = openssl_encrypt($content, 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
            return base64_encode($encrypted);
        } catch (\Exception $e) {
            throw new \Exception("AES Encrypt Error: " . $e->getMessage());
        }
    }

    public static function aesDecrypt($content, $encryptKey, $charset)
    {
        try {
            if (empty($content)) {
                return null;
            }
            $key = base64_decode($encryptKey);
            $iv = str_repeat("\0", 16);
            $decrypted = openssl_decrypt(base64_decode($content), 'AES-128-CBC', $key, OPENSSL_RAW_DATA, $iv);
            if ($charset) {
                $decrypted = mb_convert_encoding($decrypted, $charset, "UTF-8");
            }
            return $decrypted;
        } catch (\Exception $e) {
            throw new \Exception("AES Decrypt Error: " . $e->getMessage());
        }
    }
} 