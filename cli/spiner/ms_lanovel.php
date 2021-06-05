<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once (dirname(__FILE__)) . "/lanovel.php";


$ob = new Splanovel();
$ob->reg();
//ms
$ob->start();
