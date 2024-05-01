<?php


namespace ng169\lib;


class SocketCache {
	
	#超时时长
	private  $timeout;
	#编码设置	
	private static $_instance = null; //静态实例
	
	private static $_data = []; //静态实例
	/**
	* 选取redis.inc.php索引的对应的配置
	* @param undefined $index
	* 
	* @return
	*/
	private 
	function __construct($index=null){
	}
	
	private function __clone(){}
	//获取静态实例
	public static  function getCache(){
		if(!self::$_instance){
			self::$_instance = new self();
		}
		return self::$_instance;
	}


	public
	function set($name,$value,$timeout = null){
		if(!$name)return false;

		if($timeout>1){
			$timeout=time()+$timeout;	
		}else{	
			$timeout=0;
		
		}
		$data=['value'=>$value,'expired'=>$timeout];
		$data=json_encode($data);

		if($name){
			self::$_data[$name]=$data;
		}
		return 1;
	}
	public
	function get($name){
		if(!$name)return false;
		if(!isset(self::$_data[$name])){
			return false;
		}
		$data = self::$_data[$name];
		$data=json_decode($data,1);
		if($data['expired']<=1){
			return $data['value'];	
		}
		if(time()>$data['expired'] ){
			$this->del($name);
			return false;
		}else{
			return $data['value'];	
		}
	}
	public
	function del($name = null){
		if(!$name)return self::$_data=[];
		if(isset(self::$_data[$name])){
			   unset(self::$_data[$name]);
		}
		return 1;
	}
	public
	function close(){
		return self::$_data=[];
		
	}
}

?>
