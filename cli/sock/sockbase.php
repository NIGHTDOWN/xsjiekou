<?php
namespace ng169\cli\sock;
/**
 * 开启master
 * 选择子线程
 * 主线程管理子线程状态；已经转发消息
 * 另外开一个线程用于master心跳
 * 子线程记录接收消息
 */

require_once   dirname(dirname(__FILE__)) . "/clibase.php";

use ng169\cli\Clibase;
use ng169\lib\Socket;
use ng169\Y;

class sockbase extends Clibase  
{

    public function __construct()
    {
      
        parent::__construct(); //初始化帮助信息

    }
    //获取用户token,im消息根据这个token加密
    public static function gettoken($uid)
    {
        return '';
    }
    //调试类
    public function start($ip,$port)
    {
        $get=$this->get($ip,$port);
        if(!$ip){
            $ip = isset($get['ip']) ? $get['ip'] : error('ip未确定');
        }
      if(!$port){
        $port = isset($get['port']) ? $get['port'] : error('端口未确定');
      }
    //   $ip = isset($get['ip']) ? $get['ip'] : error('ip未确定');
    //   $port = isset($get['port']) ? $get['port'] : error('端口未确定');
    //     $get = $this->getargv(['ip', 'port', 'ismaster', 'type']);
       
        $ismaster = isset($get['ismaster']) ? 1 : 0;
        $type = isset($get['type']) ? $get['type'] : 'tcp';
        $ssl = false;
       
        Socket::starts($ip, $port, $type, $ssl, $ismaster);
    }
    public function onmsg($function){
        
    Socket::$onMsg=$function;
   
   
    }
    public function dismsg($function){
        
        Socket::$disMsg=$function;
       
       
        }
    public function help()
    {
        echo ('开启参数,支持参数type，值为tcp或者udp' . "\n");
        echo ('开启参数,支持参数ismaster，值为0或者1;启动master时候会重新检测一遍slave' . "\n");
        echo ('开启参数,支持参数ip,port，值绑定得ip以及端口' . "\n");
    }
}
// $sock = new sockbase();
// $sock->start();
