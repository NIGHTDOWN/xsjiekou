<?php


namespace ng169\tool;

use ng169\Y;

class Url
{
	private static $debar = array('admin');
	private static $ssl = "http://";
	private static $http = "https://";
	private static $ip = '';
	private static function gethttp()
	{


		if (preg_match('/(http[s]{0,1}):\/\/*?/', self::$ip)) {

			return '';
		}

		return 'http://';
		/*  if($_SERVER['HTTPS']=='off')
		return 'http://';
		return 'https://';*/
	}
	public static
	function isstatic($group)
	{
		$group = $group ? $group : $_GET['m'];

		if ((!in_array($group, self::$debar)) && (Y::$conf['rewrite'] || G_REWRITE)) {
			return true;
		} else {
			return false;
		}
	}
	/**
	 * 解析伪静态url
	 *
	 * @return void
	 */
	public static function resolve()
	{
		/**
		 * 1斜杠分割
		 * 2-号分割
		 * 3？&=正常分割
		 * （1）分割识别group medthod action
		 * 生成对于m，a，c
		 * 倒叙分割先分割？后面
		 * 在分割后缀
		 * 在分割斜杠
		 */
		$URL = urldecode($_SERVER["REQUEST_URI"]);
		$data = explode('?', $URL); //?后面的已经php识别；
		if (isset($data[0])) {
			$data1 = explode('.', $data[0]);

			if (isset($data1[1]) && $data1[1] == 'php') {
				//php脚本模式；不执行url解码
				return false;
			}
			if (isset($data1[0])) {
				//反向解析
				$data = explode('/', $data1[0]);
				/*$route=array('a'=>0,'c'=>0,'m'=>0);*/
				$route = array();
				if (is_array($data) && sizeof($data) > 0) {
					/*$data=array_reverse($data);*/
					foreach ($data as $param) {
						$params = explode('-', $param);
						if (is_array($params) && sizeof($params) > 1) {
							for ($i = 0; $i <= sizeof($params); $i = $i + 2) {
								if (isset($params[$i]) && isset($params[$i + 1]) && $params[$i] != '') {
									$_GET[$params[$i]] = $params[$i + 1];
								}
							}
						} else {
							if (isset($params[0]) && $params[0] != '') {
								array_push($route, $params[0]);
							}
						}
					}
				}

				switch (sizeof($route)) {
					case 0:
						return true;
						break;
					case 1:
						// $_GET['m'] = $route[0];
						$_GET['c'] = $route[0];
						break;
					case 2:
						$_GET['c'] = $route[0];
						$_GET['a'] = $route[1];
						break;
					default:
						$_GET['m'] = $route[0];
						$_GET['c'] = $route[1];
						$_GET['a'] = $route[2];

						break;
				}
				//路由定义
			}
		}


		return true;
	}





