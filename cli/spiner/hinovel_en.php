<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once (dirname(__FILE__)) . "/spbase/hinovel.php";





// 英国
$ob = new Sphinovel();
$ob->_booklang = 1;
$ob->_bookdstdesc = '英国_hinovel';
$ob->_domian = "https://enapi.hinovelasia.com";
$ob->appneedinfo['lang'] = 'en';
$ob->appneedinfo2['column_id'] = '1';
$ob->initsp();
$ob->reg();
$ob->start();
