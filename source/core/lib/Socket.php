<?php


namespace ng169\lib;

use ng169\Y;
use ng169\lib\Log as YLog;
use ng169\lib\Job;
use ng169\lib\Tcp;
use ng169\lib\Udp;

/*use ng169\cache\Rediscache ;*/
use ng169\lib\SocketCache;
use ng169\sock\slave;
use ng169\sock\master;
use sockbase;

checktop();
define('OS_TYPE_LINUX', 'linux');
define('OS_TYPE_WINDOWS', 'windows');
define('S_OPEN', '1');
define('S_CLOSE', '0');
define('USERMSG', '0');
define('ADMINMSG', '1');
define('SYSMSG', '2');




class Socket extends Y
{
	public static $isLengthMode = false; //是否支持长度模式；支持的话getbuf就是长度模式
	const LISTEN_SOCKET_NUM = 100000;
	const CHECK_REFLASH_TIME = 30; //广播间隔时间30秒；
	const REDIS_PRE_C = ":tokenCid_"; //广播间隔时间30秒；
	const REDIS_PRE_U = ":tokenUid_"; //广播间隔时间30秒；
	public static $sockets = [];
	public static $sks = [];
	public static $redis = null;
	public static $master; //当前sock
	public static $masters = []; //master进程里面的slave数据集
	public static $ip;
	public static $sid;
	public static $port;
	public static $stop = false;
	public static $userlogin = []; //记录刷新的用户id；防止重复广播
	private static $type = "tcp";
	public static $_OS = OS_TYPE_LINUX;
	public static $PCNTL = false;
	public static $EPOLL = false;
	public static $coons = 0; //当前连接数
	public static $syscode = 0;
	public static $admincode = 0;
	public static $sips = [];
	public static $sip = [];
	public static $event_base = NULL;
	public static $debug = false;
	public static $sysconn = [];
	//是否主服务器
	public static $is_master = false;
	public static  $server = NULL;
	//链接master得套字节
	public static  $slave = NULL;
	public static $call = false; //指定操作回调
	public static $needresolving = true; //需要解析
	public static $onMsg; //接受tcp数据处理函数
	public static $disMsg; //断开tcp数据处理函数
	public static $isServer = false;
	public static function info($msg)
	{
		if (self::$debug) {
			d($msg);
		}
	}
	/*
	*检测是否已经开启了该端口服务
	*/
	public static function check_sock_open($ip, $port)
	{
		$port   = $port ? $port : DB_SOCKPOST;
		$type   = self::$type;
		$socket = @stream_socket_client("$type://$ip:$port", $errno, $errstr);
		if (gettype($socket) != 'resource') {
			return false;
		}
		fclose($socket);
		unset($socket);
		return true;
	}

