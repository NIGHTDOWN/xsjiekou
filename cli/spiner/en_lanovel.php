<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once (dirname(__FILE__)) . "/lanovel.php";


$ob = new Splanovel();
$ob->_booklang = 1;
$ob->wordrate = 9;
$ob->_bookdstdesc_int = 2;
$ob->_bookdstdesc = 'en-lanovel';
$ob->appneedinfo = [
    "version" => "1.3.7",
    "language" => "EN",
];
$ob->initsp();
$ob->reg();
//ms
$ob->start();
