<?php

/**
 * 开启master
 * 选择子线程
 * 主线程管理子线程状态；已经转发消息
 * 另外开一个线程用于master心跳
 * 子线程记录接收消息
 */

use ng169\sock\time;

require_once   dirname(dirname(__FILE__)) . "/clibase.php";

new time();
