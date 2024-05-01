<?php

namespace ng169\tool;


checktop();
class Cookie {
	
	private static $prefix = 'XXOO'; 
	private static $expire = 72000; 
	private static $path   = '/'; 
	private static $domain = '';
	
	
	public static function set($name, $val, $savetime=0, $expire = '', $path = '', $domain = '') {
        
        if ($savetime == 1) {
            $expire = (time() + 60 * 60 * 24 * 30 * 12);
        }
        else {
		  $expire = (empty($expire)) ? time() + self::$expire : time() +$expire; 
        }

		$path   = (empty($path)) ? self::$path : $path;
        
		$domain = (empty($domain)) ? self::$domain : $domain; 
		if (empty($domain)) {
			setcookie(self::$prefix.$name, $val, $expire, $path);
		} 
        else {
			setcookie(self::$prefix.$name, $val, $expire, $path, $domain);
		}
		$_COOKIE[self::$prefix.$name] = $val;
	}
	
	
	public static function get($name) {
		
        if(isset($_COOKIE[self::$prefix.$name])){
			return $_COOKIE[self::$prefix.$name];
		}
        return false;
	}
	
	
	public static function del($name) {
		self::set($name, '', 0);
		$_COOKIE[self::$prefix.$name] = '';
		unset($_COOKIE[self::$prefix.$name]);
	}
	
	
	public static function is_set($name) {
		return isset($_COOKIE[self::$prefix.$name]);
	}
}?>
