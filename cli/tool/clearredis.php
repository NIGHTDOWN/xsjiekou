<?php
//批量上架书，并且更新收费章节
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
namespace ng169\cli\tool;


#相对URL路径
error_reporting(E_ALL ^ E_NOTICE);

require_once   dirname(dirname((__FILE__))) . "/clibase.php";



use ng169\Y;
Y::$cache->clear();

