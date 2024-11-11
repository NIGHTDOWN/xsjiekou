<?php

/**
 */

namespace ng169\tool;

require_once    "clibase.php";

im(TOOL."ngSwoole.php");

class Im extends \ng169\cli\Clibase
{
    private static $sqlserver; //数据库连接线程
    private static $connects; //所有连接
    private static $server; //所有连接
    private static $skqueue = [];
    private static $pwd = "";
    // private static $ip;
    // private static $port;
    public function __construct()
    {
        $sw=new \ng169\tool\ngSwoole();
        $sw->start("1199");
     
    }
    //消息解码
    public static function decode($data)
    {
        //三步；websocket协议编码；反base64；转json对象
        $data = Socket::parse($data);
        $data = base64_decode($data);
        return json_decode($data, 1);
    }
    //消息编码
    public static function encode($data)
    {
        return json_encode($data);
    }
   
   
    public static function inmsg($clientsock, $data)
    {
       
    }
    //想sock发送消息
    public static function send($obj, $data)
    {
       
    }






    //输出信息
    public static function UiShow()
    {
        return;
        
        // echo "服务信息:" . self::$server->ip . ":" . self::$server->port . "\n";
        // echo "当前SqlServer连接池:" . sizeof(self::$sqlserver) . "\n";
        // echo "当前所有连接数量:" . sizeof(self::$connects) . "\n";
        // echo chr(3); // 输出文本结束控制字符，这样可以清除之前输出的文本内容
        // echo chr(8); // 将前一个控制字符删掉，避免在控制台留下控制字符的标记
        // // echo "\e[H\e[J";
        // // echo "\r\n";
        // // echo 123;
        // // ncurses_erase();
        // // system('clear');
        // // system('cls');
    }
    //帮助doc参数stop ；start ；reload；restart ；status ；stop ；start ；reloa
    public function help(){


    }
}
//启动数据库连接池server

new Im();






