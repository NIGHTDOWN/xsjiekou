<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once (dirname(__FILE__)) . "/hinovel.php";




//印尼
$ob = new Sphinovel();
$ob->_booklang = 2;
$ob->_bookdstdesc = '印尼_hinovel';
$ob->_domian = "https://idapi.hinovelasia.com";
$ob->appneedinfo['lang'] = 'id';

$ob->appneedinfo2['column_id'] = '7';
$ob->initsp();
$ob->reg();
$ob->start();
