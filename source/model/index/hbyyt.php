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

	
	public static function pr3($e) {  
		$t = strlen($e);  
		$n = array();  
		for ($i = 0; $i < $t; $i += 2) {  
			$n[$i >> 3] |= hexdec(substr($e, $i, 2)) << (24 - $i % 8 * 4);  
		}  
		return self::toByteArray($n, $t / 2);  
	}  
	  
	public static  function stringify2($e) {  
		$_map = "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=";  
		  
		$t = $e['words'];  
		$n = $e['sigBytes'];  
		$i = $_map;  
		$a = array();  
		for ($o = 0; $o < $n; $o += 3) {  
			$r = ($t[$o >> 2] >> (24 - $o % 4 * 8)) & 255;  
			$c = ($t[($o + 1) >> 2] >> (24 - ($o + 1) % 4 * 8)) & 255;  
			$d = ($t[($o + 2) >> 2] >> (24 - ($o + 2) % 4 * 8)) & 255;  
			$s = ($r << 16) | ($c << 8) | $d;  
			for ($l = 0; $l < 4 && $o + 0.75 * $l < $n; $l++) {  
				$a[] = $i[$s >> (6 * (3 - $l)) & 63];  
			}  
		}  
		$f = $i[64];  
		if ($f) {  
			while (count($a) % 4) {  
				$a[] = $f;  
			}  
		}  
		return implode("", $a);  
	}
	public static function decode($str){
		return self::stringify2(self::pr3($str));
	}




	public static function edecode($input, $length, $reverseMap)
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
		return self::edecode($input, $length, self::$reverseBase64Map);
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
