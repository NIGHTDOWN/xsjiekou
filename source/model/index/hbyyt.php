<?php


namespace ng169\model\index;

use ng169\Y;
use ng169\lib\Log;
use ng169\tool\Out;


checktop();

class hbyyt extends Y
{
	private static $base64Map = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";
	private static $reverseBase64Map = [];

	public static function initializeReverseMap()
	{
		if (empty(self::$reverseBase64Map)) {
			for ($i = 0; $i < strlen(self::$base64Map); $i++) {
				self::$reverseBase64Map[ord(self::$base64Map[$i])] = $i;
			}
		}
	}

	public static function toByteArray($words, $sigBytes)
	{
		return ["words" => $words, "sigBytes" => $sigBytes];
	}

	public static function decode($input, $length, $reverseMap)
	{
		$output = [];
		$index = 0;
		for ($i = 0; $i < $length; $i++) {
			if ($i % 4) {
				$c = ($reverseMap[ord($input[$i - 1])] << ($i % 4 * 2));
				$d = ($reverseMap[ord($input[$i])] >> (6 - $i % 4 * 2));
				$output[intval($index / 4)] |= ($c | $d) << (24 - $index % 4 * 8);
				$index++;
			}
		}
		return self::toByteArray($output, $index);
	}

	public static function processInput($input)
	{
		self::initializeReverseMap();
		$paddingChar = self::$base64Map[64];
		$length = strpos($input, $paddingChar) !== false ? strpos($input, $paddingChar) : strlen($input);
		return self::decode($input, $length, self::$reverseBase64Map);
	}

	public static function stringify($byteArray)
	{
		$words = $byteArray['words'];
		$sigBytes = $byteArray['sigBytes'];
		$output = [];
		for ($i = 0; $i < $sigBytes; $i++) {
			$byte = ($words[intval($i / 4)] >> (24 - $i % 4 * 8)) & 255;
			$output[] = dechex(intval($byte >> 4));
			$output[] = dechex($byte & 15);
		}
		return implode("", $output);
	}

	public static function encode($input)
	{
		$processedInput = self::processInput($input);
		return self::stringify($processedInput);
	}
}
