<?php

/**
 * 开启master
 * 选择子线程
 * 主线程管理子线程状态；已经转发消息
 * 另外开一个线程用于master心跳
 * 子线程记录接收消息
 */
require_once    "sock/sockbase.php";
use ng169\Y;
$a=new sockbase();
$a->start();
