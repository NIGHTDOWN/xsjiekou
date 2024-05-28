<?php

/**
 */
namespace ng169\cli;
require_once    "./sock/sockbase.php";

use ng169\lib\Socket;
use ng169\Y;
use ng169\cli\sock\sockbase;
use Socket as GlobalSocket;

  $ims=[]; //connectObj 客户端列表
class connectObj
{
    public $sock;
    public $index;
    public $ishand=false;//是否握手
    public $type; //1表示server,2表示用户
    public $buffer; //1表示server,2表示用户
    public function __construct(&$sk, $_type,&$data)
    {
        $this->index = intval($sk);
        if(isset($ims[$this->index])){
            return $ims[$this->index];
        }else{
            //压如对象指针
             $ims[$this->index]=&$this; 
             $this->sock = &$sk;
             $this->type = $_type;
        }
        $this->buffer = &$data;
     
    }
   
    //从数据库恢复
    public function resetfromsql(){

    }
    public function savesql(){
        
    }
    public function del()
    {
    }
    public function getsk()
    {
        return  $this->sock;
    }
}

//数据库连接池
class Im extends Clibase
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
        Socket::$isServer = true;
        self::$server = new sockbase();
        self::$server->onmsg(__NAMESPACE__ . '\Im::inmsg');
        self::$server->dismsg(__NAMESPACE__ . '\Im::dis');
        $poolconf = \ng169\lib\Option::get('pool');
        self::$pwd = $poolconf['pwd'];
        self::$server->start($poolconf['ip'], $poolconf['port']);
    }
    //消息解码
    public static function decode($data)
    {
        //三步；websocket协议编码；反base64；转json对象
        $data= Socket::parse( $data);
        $data= base64_decode($data);
        return json_decode($data, 1);
    }
    //消息编码
    public static function encode($data)
    {
        return json_encode($data);
    }
    public static function dis($clientsock)
    {
        $key = intval($clientsock);
        if (isset(self::$sqlserver[$key])) {
            unset(self::$sqlserver[$key]);
        }
        if (isset(self::$connects[$key])) {
            unset(self::$connects[$key]);
        }
        $k = array_search($key, self::$kx);
        if ($k) {
            unset(self::$kx[$k]);
        }
        self::UiShow();
    }
    //检测各链接的密码是否正确,正确保持连接,否则断开连接
    public static function checkpwd($pwd)
    {
        if ($pwd == self::$pwd) return true;
        return false;
    }
    //空闲服务
    static  $kx = [];
    static  $mltime = [];
    //忙碌服务
    static  $ml = [];
    //这里的data是带长度的封包；不带长度的需要用socket::getolddata获取源数据
    public static function inmsg($clientsock, $data)
    {

        try {
            //心跳包忽略
            if (strlen($data) < 1) {
                return;
            }
            //装载对象
            if(isset(self::$connects[intval($clientsock)])){
                $obj = self::$connects[intval($clientsock)];
            }else{
                $obj = new connectObj($clientsock, 2,$data);
                self::$connects[intval($clientsock)] = $obj;
            }
           
            $dedata = self::decode($data);
            //所有连接都记录在connect
            if (!$dedata) return false; //无法解码表示数据不对;丢弃
            switch ($dedata['type']) {
                case '1': //这里是连接数据库的
                  
                    //处理server注册
                    break;
                case '2': //这里是连接php-fpm的,连接端不用注册
                    //一半线程读.一半线程写
                    //这里要绑定执行sql的服务跟来源客户端,以便返回
                    //空闲随机取一个服务,绑定忙碌;
                  
                    break;
                case '3': //这里是连接php-fpm的,连接端不用注册
                    //这里要获取回传客户端
                    // $oob = self::$connects[self::$key];
                  
                    break;
                default:
                
                    # code...
                  //消息入库
                 
                //消息返回状态成功；
                d($dedata);
                self::send($obj,"1234fdsfdsfsdfdsfsdfdsfsdfsdfdsfsdfsdfsdf");
                stream_socket_sendto($clientsock, $dedata, 0);
                //消息入库；
                //消息通知客服


                break;
            }
        } catch (\Throwable $th) {
            //throw $th;
            d($th);
        }
    }
    private static function send($obj, $data)
    {
        if ($obj) {
            d(intval($obj->getsk()));
            stream_socket_sendto($obj->getsk(), $data, 0);
            d("发送了啊");
            // \ng169\lib\Socket::senddecodeMsg($obj->getsk(), $data);
        }
    }
   

 

  
   
    //输出信息
    public static function UiShow()
    {
        return;
        self::clear();
        echo "服务信息:" . self::$server->ip . ":" . self::$server->port . "\n";
        echo "当前SqlServer连接池:" . sizeof(self::$sqlserver) . "\n";
        echo "当前所有连接数量:" . sizeof(self::$connects) . "\n";
        // echo chr(3); // 输出文本结束控制字符，这样可以清除之前输出的文本内容
        // echo chr(8); // 将前一个控制字符删掉，避免在控制台留下控制字符的标记
        // // echo "\e[H\e[J";
        // // echo "\r\n";
        // // echo 123;
        // // ncurses_erase();
        // // system('clear');
        // // system('cls');
    }
}
//启动数据库连接池server

new Im();