	protected static function reset_client()
	{
		T('sock_client')->del(['sid' => self::$sid]);
	}
	/**
	 * Check sapi.
	 *
	 * @return void
	 */
	protected static function checkSapiEnv()
	{
		// Only for cli.
		/*if(php_sapi_name() != "cli"){
		exit("only run in command line mode \n");
		}*/
		//检查系统类型
		if (DIRECTORY_SEPARATOR === '\\') {
			self::$_OS = OS_TYPE_WINDOWS;
		}
		//检查epoll
		if (class_exists('\EventBase')) {
			self::$EPOLL = true;
		} else {
			self::windowshow('Event或LibEvent组件不存在。');
		}
		//检查pcntl
		if (function_exists('pcntl_signal')) {
			self::$PCNTL = true;
		} else {
			self::windowshow('pcntl组件不存在。');
		}
	}
	/**
	 * 
	 * @param string $host ip/域名
	 * @param string $port 端口
	 * @param string $tcpip tcp/udp
	 * @param string $ssl 是否开启ssl
	 * 
	 * @return
	 */
	public static function starts($host, $port, $tcpip = '', $ssl = false, $ismaster = false)
	{
		set_time_limit(0);
		//检查系统环境
		self::checkSapiEnv();
		//设置错误输出
		set_error_handler(function ($code, $msg, $file, $line) {
			/* Worker::safeEcho("$msg in file $file on line $line\n");*/
			self::error("错误代码：$code--$msg in file $file on line $line\n");
		});
		//检测是否已经监听；
		//数据库用户表下线		
		// self::initcode(); //初始化密码
		// self::daemonize(); //守护进程
		self::installSignal(); //注册信号处理
		$type = $tcpip ? $tcpip : self::$type;
		self::$type = $type;
		self::$ip = $host;
		self::$port = $port;
		self::$redis = SocketCache::getCache();
		$f = preg_match("/(\d{1,3}\.){3}(\d{1,3})/", $host, $m);

		if ($f) {
			//这里是ip
			$host = '0.0.0.0';
		} else {
			//这里是域名		
		}
		if ($ssl) {
			$ssl = self::ssl();
		}

		if ($type == 'tcp') {
			//开启tcp模式

			$server = new Tcp($host, $port, $ssl, $ismaster);
		}
		if ($type == 'udp') {
			//开启udp模式
			$server = new Udp($host, $port, $ssl, $ismaster);
		}
		//接收数据
		self::$server = $server;
		if ($ismaster) {
			//主master检测slave并且尝试让从服务器让从服务器重新链接主服务器
			// self::master();
		} else {
			//主动链接主msater
			// self::slave();
		}

		//time
		$server->recv();
		d('正常退出了');
	}
	/**
	 * 
	 */
	public static function master()
	{
		//要用定时器，在接受消息确认之后发消息给slave，slave收到消息主动链接master。
		//尝试从连slave
		//slave失败清空slave 服务器消息，用户
		new master();
	}
	public static function slave()
	{
		self::$slave = new slave();
		if (self::$slave->init())
			self::$slave->conn_master();
	}
	//ssl
	public static  function  ssl()
	{

		/*$dn = array(
			"countryName" => "UK",
			"stateOrProvinceName" => "Somerset",
			"localityName" => "Glastonbury",
			"organizationName" => "The Brain Room Limited",
			"organizationalUnitName" => "PHP Documentation Team",
			"commonName" => "chat.com",
			"emailAddress" => "admin@chat.com"
		);		
		$privkey = openssl_pkey_new();
		$cert    = openssl_csr_new($dn, $privkey);
		
		$cert    = openssl_csr_sign($cert, null, $privkey, 365);

		// Generate PEM file
		# Optionally change the passphrase from 'comet' to whatever you want, or leave it empty for no passphrase
		$pem_passphrase = 'comet';
		$pem = array();
		openssl_x509_export($cert, $pem[0]);
		openssl_pkey_export($privkey, $pem[1], $pem_passphrase);
		$pem = implode($pem);

		// Save PEM file
		$pemfile = './server.pem';
		file_put_contents($pemfile, $pem);*/


		$arr = ['ssl' => [

			'local_cert' => ROOT . "ssl/server.crt",
			'local_pl' => ROOT . "ssl/server.key",

			'allow_self_signed' => true,
			'verify_peer' => false,
			'verify_peer_name' => false,
			//				'ciphers'=>'TLS_AES_256_GCM_SHA384:TLS_CHACHA20_POLY1305_SHA256:TLS_AES_128_GCM_SHA256',
			'ciphers' => 'HIGH:TLSv1.2:TLSv1.1:TLSv1.0:!SSLv3:!SSLv2',

		]];
		$context = stream_context_create($arr);
		return $context;
	}
	//liunx守护进程
	protected static function daemonize()
	{
		if (static::$_OS !== OS_TYPE_LINUX) {
			return;
		}
		umask(0);
		$pid = pcntl_fork();
		if (-1 === $pid) {
			throw new \Exception('fork fail');
		} elseif ($pid > 0) {
			exit(0);
		}
		if (-1 === posix_setsid()) {
			throw new \Exception("setsid fail");
		}
		// Fork again avoid SVR4 system regain the control of terminal.
		$pid = pcntl_fork();
		if (-1 === $pid) {
			throw new \Exception("fork fail");
		} elseif (0 !== $pid) {
			exit(0);
		}
	}
	//注册信号处理
	protected static function installSignal()
	{
		if (static::$_OS !== OS_TYPE_LINUX) {
			return;
		}

		// stop
		pcntl_signal(SIGINT, array('\ng169\lib\Socket', 'closeserver'), false);
		// graceful stop
		pcntl_signal(SIGTERM, array('\ng169\lib\Socket', 'closeserver'), false);
		// reload
		pcntl_signal(SIGUSR1, array('\ng169\lib\Socket', 'closeserver'), false);
		// graceful reload
		pcntl_signal(SIGQUIT, array('\ng169\lib\Socket', 'closeserver'), false);
		// status
		pcntl_signal(SIGUSR2, array('\ng169\lib\Socket', 'closeserver'), false);
		// connection status
		pcntl_signal(SIGIO, array('\ng169\lib\Socket', 'closeserver'), false);
		// ignore
		pcntl_signal(SIGPIPE, SIG_IGN, false);
	}
	/**
	 * 数据库记录ip port
	 * @param undefined $host
	 * @param undefined $port
	 * 
	 * @return
	 */
	public static function adddblog($flag, $is_master = false)
	{
		$where = ['ip' => self::$ip, 'port' => self::$port];
		$isin = T('sockserver')->set_where($where)->get_one($where);
		if ($is_master) {
			self::$is_master = $is_master;
		}

		if ($isin) {

			$info = ['starttime' => time(), 'flag' => $flag ? 1 : 0, 'ismaster' => $is_master ? 1 : 0];
			T('sockserver')->update($info, $where);
			return $isin['sid'];
		} else {
			if ($is_master) {
				$info = ['ip' => self::$ip, 'port' => self::$port, 'starttime' => time(), 'flag' => 1, 'conns' => 0, 'ismaster' => 1];
				$sipid = T('sockserver')->add($info);
			} else {
				$info = ['ip' => self::$ip, 'port' => self::$port, 'starttime' => time(), 'flag' => 1, 'conns' => 0, 'ismaster' => 0];
				$sipid = T('sockserver')->add($info);
				$info['sid'] = $sipid;
				self::$is_master = $info;
			}
		}
		return true;
	}
	/**
	 * 显示在控制台
	 * @param undefined $msg 消息
	 * 
	 * @return
	 */
	public static function windowshow($msg)
	{
		echo "\n" . $msg;
	}
	/**
	 * 踢用户下线
	 * 
	 * @return
	 */
	public static function kick($client)
	{
		$socket = self::$sockets[$client]['resource'];
		self::disconnect($socket);
		unset(self::$sockets[$client]);
	}
	/**
	 * 关闭服务器
	 * 
	 * @return
	 */
	public static function closeserver()
	{
		if (!isset(self::$master)) return false;
		socket_close(self::$master);
		self::$stop = true;
		unset(self::$sockets);
		//		unset($this);
		self::adddblog(S_CLOSE);
		self::reset_client(); //清空当前私有连接客户端
	}


