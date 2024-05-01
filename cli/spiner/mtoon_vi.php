<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





 require_once "spbase/mtoon_txt.php";
 use ng169\cli\spiner\spbase\cart_mtoon;

//越南
$ob = new cart_mtoon();
$ob->_booklang = 3;

$ob->_bookdstdesc = '越南_mtoon';
$ob->appneedinfo['_language'] = 'vi';
$ob->initsp();
$ob->start();
