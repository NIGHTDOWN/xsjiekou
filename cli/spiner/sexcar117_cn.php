<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





 require_once "spbase/sexcar117.php";

use ng169\cli\spiner\spbase\sexcar117; 

$ob = new sexcar117();
$ob->_booklang = 5;
$ob->_bookdstdesc = '中国_情色_txt';

$ob->initsp();
$ob->start();