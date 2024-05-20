<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





 require_once "spbase/sexcarbika.php";

use ng169\cli\spiner\spbase\sexcarbika; 

$ob = new sexcarbika();
$ob->_booklang = 5;
$ob->_bookdstdesc = '中国_哔咔哔咔_漫画';

$ob->initsp();
$ob->start();