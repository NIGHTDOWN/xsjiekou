<?php


namespace ng169\tool;

use function GuzzleHttp\Psr7\str;

checktop();
class Code
{


	private $width = 62;

	private $height = 20;

	private $mykey = 'NG9RDOEMARRYDFCFUV99K98Q7';


	private function getRndom()
	{
		srand((float)microtime() * 1000000);
		while (($authnum = rand() % 100000) < 10000);
		session_start();
		$_SESSION['verfitycode'] = $authnum;
		return $authnum;
	}
	public function getCode()
	{
		Header("Content-type: image/PNG");
		$im = imagecreate($this->width, $this->height);
		$black = imagecolorallocate($im, 0, 0, 0);
		$white = imagecolorallocate($im, 255, 255, 255);
		$gray = imagecolorallocate($im, 200, 200, 200);
		imagefill($im, 0, 0, $gray);
		$authnum = $this->getRndom();
		imagestring($im, 5, 10, 3, $authnum, $black);


		for ($i = 0; $i < 200; $i++) {
			$randcolor = imagecolorallocate($im, rand(0, 255), rand(0, 255), rand(0, 255));
			imagesetpixel($im, rand() % 70, rand() % 30, $randcolor);
		}
		$a = imagepng($im);
		imagedestroy($im);
		return $a;
	}


