<?php

use ng169\Y;
use ng169\tool\Out as YOut;
use ng169\tool\Url as YUrl;
use ng169\tool\Init as YInit;
use ng169\tool\Session as YSession;
use ng169\lib\Lang;

@checktop();
function T($name)
{
	return Y::table($name);
}

function p($str)
{
	if (is_string($str)) {
		echo "$str" . "\n";
	} else {
		
		var_dump($str);
	}
}
function card($str)
{
	$arr = str_split($str, 4); //4的意思就是每4个为一组
	$str = implode(' ', $arr);
	return $str;
}
function hidecard($str)
{
	$str = substr($str, -4);
	$str = '**** **** **** **** ' . $str;
	return $str;
}
function S($name, $type)
{
	return Y::service($name, $type);
}
function G($arr, $data = null)
{
	/*return Input($arr$arr,$data)->get();*/
	/*return ng169\Y::getparm($arr,$data);*/
	$input = new ng169\service\Input($arr, $data);
	return $input;
}
function pay($orderid, $model, $api)
{
	$apiob = Y::loadapi('payapi');
	$apiob->pay($orderid, $model, $api);
}
function payback()
{
	$apiob = Y::loadapi('payapi');
	$apiob->back();
}

function wallet($to_uid, $money, $info, $orderid, $tablename)
{
	if (!$orderid) {
		error('订单丢失');
	}
	$from = array('glidetype' => 1, 'userid'    => Y::$wrap_user['userid'], 'money'     => $money, 'sourcetype' => $info['title']);
	if (Y::$wrap_user['cash'] < $money) {
		error('余额不足,请选择其他方式支付');
	}
	$flag = M('fund', 'am')->add($from);

	if ($to_uid) {
		$to = array('glidetype' => 0, 'userid'    => $to_uid, 'money'     => $money, 'sourcetype' => $info);
		$falg = M('fund', 'am')->add($to);
	}
	if ($flag) {
		$in = array('paytime'  => time(), 'paystatus' => 1, 'status'   => 6);
		$w = array('orderid' => $orderid);
		$t = T($tablename)->update($in, $w);
		out('支付成功,请回到确认页面点击确认,完成支付.', null, null, 0);
	} else {
		error('支付成功,请回到确认页面点击确认,完成支付');
	}
}
function M($name, $type = 'im')
{
	return Y::model($name, $type);
}
function upcache($name = null)
{
	Y::$cache->del();
	/*$cache = Y::import('cache', 'lib');
	$cache->updateCache($name);*/
	ng169\TPL::clearAllCache();
}
function out($message, $url = null, $flag = 1, $auto_go_url = 1)
{
	YOut::out($message, $url, $flag, $auto_go_url);
}
function error($message, $url = null, $flag = 0)
{
	YOut::out($message, $url, 0, $flag);
}
function msg($message, $url = null, $flag = 1)
{
	YOut::out($message, $url, 1, $flag);
}

function geturl($args = null, $action = null, $mod = null, $group = null, $ip = null)
{
	return  YUrl::url($args, $action, $mod, $group, $ip);
}
function gourl($url)
{
	return  YOut::redirect($url);
}


function load_mod($mod_dir)
{
	/*	Y::loadTool('init');*/

	return  YInit::get($mod_dir);
}

function get_mod($mod_dir)
{

	/*Y::loadTool('init');*/
	$b = YInit::load($mod_dir);
	if (!$b) {
		return  load_mod($mod_dir);
	}
	return $b;
}
function check_verifycode($word, $renew = false)
{


	Y::loadTool('session');
	if ($word != YSession::get('verifycode')) {
		msg(__('验证码错误'), '', 0);
	}
	if ($renew) {
		YSession::del('verifycode');
	}
}
function getmodname($mod, $mod_dir)
{
	$m = get_mod($mod_dir);
	if (!is_array($m)) return false;
	$name = $m[$mod]['alias'];
	if ($name != null) {
		return $name;
	} else {
		return $mod;
	}
}
/**获取设备类型
 * @$head 头参数
 * return 设备类型
 */
