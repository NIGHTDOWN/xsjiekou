<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once (dirname(__FILE__)) . "/spbase/cart_mtoon.php";

$ob = new cart_mtoon();
$ob->_booklang = 5;


$ob->_bookdstdesc = '中国_mtoon';
$ob->appneedinfo['_language'] = 'cn';
$ob->initsp();
$ob->start();
