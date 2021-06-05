<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once (dirname(__FILE__)) . "/cart_mtoon.php";

// use \ng169\cli\Clibase;
//印尼
$ob = new cart_mtoon();
$ob->_booklang = 2;

$ob->_bookdstdesc = '印尼_mtoon';
$ob->appneedinfo['_language'] = 'id';
$ob->initsp();
$ob->start();
//越南
$ob = new cart_mtoon();
$ob->_booklang = 3;

$ob->_bookdstdesc = '越南_mtoon';
$ob->appneedinfo['_language'] = 'vi';
$ob->initsp();
$ob->start();
//中国
$ob = new cart_mtoon();
$ob->_booklang = 5;


$ob->_bookdstdesc = '中国_mtoon';
$ob->appneedinfo['_language'] = 'cn';
$ob->initsp();
$ob->start();
//英文
$ob = new cart_mtoon();
$ob->_booklang = 1;


$ob->_bookdstdesc = '英文_mtoon';
$ob->appneedinfo['_language'] = 'en';
$ob->initsp();
$ob->start();
