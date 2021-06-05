<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once (dirname(__FILE__)) . "/spbase/cart_mtoon.php";

// use \ng169\cli\Clibase;
//印尼
$ob = new cart_mtoon();
$ob->_booklang = 2;

$ob->_bookdstdesc = '印尼_mtoon';
$ob->appneedinfo['_language'] = 'id';
$ob->initsp();
$ob->start();
