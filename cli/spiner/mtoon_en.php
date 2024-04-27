<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once (dirname(__FILE__)) . "/spbase/cart_mtoon.php";



//英文
$ob = new cart_mtoon();
$ob->_booklang = 1;


$ob->_bookdstdesc = '英文_mtoon';
$ob->appneedinfo['_language'] = 'en';
$ob->initsp();
$ob->start();
