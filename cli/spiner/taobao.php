<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once (dirname(__FILE__)) . "/spbase/taobaobase.php";



//俄罗斯
$ob = new taobaobase();
// $ob->_booklang = 7;
// $ob->_bookdstdesc = '俄罗斯_hinovel';
// $ob->_domian = "https://api.hinoveleurope.com";
// $ob->appneedinfo['lang'] = 'ru';
// $ob->appneedinfo2['column_id'] = '5';

// $ob->initsp();
// $ob->reg();
$ob->start();
