<?php

/**
 * 开启master
 * 选择子线程
 * 主线程管理子线程状态；已经转发消息
 * 另外开一个线程用于master心跳
 * 子线程记录接收消息
 */
require_once    "sock/sockbase.php";

use ng169\lib\Socket;
use ng169\Y;


class connectObj
{
    public $sock;
    public $index;
    public $type; //1表示server,2表示用户
    public function connectObj(&$sk, $_type)
    {
        $this->sock = &$sk;
        $this->type = $_type;
        $this->index = intval($sk);
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
class SqlPool extends Clibase
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
        self::$server->onmsg(__NAMESPACE__ . '\SqlPool::inmsg');
        self::$server->dismsg(__NAMESPACE__ . '\SqlPool::dis');
        $poolconf = ng169\lib\Option::get('pool');
        self::$pwd = $poolconf['pwd'];

        self::$server->start($poolconf['ip'], $poolconf['port']);

        // self::$server->start("127.0.0.1", "4563");
    }
    //消息解码
    public static function decode($data)
    {
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
    public static function inmsg($clientsock, $data)
    {
        try {
            //心跳包忽略
            if (strlen($data) < 5) {
                return;
            }
            $dedata = self::decode($data);
            $obj = new connectObj($clientsock, 1);
            //所有连接都记录在connect
            self::$connects[$obj->index] = $obj;
            if (!$dedata) return false; //无法解码表示数据不对;丢弃
            switch ($dedata['type']) {
                case '1': //这里是连接数据库的
                    self::addsqlSvr($obj, $dedata);
                    self::UiShow();
                    //处理server注册
                    break;
                case '2': //这里是连接php-fpm的,连接端不用注册
                    //一半线程读.一半线程写
                    //这里要绑定执行sql的服务跟来源客户端,以便返回
                    //空闲随机取一个服务,绑定忙碌;
                    $sqlSvr = self::getkxSvr($obj);
                    if ($sqlSvr) {
                        self::send($sqlSvr, $data);
                        break;
                    } else {
                        //没空闲的时候把记录缓存到消息队列排队.等待服务进程释放,在来继续执行
                        self::inQueue($obj, $data);
                        break;
                    }
                    break;
                case '3': //这里是连接php-fpm的,连接端不用注册
                    //这里要获取回传客户端
                    // $oob = self::$connects[self::$key];
                    $client = self::sfSvr($obj);
                    self::send($client, $data);
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
    private static function send($obj, $data)
    {
        if ($obj) {
            ng169\lib\Socket::senddecodeMsg($obj->getsk(), $data);
        }
    }
    private static function inQueue($obj, $data)
    {
        array_push(self::$skqueue[$obj->index], $data);
    }
    private static function loopQueue()
    {
        foreach (self::$skqueue as $skindex => $onesks) {
            foreach ($onesks as $key => $data) {
                $client = self::getkxSvr(self::$connects[$skindex]);
                if ($client) {
                    self::send($client, $data);
                    unset(self::$skqueue[$skindex][$key]);
                } else {
                    //没取到直接退出队列
                    return;
                }
                //没空闲继续释放,等待下一次空闲释放
                # code...
            }
            # code...
        }
    }
    //释放忙碌服务返回忙碌绑定的客户端
    private static function sfSvr($mlSvr)
    {
        $index = $mlSvr->index;
        $clientid = self::$ml[$index];
        unset(self::$ml[$index]);
        unset(self::$mltime[$index]);
        array_push(self::$kx, $index);
        self::loopQueue();
        return self::$connects[$clientid];
    }
    //获取空闲服务
    private static function getkxSvr($obj)
    {

        if (sizeof(self::$kx) == 0) {
            self::qzsfSvr();
            d("当前无服务进程");
            return;
        }
        // $key = array_rand(self::$kx);
        //优先取第一条服务
        $key =  array_key_first(self::$kx);
        $d = self::$kx[$key];
        unset(self::$kx[$key]); //移除空闲
        //绑定忙碌
        self::$ml[$d] = $obj->index;
        self::$mltime[$d] = time() + 10; //10秒查询超时
        return self::$sqlserver[$d];
    }
    public  static function qzsfSvr()
    {
        $n = time();
        foreach (self::$mltime as $key => $value) {
            if ($value > $n) {
                self::sfSvr(self::$sqlserver[$key]);
            }
        }
    }
    //注册sql服务
    public static function addsqlSvr($skobj, $dedata)
    {

        $data = self::decode($dedata['data']);
        if (!$data) return;
        if (!$data['pwd']) return;

        if (!self::checkpwd($data['pwd'])) return;
        //添加服务记录,如果存在同一个socket;则不变化
        if (array_search(self::$sqlserver, $skobj)) return;
        self::$sqlserver[$skobj->index] = $skobj;
        //添加空闲记录
        array_push(self::$kx, $skobj->index);
    }
    public static function addsqlClient()
    {
    }
    public static function sendsql()
    {
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

new SqlPool();