	/**
	 * 增减连接数
	 * @param undefined $bool 0减，1加
	 * 
	 * @return
	 */
	protected static function conns($bool)
	{
		return false;
		if (!self::$sid) return false;
		if ($bool) {
			self::$coons += 1;
		} else {
			self::$coons -= 1;
		}

		return T('sockserver')->update(['conns' => self::$coons], ['sid' => self::$sid]);
	}
	public static function getindex($socket)
	{
		$sip = self::$sid;

		if (self::$type == 'udp') {
			$index = $sip . "SIP" . ($socket);
		} else {
			$index = $sip . "SIP" . intval($socket);
		}
		return $index;
	}
	public static function getsock($clientid)
	{
		if (isset(self::$sockets[$clientid])) {
			return  self::$sockets[$clientid]['resource'];
		}
		return false;;
	}
	public static function gettk($clientid)
	{
		if (!$clientid) return false;
		if (isset(self::$sockets[$clientid])) {
			return  self::$sockets[$clientid]['token'];
		}
		return false;
	}
	public static function getuidtk($uid)
	{
		$cachetk = Socket::$redis->get(self::REDIS_PRE_U . $uid);
		if (!$cachetk) {

			$usertk = sockbase::gettoken($uid);
			if ($usertk) {
				Socket::$redis->set(self::REDIS_PRE_U . $uid, $usertk, 360);
				return $usertk;
			} else {
				return false;
			}
		}
		return $cachetk;
	}


