<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */


require_once (dirname(__FILE__)) . "/spbase/mtoon_txt.php";


//越南
$ob = new mtoon_txt();
$ob->_booklang = 0;

$ob->_bookdstdesc = '泰国_mtxt';
$ob->appneedinfo['_language'] = 'th';
$ob->initsp();
$ob->start();


