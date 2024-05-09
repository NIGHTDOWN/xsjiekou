<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





 require_once "spbase/sexcar.php";

use ng169\cli\spiner\spbase\sexcar; 

$ob = new sexcar();
$ob->_booklang = 5;
$ob->_bookdstdesc = '中国_情色_txt';
$ob->appneedinfo['_language'] = 'cn';
$ob->initsp();
$ob->start();