	//检测用户是否刷新
	public static function reflash($uid)
	{
		$time = (int)self::$userlogin[$uid];

		$go_time = time() - $time;

		self::$userlogin[$uid] = time();

		if ($go_time > self::CHECK_REFLASH_TIME) return true;
		return false;
	}
	/**
	 * 客户端关闭连接
	 *
	 * @param $socket
	 *
	 * @return array
	 */
	public static function disconnect($socket)
	{

		if (self::$EPOLL) {
			self::$event_base->del($socket);	//epoll退出
		}
		$info = self::$sockets[self::getindex($socket)];
		if ($info['type']) {
			//这里是服务器端断开
			if (self::$is_master) {
				//从服务器断开
				$sid = $info['uname'];
				Socket::windowshow('id' . $sid . ';slave服务端断开');
				T('sockserver')->update(['flag' => 0], ['sid' => $sid]);
				unset(Socket::$masters[$sid]);
			} else {
				//主服务器断开
				Socket::windowshow('master服务端断开');
				self::$slave->disconnect(); //删除主服务器记录
				unset(self::$slave); //删除主服务器
			}
			//断开该server下所有链接
			T('sock_client')->set_where(['sid' => $sid])->del();
		} else {
			//这里是客户端链接断开
			if (self::$sockets[self::getindex($socket)]['check']) {
				T('sock_client')->update(array('online'   => 0, 'handshake' => 0), array('clientid' => self::getindex($socket)));
			}
		}

		unset(self::$sockets[self::getindex($socket)]);
		self::conns(false);
		if (self::$disMsg) {
			call_user_func(self::$disMsg, $socket);
		}
		/*self::broadcast($msg);*/
		return true;
	}



	/**
	 * 解析数据
	 *
	 * @param $buffer
	 *
	 * @return bool|string
	 */
	public  static function parse($buffer)
	{
		$decoded = '';
		$len     = ord($buffer[1]) & 127;
		if ($len === 126) {
			$masks = substr($buffer, 4, 4);
			$data  = substr($buffer, 8);
		} else
		if ($len === 127) {
			$masks = substr($buffer, 10, 4);
			$data  = substr($buffer, 14);
		} else {
			$masks = substr($buffer, 2, 4);
			$data  = substr($buffer, 6);
		}
		for ($index = 0; $index < strlen($data); $index++) {
			$decoded .= $data[$index] ^ $masks[$index % 4];
		}
		return $decoded;
	}

