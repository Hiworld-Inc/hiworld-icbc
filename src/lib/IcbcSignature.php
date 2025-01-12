<?php

namespace IcbcSdk\lib;

use IcbcSdk\lib\RSA;
use IcbcSdk\lib\IcbcCa;
use IcbcSdk\lib\IcbcConstants;

class IcbcSignature
{
	public static function sign($strToSign, $signType, $privateKey, $charset, $password)
	{
		if ($signType == IcbcConstants::$SIGN_TYPE_RSA || $signType == IcbcConstants::$SIGN_TYPE_RSA2) {
			return RSA::sign($strToSign, $privateKey, $charset);
		} elseif ($signType == IcbcConstants::$SIGN_TYPE_CA) {
			return IcbcCa::sign($strToSign, $privateKey, $password);
		} else {
			throw new \Exception("Only support CA or RSA signature!");
		}
	}

	public static function verify($strToSign, $signType, $publicKey, $charset, $sign)
	{
		if ($signType == IcbcConstants::$SIGN_TYPE_RSA || $signType == IcbcConstants::$SIGN_TYPE_RSA2) {
			return RSA::verify($strToSign, $publicKey, $charset, $sign);
		} elseif ($signType == IcbcConstants::$SIGN_TYPE_CA) {
			return IcbcCa::verify($strToSign, $publicKey, $sign);
		} else {
			throw new \Exception("Only support CA or RSA signature!");
		}
	}
}
?>