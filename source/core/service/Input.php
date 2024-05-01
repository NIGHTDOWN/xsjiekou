<?php

namespace ng169\service;

use ng169\Y;
use ng169\tool\Request as YRequest;
use ng169\tool\Filter as YFilter;

checktop();
class Input extends Y
{
	private $arr;
	private $attr = array();
	private $return;
	private $outempty = 1;
	private $err = array();
	private $cnname = null;
	private $Input_source = null;
	private static function _getheader($name = '', $default = null)
	{
		// if (empty($this->header)) {
		$header = [];
		if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
			$header = $result;
		} else {
			$server =  $_SERVER;
			foreach ($server as $key => $val) {
				if (0 === strpos($key, 'HTTP_')) {
					$key = str_replace('_', '-', strtolower(substr($key, 5)));
					$header[$key] = $val;
				}
			}
			if (isset($server['CONTENT_TYPE'])) {
				$header['content-type'] = $server['CONTENT_TYPE'];
			}
			if (isset($server['CONTENT_LENGTH'])) {
				$header['content-length'] = $server['CONTENT_LENGTH'];
			}
		}
		$header = array_change_key_case($header);

		// if (is_array($name)) {
		// 	return $this->header = array_merge($this->header, $name);
		// }
		if ('' === $name) {
			return $header;
		}
		$name = str_replace('_', '-', strtolower($name));
		return isset($header[$name]) ? $header[$name] : $default;
	}
	public static function  getheader()
	{

		//  $data = function_exists('getallheaders') ? \getallheaders() : $this->header();
		$data =  self::_getheader();
		//        $key=['mobileos','mobiletype','ip','appversion','language','token'];
		$key = array_keys($data);
		$ret = new Input(array('string' => $key), [], $data);

		$wrap_head = $ret->get();
		return $wrap_head;
	}
	public function get()
	{
		return $this->return;
	}
	public function closetags($html)
	{
		// 不需要补全的标签
		preg_match_all('#<[/]{0,1}([a-z]+)(?: .*)?(?<![/|/ ])>#iU', $html, $result);

		/*$arr_single_tags = array('img','font','strong','p','br','span','a');*/

		foreach ($result[0] as $index => $lab) {
			$bq = strtolower($result[1][$index]);


			if (!preg_match('#\b(img|font|strong|p|br|span|a)\b#', $bq)) {

				$html = str_ireplace($lab, '', $html);
			} else {

				preg_match_all('#(([\S]+)[=]{1}([\'\"]{1}[\S]+)[\'\"]{1})#iU', $lab, $result2);

				foreach ($result2[2] as $index => $val) {
					if (!$val) break;
					$arr_single_tags = array('style', 'src', 'href');


					if (!preg_match('#(style|src|href)#', $val)) {

						$html = str_ireplace($result2[1][$index], '', $html);
					} else {
						/*preg_match_all('#[\(\)]+#iU', $result2[3][$index], $result3);
					
						if(sizeof($result3[0])){
						error('输入不合法');
						}*/
					}
				}
			}
		}

		$html = str_ireplace('&amp;', '&', $html);
		/*$html=htmlspecialchars($html);		
		$html=htmlspecialchars_decode($html);*/
		return $html;
	}
	private function __safe($data = null)
	{
		if ($data === 0 || $data === '0') {
			return $data;
		}
		if ($data == '' || $data == null) {
			return '';
		}

		$data = YFilter::filterStr($data);
		$data = YFilter::filterSql($data);
		$data = YFilter::filterScript($data);
		$data = YFilter::filterXSS($data);

		return $data;
	}


	private function __safearray($array)
	{
		if (is_array($array)) {
			foreach ($array as $key => $val) {
				if (is_array($val)) {
					$array[$key] = $this->__safearray($val);
				} else {
					$array[$key] = $this->__safe($val);
				}
			}
		} else {
			$array = $this->__safe($array);
		}
		return $array;
	}

	private function array_hb($ar, $ar2)
	{

		if (is_array($ar) && is_array($ar2)) {
			$ar = array_merge($ar, $ar2);
		}

		return $ar;
	}
	/**
	 * 获取过滤后输入数据
	 * @param undefined $arr
	 * @param undefined $cnname
	 * @param undefined $Input_source
	 * 
	 * @return
	 */
	public function __construct($arr, $cnname = null, $Input_source = null)
	{

		$ar = array();
		$this->cnname = $cnname;
		$this->Input_source = $Input_source;

		foreach ($arr as $name => $value) {
			if (is_array($value) && count($value) < 1) {
				break;
			}

			switch (trim($name)) {
				case 'string':
					$string = $this->_getPOST($value);

					$string = $this->_checkType($string, $name);

					$ar     = $this->array_hb($ar, $string);
					break;
				case 'int':
					$int = $this->_getPOST($value);

					$int = $this->_checkType($int, $name);

					$ar  = $this->array_hb($ar, $int);

					break;
				case 'float':
					$float = $this->_getPOST($value);
					$float = $this->_checkType($float, $name);
					$ar    = $this->array_hb($ar, $float);
					break;
				case 'array':
					$array = $this->_getPostArray($value);
					$array = $this->__safearray($array);
					$ar    = $this->array_hb($ar, $array);
					break;
				case 'html':
					$html = $this->_getPOST($value);

					$html = $this->_checkType($html, $name);
					$ar   = $this->array_hb($ar, $html);
					break;
				case 'file':

					$file = $this->_getFile($value);
					$ar   = $this->array_hb($ar, $file);
					break;
				case 'img':
					$file = $this->_getImg($value);

					$ar   = $this->array_hb($ar, $file);
					break;
				case 'fixed':


					$fixed = ($value);
					$ar    = $this->array_hb($ar, $fixed);
					break;
			}
			$this->return = $ar;
		}

		return clone $this;
	}
	private function _checkfile($file)
	{
		return  file_exists($file);
	}
	private function _getImg($arr)
	{

		$ar = array();

		foreach ($arr as $name => $value) {

			$val   = YRequest::getPost($name) !== '' ? YRequest::getPost($name) : YRequest::getGet($name);
			$val   = $this->__safearray($val);


			$index = $name;

			if ($value == 1) {
				$this->_checkNull($val, $name);
			} elseif (is_array($value)) {
				$option = $value;
				$option['size'] = isset($option['size']) ? $option['size'] : $option['0'];
				$option['zoom'] = isset($option['zoom']) ? $option['zoom'] : $option['1'];
				$option['close_watermark_flag'] = isset($option['3']) ? $option['close_watermark_flag'] : $option['2'];
				$option['close_watermark_flag'] = isset($option['4']);
			} else {
				$index = $value;
				$val   = YRequest::getPost($value) !== '' ? YRequest::getPost($value) : YRequest::getGet($value);
				$option['size'] = $option['size'] ? $option['size'] : parent::$conf['default_img_size'];
				$option['zoom'] = $option['zoom'] ? $option['zoom'] : 10;
				$option['delsource'] = $option['4'];
			}
			if (!is_array($val)) {
				$val = trim($val);
			} else {
				$val = implode(',', $val);
			}
			Y::loadTool('image');

			$val = $this->_moveImg($val, $option['size'], $option['zoom'], $option['close_watermark_flag'], $option['delsource']);

			if (is_array($val)) {
				$this->attr['thumb'][$index] = $val['thumb'];
				$this->attr['source'][$index] = $val['source'];
				$val = $val['img'];
			}
			if ($val != '') {
				$ar = $this->array_hb($ar, array($index => $val));
			}
		}

		return $ar;
	}
	public function  getattr($name = 'drawimg', $type = 'thumb')
	{
		if ($name) {
			return $this->attr[$type][$name];
		}
	}


	private function _moveImg($files, $cutsize, $makethumb_flag = 10, $close_global_watermark_flag = 0, $delsource = 1)
	{

		$val    = explode(',', $files);

		$source = '';

		$thumb  = '';

		$img    = '';

		$datedir = date("Ym", time());
		if (is_array($val)) {

			foreach ($val as $key => $file)


				$file = trim($file, '/');
			if ($this->_checkfile($file)) {

				Y::loadTool('file');
				$newpath = parent::$conf['upfilepath'] . 'bigimg/' . $datedir . '/';
				$retpath = parent::$conf['upfilepath'] . 'img/' . $datedir . '/';


				YFile::createDir($newpath);
				YFile::createDir($retpath);
				$name    = pathinfo($files);
				$newfile = $newpath . $key . $name['basename'];
				$retfile = $retpath . 'oe' . $key . $name['basename'];

				YFile::copyFile($file, $newfile);
				YFile::delFile($file);



				$webroot = '/';
				if ($cutsize) {

					$size   = explode(',', $cutsize);
					$width  = $size[0];
					$height = isset($size[1]) ? $size[1] : $size[0];
					$size = array('width' => $width, 'height' => $height);

					$cutfile = YImage::makeCut($newfile, $size, $retfile);

					if (parent::$conf['watermark_control']) {
						if ($close_global_watermark_flag) {
							YImage::makeWaterMark($cutfile);
						}
					}

					$img .= $webroot . $cutfile . ',';


					if ($makethumb_flag) {

						$size = array('width' => $size['width'] / 10, 'height' => $size['height'] / 10);
						$thumbimg = YImage::makeThumb($newfile, $size, $retfile . '.thumbimg.jpg');

						$thumb .= $webroot . $thumbimg . ',';
					}
					if ($delsource) {
						YFile::delFile($newfile);
					}
				} else {

					$img .= $webroot . $newfile . ',';
				}
				$source .= $webroot . $newfile . ',';
			}
		}

		return array('source' => trim($source, ','), 'thumb' => trim($thumb, ','), 'img'   => trim($img, ','));
	}

	private function _getFile($arr)
	{
		Y::loadTool('file');
		$ar = array();
		foreach ($arr as $name => $value) {
			$index = $name;
			$val   = YRequest::getPost($name) !== '' ? YRequest::getPost($name) : YRequest::getGet($name);
			$val   = $this->__safearray($val);
			$val   = trim($val);
			if ($value == 1) {
				$this->_checkNull($val, $name);
			} else {
				$index = $value;
				$val   = YRequest::getPost($value) !== '' ? YRequest::getPost($value) : YRequest::getGet($value);
			}


			$val = trim($val);

			$val = $this->_movefile($val);

			if ($val != '') {
				$ar = $this->array_hb($ar, array($index => $val));
			}
		}
		return $ar;
	}
	private function _movefile($files)
	{
		$val    = explode(',', $files);

		$source = '';

		$thumb  = '';

		$img    = '';
		$datedir = date("Ym", time());
		if (is_array($val)) {
			foreach ($val as $file)
				if (_checkfile($file)) {
					$newpath = '/' . parent::$conf['upfilepath'] . '/file/' . $datedir . '/';
					YFile::createDir($newpath);

					$name    = pathinfo($files);
					$newfile = $newpath . '/' . $name['basename'];
					YFile::copyFile($file, $newfile);



					$source .= '/' . $newfile + ',';
					YFile::delFile($file);
				}
		}
		return trim($source, ',');
	}
	private function _getHtml($arr)
	{
		$ar = array();
		foreach ($arr as $name => $value) {
			$val = YRequest::getPost($name) !== '' ? YRequest::getPost($name) : YRequest::getGet($name);
			$val = $this->__safearray($val);
			if ($value == 1) {
				$this->_checkNull($val, $name);
			} else {
				$val = YRequest::getPost($value) !== '' ? YRequest::getPost($value) : YRequest::getGet($value);
			}

			$val    = trim($val);
			$badstr = array(
				"<",
				">",
				"<",
				">",
				"&nbsp;"
			);
			$newstr = array(
				"&lt;",
				"&gt;",
				'&lt',
				'&gt',
				'&amp;nbsp;'
			);
			$val = str_ireplace($newstr, $badstr, $val);
			$val = $this->closetags($val);
			if ($val != '') {
				$ar = array_merge($ar, array($name => $val));
			}
		}
		return $ar;
	}

	private function _getPost($arr)
	{
		$ar = array();

		foreach ($arr as $name => $value) {
			if ($this->Input_source) {
				$val = isset($this->Input_source[$name]) ? $this->Input_source[$name] : '';
			} else {
				$val = YRequest::getPost($name) !== '' ? YRequest::getPost($name) : YRequest::getGet($name);
			}
			$val = $this->__safearray($val);
			if ($value == 1) {
				$val = @trim($val);
				$this->_checkNull($val, $name);
				$ar  = array_merge($ar, array($name => $val));
			} elseif ($value == 2 || $value == 'time') {

				$val = @trim($val);

				if ($val != '') {
					$b = strtotime($val);
					if ($b) {
						$val = $b;
					}
					$ar = array_merge($ar, array($name => $val));
				}
			} elseif ($value == 5 || $value == 'md5') {

				$val = trim($val);
				if ($val != '') {
					$val = md5($val);
					$ar  = array_merge($ar, array($name => $val));
				} else {

					error('密码不能为空', null, 0);
				}
			} elseif ($value == 3 || $value == 'html') {

				$val    = trim($val);
				$badstr = array(
					"<",
					">",
					"<",
					">",
					"&nbsp;"
				);
				$newstr = array(
					"&lt;",
					"&gt;",
					'&lt',
					'&gt',
					'&amp;nbsp;'
				);
				$val = str_ireplace($newstr, $badstr, $val);
				$val = $this->closetags($val);
				if ($val != '') {
					$ar = array_merge($ar, array($name => $val));
				}
			} elseif ($value == 4 || $value == 'file') {

				$val = trim($val);
				$val = explode(',', $val);
				d(dirname(__FILE__));
				d('在这里补充文件移动模块');
				die();
				$file = ROOT . $val;

				if (file_exists($file)) {
				}
				if ($val != '') {
					$ar = array_merge($ar, array($name => $val));
				}
			} elseif ($value == 'ismail') {

				$val = trim($val);

				if (!preg_match('/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/', $val)) {
					error('邮箱格式错误');
				}
				if ($val != '') {
					$ar = array_merge($ar, array($name => $val));
				}
			} elseif ($value == 'isurl') {
				$val = trim($val);
				if (!preg_match('/(http[s]{0,1}|ftp)://[a-zA-Z0-9\\.\\-]+\\.([a-zA-Z]{2,4})(:\\d+)?(/[a-zA-Z0-9\\.\\-~!@#$%^&*+?:_/=<>]*)?/gi', $val)) {
					error('URL格式错误');
				}
				if ($val != '') {
					$ar = array_merge($ar, array($name => $val));
				}
			} elseif ($value == 'isdomian') {
				$val = trim($val);
				$str = "/^http(s?):\/\/(?:[A-za-z0-9-]+\.)+[A-za-z]{2,4}(?:[\/\?#][\/=\?%\-&~`@[\]\':+!\.#\w]*)?$/";
				if (!preg_match($str, $val)) {
					error('URL格式错误');
				}
				if ($val != '') {
					$ar = array_merge($ar, array($name => $val));
				}
			} elseif ($value == 'ismobile') {

				$val = trim($val);
				if (!preg_match('/^(0|86|17951)?(13[0-9]|15[012356789]|17[012356789]|18[0-9]|14[57])[0-9]{8}$/', $val)) {
					error('手机格式错误');
				}
				if ($val != '') {
					$ar = array_merge($ar, array($name => $val));
				}
			} else {





				if ($this->Input_source) {
					$val = isset($this->Input_source[$value]) ? $this->Input_source[$value] : '';
				} else {
					$val = YRequest::getPost($value) !== '' ? YRequest::getPost($value) : YRequest::getGet($value);
				}

				$val = $this->__safearray($val);
				if (!is_array($val)) {
					$val = trim($val);
				}

				if ($val != '' || $this->outempty) {
					$ar = $this->array_hb($ar, array($value => $val));
				}
			}
		}

		return $ar;
	}

	private function _getPostArray($arr)
	{
		$ar = array();
		foreach ($arr as $name => $value) {
			if ($value == 1) {
				$val = YRequest::getPost($name);

				if ($val == null) {
					$val = YRequest::getGet($name);
				}
				$this->_checkArrayNull($val);
				$ar = array_merge($ar, array($name => $val));
			} else {
				$val = YRequest::getPost($value);
				if (is_array($val)) {


					foreach ($val as $key => $t) {
						$b = strtotime($t);
						if ($b) {
							$val[$key] = $b;
						}
					}
				}
				if ($val == null) {
					$val = YRequest::getGet($value);
				}
				if ($val != null) {
					$ar = array_merge($ar, array($value => $val));
				}
			}
		}
		return $ar;
	}

	private function _checkType($val, $type)
	{

		if (!is_array($val)) {

			return $val;
		}
		foreach ($val as $name => $value) {
			switch ($type) {
				case 'string':
					if (is_array($value)) {
						$val[$name] = ($value);
						break;
					}
					if (!is_string($value)) {
					}
					$val[$name] = strval($value);
					break;
				case 'int':
					if (!preg_match('/^[0-9\.\-]+$/', $value) && $value != '') {
						error(($this->cnname[$name] ? $this->cnname[$name] : $name) . '数据类型错误', '', 0);
					}
					$val[$name] = /*(double)*/ ($value);
					break;
				case 'float':

					if (!preg_match('/^[0-9\.]+$/', $value) && $value != '') {
						out(($this->err[$name] ? $this->err[$name] : $name) . '数据类型错误', '', false);
					}
					$val[$name] = ($value);
					break;
			}
		}

		return $val;
	}

	private function _checkNull($value, $who = null)
	{

		if ($value === '') {
			if (GET_SERVER_DEBUG) {
				$msg = $who . '不能为空';
			} else {
				$msg = ($this->cnname[$who] ? $this->cnname[$who] : $who) . '不能为空';
			}

			out($msg, null, 0, false);
		}
	}
	private function _checkArrayNull($arr)
	{
		if (!is_array($arr)) return false;
		if (sizeof($arr) == 0 || $arr == null) {
			out('数据不能为空', '', false);
		}
	}
}