	/**
	 * 将普通信息组装成websocket数据帧
	 *
	 * @param $msg
	 *
	 * @return string
	 */
	protected  static function build($msg)
	{
		// $frame = [];
		// $frame[0] = '81';
		// $len = strlen($msg);
		// if ($len < 126) {
		// 	$frame[1] = $len < 16 ? '0' . dechex($len) : dechex($len);
		// } else
		// if ($len < 65025) {
		// 	$s = dechex($len);
		// 	$frame[1] = '7e' . str_repeat('0', 4 - strlen($s)) . $s;
		// } else {
		// 	$s = dechex($len);
		// 	$frame[1] = '7f' . str_repeat('0', 16 - strlen($s)) . $s;
		// }

		// $data = '';
		// $l    = strlen($msg);
		// for ($i = 0; $i < $l; $i++) {
		// 	$data .= dechex(ord($msg{$i}));
		// }
		// $frame[2] = $data;

		// $data = implode('', $frame);

		// return pack("H*", $data);
	}
	protected  static function checksystem($code)
	{


		return ($code == self::$syscode);
		/* $bool;*/
	}
	protected static  function checkadmin($code)
	{

		return ($code == self::$admincode);
	}
	protected  static function initcode()
	{
		// $bool = T('sock_type')->get_one(array('type'    => 3));
		// self::$syscode = $bool['password'];
		// $bool = T('sock_type')->get_one(array('type'    => 2));
		// self::$admincode = $bool['password'];
	}
	/**
	 * 发送消息到对应的用户客户端
	 * 拼装信息
	 *
	 * @param $socket
	 * @param $recv_msg
	 *          [
	 *          'stype'=>''2管理，3系统，1或则空是用户
	 *       'code'=>''管理效验码，3系统效验码，空是用户
	 *          'type'=>user/login  //用户登入信息
	 *          'data'=>content   //数据base64压缩
	 *          ]
	 *
	 * @return string
	 */
	public static function socksend($clientid, $msg)
	{
		// Socket::$server->send($clientid, $msg);
		Socket::$server->send($clientid, $msg);
	}
	//发送数据;无任何编码,元数据发送
	public static function senddecodeMsg($clientsk, $msg)
	{

		$msg = self::buildMsg($msg);
		Socket::$server->SkSend($clientsk, $msg);
	}
	//把要发送的数据编码加长度,来避免粘包,分包
	public static function buildMsg($msg)
	{
		if (!is_string($msg)) {
			$msg = json_encode($msg);
		}
		$msg = str_pad(strlen($msg), 5, "0", STR_PAD_LEFT) . $msg;
		return $msg;
	}
	//把接受的数据包进行按序拆包,防止粘包,分包
	public static function decodemsg()
	{
	}
	protected static  function dealMsg($socket, $recv_msg)
	{

		if (self::$needresolving) {
			$recv_msg = (json_decode($recv_msg, 1));
		}
		if (self::$call) {

			call_user_func(self::$call, $socket, $recv_msg);
			return 1;
		}

		$type     = isset($recv_msg['stype']) ? $recv_msg['stype'] : 1;
		$code     = isset($recv_msg['code']) ? $recv_msg['code'] : '';
		$recv_msg['fun']     = isset($recv_msg['fun']) ? $recv_msg['fun'] : 'run';
		/*$code     = $recv_msg['code'];*/
		if (!isset($recv_msg['action']) && isset($recv_msg['control'])) {
			$recv_msg['action'] = $recv_msg['control'];
		}
		//无参数退出；		
		if (!isset($recv_msg['action'])) {
			// d('tuichu');
			return false;
		}
		switch ($type) {
			case 2:
				if (self::checkadmin($code)) {
					self::sockdoing($socket, 'admin', $recv_msg);
				}
			case 3:

				if (self::checksystem($code)) {

					self::systemdeal($socket, $recv_msg);
				}
				break;
			default:
				self::sockdoing($socket, 'user', $recv_msg);
		}
		return 1;
	}




	public static function yscode($data)
	{

		return $data;
	}
	public static function unyscode($data)
	{
		if (is_array($data)) {
			return $data;
		}
		$ysdata = json_decode($data);
		return $ysdata;
	}
	/**
	 * 后端发消息到服务器，由服务器sock入口接收并且处理
	 * @param undefined $ip
	 * @param undefined $port
	 * @param undefined $data
	 * 
	 * @return
	 */
	//data包含action 以及执行所需要的所有数据所组成的数组
	public static function phpsend($ip, $port, $data)
	{
		// $systemcode = T('sock_type')->set_field(array('password'))->get_one(array('type' => 3));
		// if (!$systemcode) {
		// 	YLog::txt('system结构密码丢失');
		// 	return false;
		// }
		$send['stype'] = 3;
		// $send['code'] = $systemcode['password'];
		$send['data'] = self::yscode(json_encode($data));
		$msg    = json_encode($send);
		$client = stream_socket_client("tcp://$ip:$port", $errno, $errmsg, 1);
		if ($client) {
			fwrite($client, $msg . "\n");
			fclose($client);
			return true;
		}
		return false;
	}
	public static function phpsend_live($sip, $data)
	{

		$send['stype'] = 3;
		$send['code'] = self::$syscode;
		$send['data'] = self::yscode(json_encode($data));
		$msg    = json_encode($send);
		/*$job=Job::getInstance();
		$job->add('5',function(){
		d('5秒之后的事情');
		});*/
		if (isset(self::$sysconn[$sip]) && is_resource(self::$sysconn[$sip])) {
			fwrite(self::$sysconn[$sip], $msg . "\n");
			return true;
		}
		$server = T('sockserver')->get_one(array('sid' => $sip, 'flag' => 1));
		$ip = $server['ip'];
		$port = $server['port'];
		$type = self::$type;
		$client = stream_socket_client("$type://$ip:$port", $errno, $errmsg, 1);
		if ($client) {
			self::$sysconn[$sip] = $client;

			fwrite($client, $msg . "\n");
			/*fclose($client);*/
			return true;
		}
		return false;
	}

