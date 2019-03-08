<?php

Class WPJAM_OPENSSL_Crypt
{
	private $key;
	private $method = 'aes-128-cbc';
	private $iv = '';
	private $options = OPENSSL_RAW_DATA;

	public function __construct($key, $args = [])
	{
		$this->key     = $key;
		$this->method  = ($args['method']) ?? $this->method;
		$this->options = ($args['options']) ?? $this->options;
		$this->iv      = ($args['iv']) ?? '';
	}

	public function encrypt($text)
	{
		$encrypted_text = openssl_encrypt($text, $this->method, $this->key, $this->options, $this->iv);

		return trim($encrypted_text);
	}

	public function decrypt($encrypted_text)
	{
		$decrypted_text = openssl_decrypt($encrypted_text, $this->method, $this->key, $this->options, $this->iv);

		return trim($decrypted_text);
	}

	public static function generate_random_string($length)
	{
		$token        = "";
		$codeAlphabet = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";
		$codeAlphabet .= "abcdefghijklmnopqrstuvwxyz";
		$codeAlphabet .= "0123456789";
		$max          = strlen($codeAlphabet); // edited

		for ($i = 0; $i < $length; $i++) {
			$token .= $codeAlphabet[self::crypto_rand_secure(0, $max - 1)];
		}

		return $token;
	}

	private static function crypto_rand_secure($min, $max)
	{
		$range = $max - $min;
		if ($range < 1) {
			return $min;
		} // not so random...

		$log    = ceil(log($range, 2));
		$bytes  = (int)($log / 8) + 1; // length in bytes
		$bits   = (int)$log + 1; // length in bits
		$filter = (int)(1 << $bits) - 1; // set all lower bits to 1
		do {
			$rnd = hexdec(bin2hex(openssl_random_pseudo_bytes($bytes)));
			$rnd = $rnd & $filter; // discard irrelevant bits
		} while ($rnd > $range);

		return $min + $rnd;
	}
}