<?php

namespace Hiworld\LaravelIcbc\lib;

class IcbcSignature
{
    public static function sign($strToSign, $signType, $privateKey, $charset, $password)
    {
        if ($signType == IcbcConstants::$SIGN_TYPE_RSA) {
            return RSA::sign($strToSign, $privateKey, $charset);
        } elseif ($signType == IcbcConstants::$SIGN_TYPE_RSA2) {
            return RSA::sign256($strToSign, $privateKey, $charset);
        } elseif ($signType == IcbcConstants::$SIGN_TYPE_SM2) {
            return RSA::signSM2($strToSign, $privateKey, $charset);
        } elseif ($signType == IcbcConstants::$SIGN_TYPE_CA) {
            return IcbcCa::sign($strToSign, $privateKey, $password);
        }
        throw new \Exception("Sign Type is Not Support : " . $signType);
    }

    public static function verify($strToSign, $signType, $publicKey, $charset, $sign, $password = null)
    {
        if ($signType == IcbcConstants::$SIGN_TYPE_RSA) {
            return RSA::verify($strToSign, $publicKey, $charset, $sign);
        } elseif ($signType == IcbcConstants::$SIGN_TYPE_RSA2) {
            return RSA::verify256($strToSign, $publicKey, $charset, $sign);
        } elseif ($signType == IcbcConstants::$SIGN_TYPE_SM2) {
            return RSA::verifySM2($strToSign, $publicKey, $charset, $sign);
        } elseif ($signType == IcbcConstants::$SIGN_TYPE_CA) {
            return IcbcCa::verify($strToSign, $publicKey, $sign, $password);
        }
        throw new \Exception("Sign Type is Not Support : " . $signType);
    }
} 