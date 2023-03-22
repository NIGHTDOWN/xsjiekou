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


class connectObj{
    public $sock;
    public $index;
    public $type;//1表示server,2表示用户
    public function connectObj(&$sk,$_type){
        $this->sock= &$sk;
        $this->type=$_type;
        $this->index=intval($sk);
    }
    public function del(){

    }
    public function getsk(){
        return  $this->sock;
    }
}
echo 222222222;
//数据库连接池
class SqlPool extends Clibase
{
    private static $sqlserver; //数据库连接线程
    private static $client; //服务器php-fpm连接
    private static $connects; //所有连接
    private static $server; //所有连接
    private static $skqueue=[];
    // private static $ip;
    // private static $port;
    public function __construct()
    {
      
        self::$server = new sockbase();
        self::$server->onmsg(__NAMESPACE__ . '\SqlPool::inmsg');
        self::$server->dismsg(__NAMESPACE__ . '\SqlPool::dis');
      
        $poolconf= ng169\lib\Option::get('pool');
        // self::$ip=$poolconf['ip'];
        // self::$port=$poolconf['port'];
        self::$server->start($poolconf['ip'], $poolconf['port']);
        // self::$server->start("127.0.0.1", "4563");
    }
    //消息解码
    public static function decode($data)
    {
        return json_decode($data,1);
    }
    //消息编码
    public static function encode($data)
    {
        return json_encode($data);
    }
    public static function dis($clientsock)
    {
       
        if(isset(self::$sqlserver[intval($clientsock)])){
            unset(self::$sqlserver[intval($clientsock)]);
        }
        if(isset(self::$connects[intval($clientsock)])){
            unset(self::$connects[intval($clientsock)]);
        }
        self::UiShow();
    }
    //检测各链接的密码是否正确,正确保持连接,否则断开连接
    public static function checkpwd($pwd)
    {
        if($pwd=="123456")return true;
        return false;
    }
    public static function inmsg($clientsock, $data)
    {
     
        try {
            //心跳包忽略
            if (strlen($data) < 5) {
                return;
            }
            $dedata = self::decode($data);
            $obj=new connectObj($clientsock,1);
            self::$connects[$obj->index]=$obj;
            if (!$dedata) return false; //无法解码表示数据不对;丢弃
            switch ($dedata['type']) {
                case '1': //这里是连接数据库的
                  
                    $data=self::decode($dedata['data']);
                    if(!$data)return;
                    if(!$data['pwd'])return;
                    if(!self::checkpwd($data['pwd']))return;
                    self::$sqlserver[$obj->index]=$obj;
                    self::UiShow();
                    //处理server注册
                    break;
                case '2': //这里是连接php-fpm的,连接端不用注册
                    // $data=self::decode($dedata['data']);
                    // if(!$data)return;
                    //一半线程读.一半线程写
                    array_push(self::$skqueue[intval($clientsock)],$data);
                
                    if(sizeof(self::$sqlserver)){
                        $obj=self::$sqlserver[array_key_first(self::$sqlserver)];
                        ng169\lib\Socket::senddecodeMsg($obj->getsk(),$data);
                    }
                    // $obj=new connectObj($clientsock,1);
                    // self::$connects[$obj['index']]=$obj;
                    // self::$client[$obj['index']]=$obj;
                    //处理用户注册,
                    break;
                default:
                    # code...
                    break;
            }
          
        } catch (\Throwable $th) {
            //throw $th;
            d($th);
        }
    }
   
    public static function addsqlSvr()
    {
    }
    public static function addsqlClient()
    {
    }
    public static function sendsql()
    {
    }
    //输出信息
    public static function UiShow(){
    
        self::clear();
        echo "服务信息:".self::$server->ip.":".self::$server->port."\n";
        echo "当前SqlServer连接池:".sizeof(self::$sqlserver)."\n";
        echo "当前所有连接数量:".sizeof(self::$connects)."\n";
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

new SqlPool();
echo 2222222222222;