	//系统发来信息
	protected static  function systemdeal($socket, $data)
	{

		$index = self::getindex($socket);
		// $data = json_decode($data, 1);
		$bool = true;
		if (!$data) return false;
		if (!isset($data['stype']) || $data['stype'] != 3) return false;
		// $code   = $data['code'];

		$recv   = self::unyscode($data['data']);



		if (!isset($data['action'])) return false;
		$action = $data['action'];
		$fun = isset($data['fun']) ? $data['fun'] : 'run';
		$type   = 'system';
		if ($bool) {
			$bool2 = self::get_typea_ction_file($type, $action);
			if ($bool2) {
				$classname = "ng169\\sock\\{$type}\\" . $action;
				if (!class_exists($classname)) {
					self::error($type . '下执行文件类错误');
					return true;
				}
				$class     = new $classname($socket, $recv);
				$class->init($socket, $data);
				if (method_exists($class, $fun) &&  $fun != '') {
					$class->$fun($recv);
					return true;
				} elseif (method_exists($class, $fun)) {
					$class->run($recv);
					return true;
				} else {
					self::error($type . '下' . $action . '执行文件错误');
					self::disconnect($socket);
					return false;
				}
			}
		}
		return true;
	}
	//管理员发来信息



	/**
	 * 广播消息
	 *
	 * @param $data
	 */
	public static function broadcast($data)
	{
		/*if(!is_string($data)){
		$data=serialize($data);
		}*/
		foreach (self::$sockets as $k => $socket) {
			if ($k != '-1') {
				$d['action'] = 'login';
				$d['data'] = $data;
				$d2['data'] = $d;
				self::socksend($socket['resource'], $d2);
			}
		}
	}
	/**
	 * 记录错误信息
	 *
	 * @param array $info
	 */
	protected static function error($info)
	{

		if (self::$debug) {
			self::windowshow($info);
		} else {
			YLog::txt($info, LOG . 'websocket_error.log');
		}
	}
	protected static function get_typea_ction_file($type, $action)
	{
		$sockfile = SOURCE . "/sock/{$type}Sock.php";
		im($sockfile);
		$file     = SOURCE . "sock/{$type}/$action.php";

		if (file_exists($file)) {
			/* require_once ($file);*/
			im($file);
			return true;
		} else {
			self::error($type . '下' . $action . '执行文件' . $file . '不存在');
			return false;
			/*  d('API '.$apiname. ' IS NOT EXIST',1);*/
		}
		return true;
	}
	protected static function sockdoing($sock, $type, $recv)
	{

		$action =	$recv['action'];
		$fun = $recv['fun'];
		$data = $recv['data'];
		$bool = self::get_typea_ction_file($type, $action);

		if ($bool) {
			$classname = "ng169\\sock\\{$type}\\" . $action;
			$class     = new $classname($sock, $recv);

			$class->init($sock, $recv);
			if (!class_exists($classname)) {
				self::error($type . '下执行文件类错误');
				return true;
			}
			if (method_exists($class, $fun) &&  $fun != '') {
				$class->$fun($data);
				return true;
			} elseif (method_exists($class, 'run')) {

				$class->run($data);
				return true;
			} else {
				self::error($type . '下' . $action . '执行文件错误');
				return false;
			}
			unset($control);
		}
	}
	public static $bufs;
	public static $bufsnoleng = [];

