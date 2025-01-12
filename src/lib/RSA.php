<?php

namespace Hiworld\LaravelIcbc\lib;

class RSA
{
    public static function sign($data, $privateKey, $charset)
    {
        if ($charset) {
            $data = mb_convert_encoding($data, "UTF-8", $charset);
        }
        error_log("RSA Sign Data: " . $data);
        error_log("RSA Private Key: " . substr($privateKey, 0, 64) . "...");
        
        $res = openssl_get_privatekey($privateKey);
        if (!$res) {
            throw new \Exception("Private Key Error: " . openssl_error_string());
        }
        if (!openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA1)) {
            throw new \Exception("Sign Error: " . openssl_error_string());
        }
        $signBase64 = base64_encode($sign);
        error_log("RSA Sign Result: " . $signBase64);
        return $signBase64;
    }

    public static function sign256($data, $privateKey, $charset)
    {
        if ($charset) {
            $data = mb_convert_encoding($data, "UTF-8", $charset);
        }
        error_log("RSA2 Sign Data: " . $data);
        error_log("RSA2 Private Key: " . substr($privateKey, 0, 64) . "...");
        
        $res = openssl_get_privatekey($privateKey);
        if (!$res) {
            throw new \Exception("Private Key Error: " . openssl_error_string());
        }
        if (!openssl_sign($data, $sign, $res, OPENSSL_ALGO_SHA256)) {
            throw new \Exception("Sign Error: " . openssl_error_string());
        }
        $signBase64 = base64_encode($sign);
        error_log("RSA2 Sign Result: " . $signBase64);
        return $signBase64;
    }

    public static function verify($data, $publicKey, $charset, $sign)
    {
        if ($charset) {
            $data = mb_convert_encoding($data, "UTF-8", $charset);
        }
        error_log("RSA Verify Data: " . $data);
        error_log("RSA Public Key: " . substr($publicKey, 0, 64) . "...");
        error_log("RSA Sign to Verify: " . $sign);
        
        $res = openssl_get_publickey($publicKey);
        if (!$res) {
            throw new \Exception("Public Key Error: " . openssl_error_string());
        }
        $result = openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA1);
        if ($result === -1) {
            throw new \Exception("Verify Error: " . openssl_error_string());
        }
        error_log("RSA Verify Result: " . ($result === 1 ? "Success" : "Failed"));
        return $result === 1;
    }

    public static function verify256($data, $publicKey, $charset, $sign)
    {
        if ($charset) {
            $data = mb_convert_encoding($data, "UTF-8", $charset);
        }
        error_log("RSA2 Verify Data: " . $data);
        error_log("RSA2 Public Key: " . substr($publicKey, 0, 64) . "...");
        error_log("RSA2 Sign to Verify: " . $sign);
        
        $res = openssl_get_publickey($publicKey);
        if (!$res) {
            throw new \Exception("Public Key Error: " . openssl_error_string());
        }
        $result = openssl_verify($data, base64_decode($sign), $res, OPENSSL_ALGO_SHA256);
        if ($result === -1) {
            throw new \Exception("Verify Error: " . openssl_error_string());
        }
        error_log("RSA2 Verify Result: " . ($result === 1 ? "Success" : "Failed"));
        return $result === 1;
    }

    public static function signSM2($data, $privateKey, $charset)
    {
        throw new \Exception("SM2 Sign Not Supported");
    }

    public static function verifySM2($data, $publicKey, $charset, $sign)
    {
        throw new \Exception("SM2 Verify Not Supported");
    }
} 