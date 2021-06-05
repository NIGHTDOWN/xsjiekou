<?php

namespace ng169\cache;

checktop();
class Rediscache
{
	#缓存目录
	private $nosqlobj;
	#超时时长
	private  $timeout;
	#编码设置
	private $charset;
	private $flag = false;
	private $host;
	private $post;
	private $pre;

	private static $_instance = null; //静态实例
	private static $_redis = null; //静态实例
	/**
	 * 选取redis.inc.php索引的对应的配置
	 * @param undefined $index
	 * 
	 * @return
	 */
	private
	function __construct($index = null)
	{

		$config = include(CONF . '/redis.inc.php');
		if ($index == null) {
			$info = $config['main'];
		} else {
			$info = $config[$index];
		}
		$this->host = $info['host'];
		$this->port = $info['port'];
		$this->pwd = $info['pwd'];
		$this->num = $info['num'];
		$this->timeout = $info['timeout'];
		$this->pre = $info['pre'];

		/* $this->nosqlobj = new \Redis(); 
		$this->nosqlobj->connect($this->host,$this->port);*/
		try {
			//code...
			self::$_redis = new \Redis();
			self::$_redis->connect($this->host, $this->port);
			self::$_redis->auth($this->pwd);
			self::$_redis->select($this->num);
		} catch (\Throwable $th) {
			//throw $th;
			d($th);
		}


		/*return $this;*/
	}

	private function __clone()
	{
	}
	//获取静态实例
	public static  function getRedis()
	{
		if (!self::$_instance) {
			self::$_instance = new self();
			/* new self;*/
		}

		return self::$_instance;
	}

	/**
	 * 
	 * @param undefined $name
	 * @param undefined $value
	 * @param undefined $timeout 有效时间长
	 * 
	 * @return
	 */
	public
	function set($name, $value, $timeout = null)
	{
		if (!$name) return false;
		/*$timeout = $timeout?$timeout:$this->timeout;*/
		if ($timeout > 1) {
			$timeout = time() + $timeout;
		} else {
			if ($timeout === NULL) {
				$timeout = intval(time()) + intval($this->timeout);
			} else {
				$timeout = $timeout;
			}
		}
		$data = ['value' => $value, 'expired' => $timeout];
		$data = json_encode($data);
		return  self::$_redis->set($this->pre . $name, $data);
	}
	public
	function get($name)
	{
		if (!$name) return false;
		$data = self::$_redis->get($this->pre . $name);
		$data = json_decode($data, 1);
		if ($data['expired'] <= 1) {
			// return $data['value'];
		}
		if (time() > $data['expired']) {
			$this->del($name);
			return [false, $data['value']];
		} else {
			// return $data['value'];
		}
		return [true, $data['value']];
		/*return array((bool)($data),$data);*/
	}
	public
	function del($name = null)
	{
		if (!$name) return self::$_redis->flush();
		return   self::$_redis->del($this->pre . $name);
	}
	public
	function close()
	{
		return self::$_redis->close();
	}
}