function getdevicetype($head)
{
	if (isset($head['devicetype'])) {
		return $head['devicetype'];
	}

	if (isset($head['user-agent'])) {
		preg_match('/\(.*?\)/', $head['user-agent'], $m);
		if ($m[0]) {
			preg_match('/window/Ui', $m[0], $m2);
			if ($m2) {
				return 'win-web';
			}
			preg_match('/iphone/Ui', $m[0], $m2);
			if ($m2) {
				return 'iphone-wap';
			}
			preg_match('/ipad/Ui', $m[0], $m2);
			if ($m2) {
				return 'ipad-wap';
			}
			preg_match('/android/Ui', $m[0], $m2);
			if ($m2) {
				return 'android-wap';
			}
			preg_match("/linux/Ui", $m[0], $m2);


			if ($m2) {
				return 'linux-web';
			}
			preg_match('/mac/Ui', $m[0], $m2);

			if ($m2) {
				return 'macos-web';
			}
			preg_match('/\(([^\;]{1,})\;/Ui', $m[0], $m1);
			if ($m1[1]) {
				return $m1[1] . '-web';
			}
			//匹配window 返回web
			//匹配ipad返回wap-ipad
			//匹配安卓返回wap-android
			//匹配liunx返回liunx
		}
	}
	//获取失败
	return false;
}
function getactionname($action, $mod, $mod_dir)
{
	$m = get_mod($mod_dir);
	if (!is_array($m)) return false;
	$name = $m[$mod]['action'][$action]['alias'];
	if ($name != null) {
		return $name;
	} else {
		return $action;
	}
}


function __($index)
{
	return Lang::get($index);
}
function img($imgstring, $num = null)
{
	$imgarr = explode(',', $imgstring);
	$num    = $num ? $num : 0;
	return $imgarr[$num];
}
function imgarray($imgstring)
{
	$imgarr = explode(',', $imgstring);
	return $imgarr;
}
function incode($str, $f = '*')
{
	$len = strlen($str) / 2;
	return substr_replace($str, str_repeat($f, $len), floor(($len) / 2), $len);
}
function url_exists($url)
{
	$hdrs = @get_headers($url);
	return is_array($hdrs) ? preg_match('/^HTTP\\/\\d+\\.\\d+\\s+2\\d\\d\\s+.*$/', $hdrs[0]) : false;
}
function imgsize($imgfile, $size = null)
{

	$path    = Y::$urlpath;
	$cutpath = Y::$conf['upfilepath'] . '/autoimg/';
	$sizeimg = $cutpath . basename($imgfile) . '.' . $size . '.jpg';

	if (file_exists($sizeimg)) {
		return $path . $sizeimg;
	}
	return $imgfile;
	if (!url_exists($imgfile)) {
		return $imgfile;
	}

	Y::loadTool('file');
	Y::loadTool('image');
	$imgfile = trim($imgfile, $path);
	$data    = YImage::isimg($imgfile);
	/* d($imgfile);
    d($data);	return false;*/
	if (!$data) {
		return $imgfile;
	}

	if (!is_dir($cutpath)) {
		YFile::createDir($cutpath);
	}

	$data = YImage::isimg($sizeimg);

	if ($data || $size == null) {
		return $path . $sizeimg;
	} else {


		$size = explode(',', $size);
		$sizes['width'] = $size[0];
		$sizes['height'] = $size[1];
		return $path . YImage::makeCut($imgfile, $size, $sizeimg);
	}
}
function getareaname($id)
{
	if (!$id) return null;
	$data = T('area')->set_field(array('areaname'))->get_one(array('areaid' => $id));
	if ($data)
		return $data['areaname'];
}

function snapshot($url, $path = '/data/attachment/snap/', $dealy = 0)
{

	$ch = curl_init($url);
	curl_setopt($ch, CURLOPT_POST, true);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $url);
	curl_setopt($ch, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	$data = curl_exec($ch);
	curl_close($ch);
	Y::loadTool('file');
	$path .= date('Y-m-d') . '/';
	$name = time() . getrand(4) . '.html';
	YFile::createDir($path);
	$file = $path . $name;
	YFile::writeFile($file, $data);
	return $file;
}


function get($array, $cnname = null)
{

	return G($array, $cnname)->get();
}
function getGET($name)
{
	$val = get(array('string' => array($name)));
	return $val[$name];
}
function daytomouthoryear($day)
{
	if (!$day) {
		return '永久';
	}
	if ($day < 31) {
		return $day . '天';
	} elseif (
		31 < $day && $day < 365
	) {
		if ($day % 31 == 0) {
			return ($day / 31) . '月';
		}
	} else {
		if ($day % 365 == 0) {
			return ($day / 365) . '年';
		}
	}
	return $day . '天';
}
function closepage()
{

	echo ("<script>this.window.opener = null;window.open('','_self');;window.close();</script>");
}
function djs($stime, $etime)
{
}


