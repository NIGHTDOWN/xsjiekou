<?php


namespace ng169\service;

checktop();
class Output{
	private static $header;
	private static $outtype;
	private static $body;
	private static $data = [];
	public static function start(){
		//允许跨域 
		header("Content-type: text/html; charset=".G_CHARSET);
		if(G_ALLOW_ORIGIN){
			Header("Access-Control-Allow-Origin:*");
			Header("Access-Control-Allow-Method:POST,GET");
		}
  
		if(!G_ERRORLEVEL){
			error_reporting(E_ALL || ~E_NOTICE);
		}

	}
	/**
	* 输出内容
	* @param string $body
	*
	* @return void
	*/
	public static function show($html){
		if($html)echo $html;
		echo self::$body;
	}
	public static function get($key = null){
		if(!$key)return self::$data;
		if(isset(self::$data[$key]))return self::$data[$key];
		return false;
	}
	/**
	* 添加输出内容体
	* @param string $body
	*
	* @return void
	*/
	public static function set($key,$value){
		self::$data[$key] = $value;
	}
	/**
	* 设置输出html内容
	* @param undefined $body
	*
	* @return
	*/
	public static function setHtml($body){
		self::$body .= $body;
	}
	public static function out($data){
		if(is_string($data)){
			echo $data;
		}else{
			echo json_encode($data);
		}
  
  
		die();
	}
}

?>
