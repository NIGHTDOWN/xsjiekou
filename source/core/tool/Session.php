<?php



namespace ng169\tool;
use ng169\tool\Filter as YFilter;
checktop();
class Session
{

	
	public static function set($key, $value = '')
	{
		if (!session_id())
			self::start();
		if (!is_array($key))
		{
			$_SESSION[$key] = trim($value);
		}
		else
		{
			foreach ($key as $k => $v)
				$_SESSION[$k] = $v;
		}
		return true;
	}
	public static function check($value)
	{
		if (self::get('verifycode') == $value)
		{
			self::del('verifycode');
			return true;
		}
		else
		{
			self::del('verifycode');
			return false;
		}
		;

	}
	
	public static function get($key)
	{
		if (!session_id())
			self::start();
		return (isset($_SESSION[$key])) ? YFilter::filterBadChar(trim($_SESSION[$key])) : null;
	}

	
	public static function del($key)
	{
		if (!session_id())
			self::start();
		if (is_array($key))
		{
			foreach ($key as $k)
			{
				if (isset($_SESSION[$k]))
					unset($_SESSION[$k]);
			}
		}
		else
		{
			if (isset($_SESSION[$key]))
				unset($_SESSION[$key]);
		}
		return true;
	}

	
	public static function clear()
	{
		if (!session_id())
			self::start();
		session_destroy();
		$_SESSION = array();
	}

	
	private static function start()
	{
		session_start();
	}

}
?>
