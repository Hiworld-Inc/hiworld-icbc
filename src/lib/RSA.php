<?php

namespace IcbcSdk\lib;

class RSA
{
	public static function sign($data, $privateKey, $charset)
	{
		$privateKey = "-----BEGIN RSA PRIVATE KEY-----\n" .
			wordwrap($privateKey, 64, "\n", true) .
			"\n-----END RSA PRIVATE KEY-----";
			
		$key = openssl_get_privatekey($privateKey);
		openssl_sign($data, $signature, $key, OPENSSL_ALGO_SHA1);
		openssl_free_key($key);
		return base64_encode($signature);
	}

	public static function verify($data, $publicKey, $charset, $sign)
	{
		$publicKey = "-----BEGIN PUBLIC KEY-----\n" .
			wordwrap($publicKey, 64, "\n", true) .
			"\n-----END PUBLIC KEY-----";
			
		$key = openssl_get_publickey($publicKey);
		$result = openssl_verify($data, base64_decode($sign), $key, OPENSSL_ALGO_SHA1);
		openssl_free_key($key);
		return $result === 1;
	}
}
?>