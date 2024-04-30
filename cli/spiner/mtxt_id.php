<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */



 require_once "spbase/mtoon_txt.php";

use ng169\cli\spiner\spbase\mtoon_txt; 
$ob = new mtoon_txt();
$ob->_booklang = 2;
$ob->_bookdstdesc = '印尼_mtxt';
$ob->appneedinfo['_language'] = 'id';
$ob->initsp();
$ob->start();






