<?php

namespace IcbcSdk\lib;

use IcbcSdk\lib\AES;
use IcbcSdk\lib\IcbcConstants;

class IcbcEncrypt
{
	public static function encryptContent($content, $encryptType, $encryptKey, $charset)
	{
		if ($encryptType == IcbcConstants::$ENCRYPT_TYPE_AES) {
			return AES::aesEncrypt($content, $encryptKey, $charset);
		}
		throw new \Exception("Only support AES encrypt!");
	}

	public static function decryptContent($content, $encryptType, $encryptKey, $charset)
	{
		if ($encryptType == IcbcConstants::$ENCRYPT_TYPE_AES) {
			return AES::aesDecrypt($content, $encryptKey, $charset);
		}
		throw new \Exception("Only support AES decrypt!");
	}
}
?>