<?php
/**
* 本服务接收两个参数  IP 端口   
* 列子 ：php opsock 192.168.1.1 8080
*/
/*
header('Access-Control-Allow-Origin:*'); 
// 响应类型 
header('Access-Control-Allow-Methods:POST'); 
// 响应头设置 
header('Access-Control-Allow-Headers:x-requested-with,content-type');*/
define('ROOT',dirname(__FILE__).'/');
#相对URL路径

if(!defined('PATH_URL'))define('PATH_URL','/');

require_once ROOT.'source/core/enter.php';
require_once ROOT.'spinerds.php';
use \ng169\lib;
//12小时执行一次
ng169\lib\Job::add(1, function(){
			// M('queue','im')->task();
			start();
		
	});