	public static
	function url($args = null, $action = null, $mod = null, $group = null, $ip = null)
	{

		$group = $group ? $group : D_GROUP;
		if (is_array($args)) {
			if (isset($args['a'])) {
				unset($args['a']);
			}
			if (isset($args['m'])) {
				unset($args['m']);
			}
			if (isset($args['c'])) {
				unset($args['c']);
			}
		}

		if ($ip) {
			self::$ip = $ip;
		} else {
			self::$ip = $_SERVER["HTTP_HOST"];
		}
		// d($_SERVER);
		if (isset($args['alias']) && isset($args['catid'])) {
			unset($args['catid']);
		}

		if (self::isstatic($group) && !in_array($group, self::$debar)) {
			$url = self::get_url_static($args, $action, $mod, $group, $ip);
		} else {
			$url = self::get_url_dynamic($args, $action, $mod, $group, $ip);
		}
		return $url;
	}
	/**
	 * 返回动态url
	 * @param undefined $args 参数
	 * @param undefined $action 动作
	 * @param undefined $mod  控制器
	 * @param undefined $group 模块
	 * @param undefined $ip  域名
	 * @param undefined $preflag
	 *
	 * @return string
	 */
	private static
	function get_url_dynamic(
		$args = null,
		$action = null,
		$mod = null,
		$group = null,
		$ip = null
	) {
		$a    = defined('D_FUNC') ? D_FUNC : 'run';
		$c    = defined('D_MEDTHOD') ? D_MEDTHOD : 'index';
		$file = 'index.php?';
		$fh   = '&';
		$param = '';
		if (is_array($args)) {
			foreach ($args as $name => $vaal) {
				if ($name != null && $vaal != null) {
					$param .= "{$fh}{$name}={$vaal}";
				}
			}
		}
		$group = $group ? $group : D_GROUP;
		if ($group != 'index' && $group != '') {
			$m = 'm=' . $group . $fh;
		} else {
			$m = '';
		}
		$mod    = $mod ? $mod : $c;
		$action = $action ? $action : 'run';
		if ($ip == '/' || $ip == null) {
			$ip = $_SERVER["HTTP_HOST"];
		}
		$url = self::gethttp() . $ip . '/' . $file . $m . 'c=' . $mod . $fh . 'a=' . $action . $param;
		return $url;
	}
	/**
	 * 返回伪静态url
	 * @param undefined $args 参数
	 * @param undefined $action 动作
	 * @param undefined $mod  控制器
	 * @param undefined $group 模块
	 * @param undefined $ip  域名
	 * @param undefined $preflag
	 *
	 * @return string
	 */
	private static
	function get_url_static(
		$args = null,
		$action = null,
		$mod = null,
		$group = null,
		$ip = null,
		$preflag = 1
	) {
		$a      = defined('D_FUNC') ? D_FUNC : 'run';
		$c      = defined('D_MEDTHOD') ? D_MEDTHOD : 'index';
		$file   = 'index.php?';
		$fh     = '/';
		$group  = $group ? $group : D_GROUP;
		$mod    = $mod ? $mod : $c;
		$action = $action ? $action : 'run';
		$param = '';
		if ($group == 'index') {
			$group = '';
		} else {
			$group .= '/';
		}
		if (!$group) {
			//如果是index模块，action可以控
			if ($action == 'run') {
				$action = '';
			}
		}
		// $alias  = null;

		// $city = null;
		// if ($mod == 'index') {
		// 	$mod = '';
		// }
		if ($mod != null) {
			$mod = $mod . '/';
		}
		if ($action != null) {
			$action = $action . '/';
		}
		// if ($group != null) {
		// 	if ($group == 'index') {
		// 		$group = $group . '/';
		// 	} else {
		// 		$group = '';
		// 	}
		// }
		$url = self::gethttp() . $_SERVER["HTTP_HOST"] . '/';

		if (is_array($args)) {
			foreach ($args as $name => $vaal) {
				if ($name != null && $vaal != null) {
					$param .= "-{$name}-{$vaal}";
				}
			}
		}

		$param = urlencode(trim($param, '-'));

		if ($param != null && Y::$conf['rewritepre'] != null && $preflag) {

			$pre = '.' . Y::$conf['rewritepre'];
		} else {
			$pre = G_URLPRE;
		}
		$params = trim($group . $mod . $action . $param ,'/') . $pre;
		if ($ip) {
			return $ip . '/' . $params;
		} else {
			return $url . $params;
		}
		// $url = trim(self::gethttp() . $_SERVER["HTTP_HOST"] . '/' . $group . $mod . $action . $param, '/') . $pre;

		// return $url;
	}

	public static
	function load_static()
	{
		$file = '/.htaccess';
		$msg  = '<IfModule mod_rewrite.c>
		Options +FollowSymlinks -Multiviews
		RewriteEngine On
		RewriteCond %{REQUEST_FILENAME} !-d
		RewriteCond %{REQUEST_FILENAME} !-f
		RewriteRule ^(.*)$ index.php [L,E=PATH_INFO:$1]
		</IfModule>';
		\ng169\tool\File::writeFile($file, $msg);
	}

	public static
	function unload_static()
	{
		$file = '/.htaccess';

		\ng169\tool\File::delFile($file);
	}
	public static
	function ismoible()
	{
		if ((@$_GET['windows'] == 'mobile')) {
			return true;
		}
		if (isset($_SERVER['HTTP_X_WAP_PROFILE'])) {
			return true;
		}

		if (isset($_SERVER['HTTP_VIA'])) {

			return stristr($_SERVER['HTTP_VIA'], "wap") ? true : false;
		}

		if (isset($_SERVER['HTTP_USER_AGENT'])) {
			$clientkeywords = array(
				'nokia',
				'sony',
				'ericsson',
				'mot',
				'samsung',
				'htc',
				'sgh',
				'lg',
				'sharp',
				'sie-',
				'philips',
				'panasonic',
				'alcatel',
				'lenovo',
				'iphone',
				'ipod',
				'blackberry',
				'meizu',
				'android',
				'netfront',
				'symbian',
				'ucweb',
				'windowsce',
				'palm',
				'operamini',
				'operamobi',
				'openwave',
				'nexusone',
				'cldc',
				'midp',
				'wap',
				'mobile'
			);

			if (preg_match("/(" . implode('|', $clientkeywords) . ")/i", strtolower($_SERVER['HTTP_USER_AGENT']))) {
				return true;
			}
		}

		if (isset($_SERVER['HTTP_ACCEPT'])) {

			if ((strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') !== false) && (strpos($_SERVER['HTTP_ACCEPT'], 'text/html') === false || (strpos($_SERVER['HTTP_ACCEPT'], 'vnd.wap.wml') < strpos($_SERVER['HTTP_ACCEPT'], 'text/html')))) {
				return true;
			}
		}
		return false;
	}
	public static
	function isAjax()
	{

		if (@$_SERVER["HTTP_X_REQUESTED_WITH"] == 'XMLHttpRequest') {

			return true;
		}
		if (!empty($_POST['ajax']) || !empty($_GET['ajax']))
			return true;
		if (!empty($_POST['json']) || !empty($_GET['json']))
			return true;
		return false;
	}
}
