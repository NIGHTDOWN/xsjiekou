<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once (dirname(__FILE__)) . "/spbase/cart_mtoon.php";


//越南
$ob = new cart_mtoon();
$ob->_booklang = 3;

$ob->_bookdstdesc = '越南_mtoon';
$ob->appneedinfo['_language'] = 'vi';
$ob->initsp();
$ob->start();
