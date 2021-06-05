<?php


namespace ng169\lib;


checktop();
class Lang
{

	private static $lang;
	private static $country;
	public static  function init($country = '')
	{

		$defult   = 'index' . G_EXT;
		if ($country) {
			self::$country = $country;
		} else {
			self::$country = G_LANG;
		}
		$fielname = LANG . FG . self::$country . FG . $defult; //框架默认加载语言
// d($fielname,null,null,2);
		if (file_exists($fielname)) {
			self::$lang = include($fielname);
		}
	}
	public static  function load()
	{
		//加载模块公共语言
		//加载control语言
		$dir        = LANG . FG . self::$country . FG . D_GROUP . FG;

		$baselang   = $dir . 'index' . G_EXT;
		$methodlang = $dir . D_MEDTHOD . '_index' . G_EXT;

		if (file_exists($baselang)) {
			$lang = include($baselang);
			self::$lang = array_merge(self::$lang, $lang);
		}
		if (file_exists($methodlang)) {
			$method = include($methodlang);
			self::$lang = array_merge(self::$lang, $method);
		}
	}
	public static  function sockload($group, $action)
	{
		//加载模块公共语言
		//加载control语言
		$dir        = LANG . FG . self::$country . FG . "sock" . FG . $group . FG;
		$baselang   = $dir . 'index' . G_EXT;
		$methodlang = $dir . $action . '_index' . G_EXT;


		if (file_exists($baselang)) {
			$lang = include($baselang);
			self::$lang = array_merge(self::$lang, $lang);
		}
		if (file_exists($methodlang)) {
			$method = include($methodlang);

			self::$lang = array_merge(self::$lang, $method);
		}
	}
	public static  function echo()
	{

		// if(isset(self::$lang[$index]))return self::$lang[$index];
		return self::$lang;
	}
	public static  function get($index)
	{

		if (isset(self::$lang[$index])) return self::$lang[$index];
		return $index;
	}
}
