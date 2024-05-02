<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once (dirname(__FILE__)) . "/spbase/hinovel.php";



//越南
$ob = new Sphinovel();
$ob->_booklang = 3;
$ob->_bookdstdesc = '越南_hinovel';
$ob->_domian = "https://vnapi.hinovelasia.com";
$ob->appneedinfo['lang'] = 'vi';
$ob->appneedinfo2['column_id'] = '5';
$ob->initsp();
$ob->reg();
$ob->start();
