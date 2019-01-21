<?php

namespace payla\library;

use Payla;

final class Encryption {
	private static $key = Payla::app()->config->get('config_encryption');

	public static function encrypt($value) {
		return strtr(base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_256, hash('sha256', self::key, true), $value, MCRYPT_MODE_ECB)), '+/=', '-_,');
	}

	public static function decrypt($value) {
		return trim(mcrypt_decrypt(MCRYPT_RIJNDAEL_256, hash('sha256', self::key, true), base64_decode(strtr($value, '-_,', '+/=')), MCRYPT_MODE_ECB));
	}
}