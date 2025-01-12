<?php

namespace IcbcSdk\lib;

class IcbcCa
{
	public static function sign($content,$privatekey,$password){
		if (!extension_loaded('infosec')) {
			throw new \RuntimeException('ICBC CA requires infosec extension to be installed');
		}

		if (empty($content)) {
			throw new \Exception("no source data input");
		}

		$contents = base64_decode($privatekey);
		$key = substr($contents,2);

		if (empty($password)) {
			throw new \Exception("no key password input");
		}

		$signature = \sign($content,$key,$password);
		$code = current($signature);
		$signcode = \base64enc($code);
		return current($signcode);
	}

	public static function verify($content,$publicKey,$password){

	}
}