	public static function getbuf($socket)
	{
		$buffer = stream_socket_recvfrom($socket,  Tcp::$recvlength, 0);
		// 62{"type":"1","data":"{\"pwd\":\"123456\"}","msg":"注册sqlServer"}
		if (!$buffer) {
			//断开连接
			//self::disconnect($socket);
			return "";
		} //断开的消息要直接推给上层
		self::$bufsnoleng[$socket] = $buffer;
		if(self::$isLengthMode){
			self::$bufs[$socket] .= ($buffer); 
		}else{
			self::$bufs[$socket] = ($buffer); 
		}
	
		//粘包分包处理,
		// self::$bufs[$socket].=str_replace('\\','', $buffer);
		self::bufdeal($socket);
	}
	public static function gettmpdata($socket)
	{
		if (isset(self::$bufsnoleng[$socket])) return self::$bufsnoleng[$socket];
		return "";
	}
	public static function getolddata($socket)
	{
		return self::gettmpdata($socket);
	}
	public static function bufdeal($socket)
	{
		if (self::$isLengthMode) {


			$msglen = intval(substr(self::$bufs[$socket], 0, 5));

			if (($msglen + 5) > strlen(self::$bufs[$socket])) {
				//分包,等待下一次接受

				return "";
			}
			if (($msglen + 5) == strlen(self::$bufs[$socket])) {
				//大小刚好
				$msg = substr(self::$bufs[$socket], 5, $msglen);
				self::indata($socket, $msg);
				self::$bufs[$socket] = "";
				return "";
			}
			if (($msglen + 5) < strlen(self::$bufs[$socket])) {
				//粘包,多次数据
				// d("粘包".strlen(self::$bufs[$socket]));
				$msg = substr(self::$bufs[$socket], 5, $msglen);
				self::indata($socket, $msg);
				self::$bufs[$socket] = substr(self::$bufs[$socket], 5 + $msglen);;
				self::bufdeal($socket); //再次取数据
				return "";
			}
		} else {
			$msg = self::$bufs[$socket];
			self::indata($socket, $msg);
			self::$bufs[$socket] = "";
		}
	}
	public static function indata($socket, $msg)
	{
		$bytes = strlen($msg);
		self::info("收到消息" . $msg);
		//长度限制10000个字符
		if ($bytes < 2) {
			//心跳必须大于三个字节,空数据可能是客户端断开的消息

			// $recv_msg = self::disconnect($socket);
		} else {

			if (self::$onMsg) {
				call_user_func(self::$onMsg, $socket, $msg);
			}


			$hand = self::$sockets[self::getindex($socket)]['handshake'];

			if ($hand == 0) {
				$bool = !self::$server->handShake($socket, $msg);
				self::conns(true);
				//如果当前是master
				if ($bool && self::$is_master) {
					// self::systemdeal($socket, $buffer);
					$data = $msg;
				} else {
					return;
				}
			} elseif ($hand == 2) {
				// d($buffer);
				// self::systemdeal($socket, $buffer);
				$data = $msg;
			} elseif ($hand == 1) {
				$data = self::parse($msg);
				// if (self::dealMsg($socket, $recv_msg)) {
				// 	self::conns(true);
				// }
			}
			//统一走这里
			self::dealMsg($socket, $data);
		}
	}
	/**
	 * 监听已经变化的套子节，回去桃子姐数据
	 */
	public static function  listen($socket)
	{

		if ($socket == self::$master) {

			//是当前接口桃子姐表示接受到链接请求。
			$client = stream_socket_accept($socket, 0, $remote_address);
			self::info("收到连接" . $socket);
			// 创建,绑定,监听后accept函数将会接受socket要来的连接,一旦有一个连接成功,将会返回一个新的socket资源用以交互,如果是一个多个连接的队列,只会处理第一个,如果没有连接的话,进程将会被阻塞,直到连接上.如果用set_socket_blocking或socket_set_noblock()设置了阻塞,会返回false;返回资源后,将会持续等待连接。
			if (false === $client) {
				self::error([
					'err_accept',
					$err_code = socket_last_error(),
					socket_strerror($err_code)
				]);
			} else {
				self::$server->connect($client);
			}
		} else {

			// 如果可读的是其他已连接socket,则读取其数据,并处理应答逻辑
			// $buffer = stream_socket_recvfrom($socket,  Tcp::$recvlength, 0);
			self::getbuf($socket);

			//sock消息"`"+数据长度5位+数据体;
			//这里要做粘包分包的缓存机制

		}
	}
}