	public function authCode($string, $operation = 'DECODE', $key = '', $expiry = 0)
	{
		if ($key == null) {
			$key = AUTH_CODE_KEY;
		}
		$ckey_length = 4;
		$key = md5($key ? $key : $this->mykey);

		$keya = md5(substr($key, 0, 16));
		$keyb = md5(substr($key, 16, 16));
		$keyc = $ckey_length ? ($operation == 'DECODE' ? substr($string, 0, $ckey_length) :
			substr(md5(time()), -$ckey_length)) : '';


		$cryptkey = $keya . md5($keya . $keyc);

		// $s1=substr($key, 0, 16);




		$key_length = strlen($cryptkey);
		$string = $operation == 'DECODE' ? base64_decode(substr($string, $ckey_length)) :
			sprintf('%010d', $expiry ? $expiry + time() : 0) . substr(
				md5($string . $keyb),
				0,
				16
			) . $string;
		$string_length = strlen($string);

		$result = '';
		$box = range(0, 255);
		$rndkey = array();
		for ($i = 0; $i <= 255; $i++) {
			$rndkey[$i] = ord($cryptkey[$i % $key_length]);
		}

		for ($j = $i = 0; $i < 256; $i++) {
			$j = ($j + $box[$i] + $rndkey[$i]) % 256;
			$tmp = $box[$i];
			$box[$i] = $box[$j];
			$box[$j] = $tmp;
		}
		$tmp99 = '';
		for ($a = $j = $i = 0; $i < $string_length; $i++) {
			$a = ($a + 1) % 256;
			$j = ($j + $box[$a]) % 256;
			$tmp = $box[$a];
			$box[$a] = $box[$j];
			$box[$j] = $tmp;
			$int = ord($string[$i]) ^ ($box[($box[$a] + $box[$j]) % 256]);
			$tmp99 .= '_' . $int;
			$result .= chr($int);
		}


		if ($operation == 'DECODE') {
			if ((substr($result, 0, 10) == 0 || substr($result, 0, 10) - time() > 0) &&
				substr($result, 10, 16) == substr(md5(substr($result, 26) . $keyb), 0, 16)
			) {

				return substr($result, 26);
			} else {
				return '';
			}
		} else {

			return $keyc . str_replace('=', '', base64_encode($result));
		}
	}
	//ase 128加密
	public  function encode($str, $key)
	{
		/*$str=Bytes::getBytes($str);*/
		if (!$key) return $str;
		$cipher = "DES-ECB";
		$ivlen = openssl_cipher_iv_length($cipher);
		$iv = openssl_random_pseudo_bytes($ivlen);
		$ciphertext = openssl_encrypt($str, $cipher, $key, $options = 0);
		/*d($ciphertext);
		$ciphertext=gzencode($ciphertext);*/
		return $ciphertext;
	}
	//ase 128解密
	public  function decode($str, $key)
	{
		//		$str=gzdecode($str);
		if (!$key) return $str;
		$cipher = "DES-ECB";
		$ivlen = openssl_cipher_iv_length($cipher);
		$iv = openssl_random_pseudo_bytes($ivlen);
		$original_plaintext = openssl_decrypt($str, $cipher, $key, $options = 0);
		return $original_plaintext;
	}
	private $buweileng;
	private $size = 7;
	public function appdecode($str, $key)
	{
		d($key);
		$key = md5($key);
		$s1 = substr($key, 0, 16);
		$s2 = substr($key, 16, 16);
		$keya = md5($s1);
		$keyb = md5($s2);

		$code = substr($str, 0, strlen($str) - 5);
		$this->buweileng = substr($str,  -5);

		$step = intval(strlen($code) / 2);
		// $step = int.parse((code.length / 2).toStringAsFixed(0));
		$strs = [(substr($code, 0, $step)), (substr($code, $step))];
		
		$strs[0] = $this->toyh2($strs[0], $keyb);
		
		$strs[1] = $this->toyh2($strs[1], $keya);

		$strings = $strs[0] . $strs[1];

		// var pad=buweileng.substring(0,1);
		$pad = substr($this->buweileng, 0, 1);
		if ($pad) {
			//   $strings=string.substring(0,string.length-int.parse(pad));
			$strings = substr($strings, 0, strlen($strings) - $pad);
		}


		return $this->itos($strings);
	}
	private function itos($value)
	{

		$tmp = '';
		$l = strlen($value);

		$index = 0;
		for (
			$i = 0;
			$i < intval(($l / $this->size));
			$i++
		) {
			$index = $i * $this->size;

			try {
				$int = intval(substr($value, $index, $this->size));

				$tmp .= $this->ascii_decode($int);
			} catch (\Throwable $th) {
				d($th);
			}
		}

		return $tmp;
	}
	function ascii_encode($strLong)
	{
		$strArr = preg_split('/(?<!^)(?!$)/u', $strLong); //拆分字符串为数组(含中文字符)
		$resUnicode = '';
		foreach ($strArr as $str) {
			$bin_str = '';
			$arr = is_array($str) ? $str : str_split($str); //获取字符内部数组表示,此时$arr应类似array(228, 189, 160)
			foreach ($arr as $value) {
				$bin_str .= decbin(ord($value)); //转成数字再转成二进制字符串,$bin_str应类似111001001011110110100000,如果是汉字"你"
			}
			$bin_str = preg_replace('/^.{4}(.{4}).{2}(.{6}).{2}(.{6})$/', '$1$2$3', $bin_str); //正则截取, $bin_str应类似0100111101100000,如果是汉字"你"
			$unicode = dechex(bindec($bin_str)); //返回unicode十六进制
			$_sup = '';
			for ($i = 0; $i < 4 - strlen($unicode); $i++) {
				$_sup .= '0'; //补位高字节 0
			}
			$str =  '\\u' . $_sup . $unicode; //加上 \u  返回
			$resUnicode .= $str;
		}

		return $resUnicode;
	}
	function ascii_decode($name)
	{

		if ($name < 255) {
			return chr($name);
		}
		return iconv("UCS-2", "utf-8", pack("H4", dechex($name)));
	}
	private function sti($vale)
	{

		$tmp = '';
		for ($i = 0; $i < strlen($vale); $i++) {
			# code...
			$tmp .= str_pad(ord($vale[$i]), $this->size, '0', STR_PAD_LEFT);
		}
		return $tmp;
	}
	private function  getkeyint($ints)
	{
		// $aa= ints.replaceAll('0', '');
		$aa = str_replace('0', '', $ints);
		if (strlen($aa) < 15) {
			$aa = str_pad($aa, 15, '0', STR_PAD_RIGHT);
			//   aa.padRight(15,'0');
		}

		return $aa;
	}
	private function toyh2($str, $keys)
	{

		$step1 = 15;
		$step2 = 16;
		// $length = int.parse((str.length / step).toStringAsFixed(0));
		$length = intval(strlen($str) / $step2);
		$index = 0;
		$tmp2 = '';
		// $keyss = intval(substr($this->getkeyint($this->sti($keys)), 0, $step1));
		$keyss = (substr($this->getkeyint($this->sti($keys)), 0, $step1));
		for ($i = 0; $i <= $length; $i++) {
			$index = $i * $step2;
			$tmp = '';
			if ($index >= strlen($str)) {
				break;
			}
			$tmp = (substr($str, $index, $step2));
			// d($tmp);
			$tp = $this->xhor($tmp, $keyss);
			// d($tp,1);
			// $tp = $tmp ^ $keyss;
			$ttt = str_pad($tp, $step1, '0', STR_PAD_LEFT);
			$tmp2 .= $ttt;
			//   $tmp2 .= (($tmp) ^ $keyss).toString().padLeft(15, '0');
		}
		$tmpbw = substr($this->buweileng, strlen($this->buweileng) - 2);

		if ($tmpbw == 0) {
			return $tmp2;
		} else {
			return substr($tmp2, $tmpbw);
		}
	}
	//大数转二进制
	public function bth($str)
	{
		$n = $str;
		$r = ''; //结果
		while ($n) {
			$k = 0;
			$m = '';
			do {
				$k = $k * 10 + substr($n, 0, 1);
				if ($m != '' || $k > 1) $m .= floor($k / 2);
				$k = $k % 2;
				$n = substr($n, 1);
			} while ($n != '');
			$n = $m;
			$r = $k . $r;
		}
		return  $r;
	}
	//修复32位系统下面超大整数溢出
	public function xhor($str1, $str2)
	{
		$bin1 = $this->bth($str1);
		$bin2 = $this->bth($str2);
		$bin1l = strlen($bin1);
		$bin2l = strlen($bin2);
		$fl = $bin1l;
		if ($bin1l < $bin2l) {
			$fl = $bin2l;
		}
		$bin1 = str_pad($bin1, $fl, '0', STR_PAD_LEFT);
		$bin2 = str_pad($bin2, $fl, '0', STR_PAD_LEFT);
		$arr = [];
		$r = array_pad($arr, $fl, 0);
		$out = '';
		foreach ($r as $key => $value) {
			$r[$key] = intval($bin1[$key]) ^ intval($bin2[$key]);
		}
		$out = implode('', $r);
		$out =
			base_convert($out, 2, 10);

		// $out = $this->BinToStr($out);

		// $out = $this->twoChangeTen($out);

		return $out;
	}
	public function appencode($str, $key)
	{
	}
}
