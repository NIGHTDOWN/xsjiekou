<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





 require_once "spbase/pmj029.php";

use ng169\cli\spiner\spbase\pmj029; 

$ob = new pmj029();
$ob->_booklang = 5;
$ob->_bookdstdesc = '中国_pmj029_txt';
$ob->appneedinfo['_language'] = 'cn';
$ob->initsp();
$ob->start();