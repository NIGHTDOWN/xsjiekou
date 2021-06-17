<?php

//socke
require_once   dirname(dirname(__FILE__)) . "/clibase.php";

use ng169\lib\Socket;
use ng169\Y;

class sockbase extends Clibase
{

    public function __construct()
    {
        parent::__construct(); //初始化帮助信息

    }
    //调试类
    public function start()
    {
        Socket::starts('127.0.0.1', '8123');
    }


    public function help()
    {
        d('本地同步到远程-修改参数,支持参数书籍类型type，语言lang');
    }
}
$sock = new sockbase();
$sock->start();
/**
 * 本服务接收两个参数  IP 端口   
 * 列子 ：php opsock 192.168.1.1 8080
 */
/*
header('Access-Control-Allow-Origin:*'); 
// 响应类型 
header('Access-Control-Allow-Methods:POST'); 
// 响应头设置 
header('Access-Control-Allow-Headers:x-requested-with,content-type');*/
// define('ROOT',dirname(__FILE__).'/');
// #相对URL路径
// if(!defined('PATH_URL'))define('PATH_URL','/');
// require_once ROOT.'source/core/enter.php';
// use \ng169\lib;
// use \ng169\tool\File;
/*$server = 'udp://0.0.0.0:53'; 
//消息结束符号 
$msg_eof = "\n"; 
echo '开启';
$socket = stream_socket_server($server, $errno, $errstr, STREAM_SERVER_BIND); 
if (!$socket) { 
    die("$errstr ($errno)"); 
} 
   
do { 
    //接收客户端发来的信息 
    $inMsg = stream_socket_recvfrom($socket, 1024, 0, $peer); 
    //服务端打印出相关信息 
    echo "Client : $peer\n"; 
    echo "Receive : {$inMsg}"; 
    //给客户端发送信息 
    $outMsg = substr($inMsg, 0, (strrpos($inMsg, $msg_eof))).' -- '.date("D M j H:i:s Y\r\n"); 
    stream_socket_sendto($socket, $outMsg, 0, $peer); 
       
} while ($inMsg !== false);
*/


// $port=53;
// if($argc<2){
// $ipx='0.0.0.0';	
// }else{
// 	if(isset($argv[1])){
// 		$ipx=$argv[1];
// 	}
// 	if(isset($argv[2])){
// 		$port=$argv[2];
// 	}
// }
// $text=File::readContent('/hosts');
// //preg_match_all("/([\n\r])(\s)*/",$text,$lists, PREG_OFFSET_CAPTURE);

// preg_match_all("/\b(([\d]{1,3}\.?){4})([\t\n]{1,}(([\S]{1,}\.?){2,}))\s{1,}/",$text,$lists, PREG_OFFSET_CAPTURE);

// $dns=[];
// if(isset($lists[4])){
// 	foreach($lists[4] as $i=>$row){
// 		$dns[$row[0]]=$lists[1][$i][0];	
// 	}
// }
// \ng169\sock\system\udp::$dnslist=$dns;
// 			//load hosts
// $ip=isset($_SERVER['SERVER_ADDR'])?$_SERVER['SERVER_ADDR']:$ipx;
// $ip=$ip=='127.0.0.1'?$ipx:$ip;
// 	\ng169\lib\Socket::$call=['\ng169\sock\system\udp','init_udp'];	
// 	\ng169\lib\Socket::$needresolving=false;	
// \ng169\lib\Socket::starts($ip,$port,'udp');
// //\ng169\lib\Socket::starts($ip,$port,'udp');