function userinfo($uid, $name = null)
{
	if (!$uid) return false;
	$w = array('uid' => $uid);
	$n     = 'user' . $uid;

	$cache = Y::$cache;
	list($bool, $data) = $cache->get($n);

	if (!$data) {
		$data = T('user')->join_table(array('t' => 'user_attr', 'uid', 'uid'))->get_one($w);
		$encode = ($data);
		$cache->set($n, $encode, G_DAY);
	}
	if (!$data) return false;
	if (!$name) return $data;
	return $data[$name];
}
function rmb($num, $separator = ',', $accuracy = 2)
{

	$numArr = explode('.', $num);

	$IntPart = $numArr['0'];

	$c      = strlen($IntPart);

	$prefix = NULL;

	$IntPart = $c > 0 && $IntPart[0]
		== '-' ? $prefix = substr($IntPart, 1) : $IntPart;

	$IntPart = str_pad($IntPart, $c + (3 - $c % 3), '0', STR_PAD_LEFT);

	$arr    = str_split($IntPart, 3);

	$Int    = ltrim(implode($separator, $arr), '0' . $separator);

	$Int    = empty($Int) ? '0' : $Int;

	$addPart    = ($f          = strlen($numArr['1'])) < $accuracy ? $accuracy - $f : 0;

	$fractional = empty($numArr['1'])

		? str_repeat('0', $accuracy)

		: substr($numArr['1'], 0, $accuracy) . str_repeat('0', $addPart);
	$fractional = '.' . $fractional;


	$prefix = empty($prefix) ? $prefix : '-';


	return $prefix . $Int . $fractional;
}
function residual_time($timenum)
{
	$day = floor($timenum / G_DAY);
	$hour = floor($timenum % G_DAY / 3600);

	return $day . '天' . $hour . '时';
}
function getarea($id)
{
	$w = array('areaID' => $id);
	$data = T('area')->get_one($w);
	if ($data) return  $data['area'];
	return false;
}
function getcity($id)
{
	$w = array('cityID' => $id);
	$data = T('city')->get_one($w);
	if ($data) return  $data['city'];
	return false;
}
function getprovince($id)
{

	$w = array('provinceID' => $id);
	$data = T('province')->get_one($w);

	if ($data) return  $data['province'];
	return false;
}
function tplarray($tplarray)
{

	if (!($tplarray)) {
		return $tplarray;
	}
	if (is_array($tplarray)) {
		return $tplarray;
	}
	$_value = explode(',', $tplarray);
	$out    = array();
	foreach ($_value as $key => $v) {
		if (preg_match("/(=>|:|=)/", $v)) {
			$tmp = preg_split("/(=>|:|=)/", $v);

			$tmp[1] = trim($tmp[1], '\'');
			if (preg_match("/^\[(.*)\]$/", $tmp[1], $tmp1)) {


				$tmp[1] = explode('|', $tmp1['1']);
			}
			$tmpa[trim($tmp[0], '\'')] = $tmp[1];

			$out = array_merge($out, $tmpa);
			unset($tmpa);
			unset($tmp);
		} else {
			$tmpa = (array(trim($v, '\'')));
			$out = array_merge($out, $tmpa);
			unset($tmpa);
			unset($tmp);
		}
	}

	return $out;
}
function electronictype($typeid)
{
	$type = array('未设置', '支付宝', '微信');
	return $type[$typeid];
}
function tohex($str)
{
	$url = "";
	$m1 = "";
	for ($i = 0; $i <= strlen($str); $i++) {
		$m1 = base_convert(ord(substr($str, $i, 1)), 10, 16);
		if ($m1 == 20) {
			$m1 = " ";
			$url = $url . $m1;
		} else {
			$m1 = base_convert(ord(substr($str, $i, 1)), 10, 16);
			if ($m1 != "0")
				$url = $url . "N" . $m1;
		}
	}
	return $url;
}
function tostr($hex)
{

	$hex = str_replace('N', '\x', $hex);
	$url = stripcslashes($hex);
	$url = mb_substr($url, 0, 34, 'utf-8');

	return $url;
}
if (!function_exists('array_column')) {
	function array_column($input, $columnKey, $indexKey = null)
	{
		$columnKeyIsNumber  = (is_numeric($columnKey)) ? true : false;
		$indexKeyIsNull            = (is_null($indexKey)) ? true : false;
		$indexKeyIsNumber     = (is_numeric($indexKey)) ? true : false;
		$result                         = array();
		foreach ((array)$input as $key => $row) {
			if ($columnKeyIsNumber) {
				$tmp = array_slice($row, $columnKey, 1);
				$tmp = (is_array($tmp) && !empty($tmp)) ? current($tmp) : null;
			} else {
				$tmp = isset($row[$columnKey]) ? $row[$columnKey] : null;
			}
			if (!$indexKeyIsNull) {
				if ($indexKeyIsNumber) {
					$key = array_slice($row, $indexKey, 1);
					$key = (is_array($key) && !empty($key)) ? current($key) : null;
					$key = is_null($key) ? 0 : $key;
				} else {
					$key = isset($row[$indexKey]) ? $row[$indexKey] : 0;
				}
			}
			$result[$key] = $tmp;
		}
		return $result;
	}
}
