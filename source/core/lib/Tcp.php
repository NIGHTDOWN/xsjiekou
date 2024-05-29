<?php


namespace ng169\lib;

use ng169\Y;
use ng169\lib\Log as YLog;
use ng169\lib\Socket;
use ng169\lib\Job;


use ng169\lib\SocketCache;

checktop();




class Tcp extends Socket
{
	public static $recvlength = 4048; //buffer大小4k
	public function __construct($host, $port, $sslcontent, $ismaster = false)
	{
		if ($sslcontent) {

			self::$master = stream_socket_server("tcp://{$host}:{$port}", $errno, $errmsg, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN, $sslcontent);
			stream_socket_enable_crypto(self::$master, false);
		} else {
			self::$master = stream_socket_server("tcp://{$host}:{$port}", $errno, $errmsg, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
		}
		if (!self::$master) {
			self::error("错误代码：$errno--$errmsg \n");
		}
		self::$sockets["-1"] = ['resource' => self::$master];
		$sid = self::adddblog(S_OPEN, $ismaster);
		self::$sid = $sid;
		self::reset_client();
		self::windowshow("\nServices ID：" . self::$sid);
		self::windowshow("\n开启TCP监听：$host:$port");
		return $this;
	}
	public function recv()
	{
		//这里在对消息处理时候开启子线程
		if (self::$EPOLL) {
			$this->epollmodel();
		} else {
			while (!self::$stop) {
				$this->selectmodel();
			}
		}
	}
	private function selectmodel()
	{

		$sockets = array_column(self::$sockets, 'resource');
		stream_set_blocking(self::$master, false);
		$read = $sockets;
		$read_num = stream_select($read, $write, $except, NULL);
		// select作为监视函数,参数分别是(监视可读,可写,异常,超时时间),返回可操作数目,出错时返回false;
		if (false === $read_num) {
			self::error([
				'error_select',
				$err_code = socket_last_error(),
				socket_strerror($err_code)
			]);
			return;
		}
		foreach ($read as $socket) {
			Socket::listen($socket);
		
		}
	}
	private function epollmodel()
	{
		$fd = &self::$master;
		//非阻塞模式	
		stream_set_blocking(self::$master, false);
		$epoll = \ng169\lib\Epoll::getInstance();
		self::$event_base = $epoll;
		$epoll->add($fd, \ng169\lib\Epoll::READ, function ($fd) {
			$conn = stream_socket_accept($fd);
			\ng169\lib\Tcp::_epoll_fun($conn);
		});

		$epoll->run();
	}
	public static function _epoll_fun($socket)
	{
		$index = self::getindex($socket);
		if (!isset(self::$sockets[$index])) {
			//看看连接有没有被记录，没记录就建立链接，有记录就处理数
			self::connect($socket); //创建新连接	
			self::conns(true);
			self::$event_base->add($socket, \ng169\lib\Epoll::READ, function ($conn) {
				\ng169\lib\Tcp::_epoll_fun_con($conn);
			});
		}
	}
	public  static function _epoll_fun_con($socket)
	{
		Socket::listen($socket);
	
	}
	/**
	 * 将socket添加到已连接列表,但握手状态留空;
	 *
	 * @param $socket
	 */
	public static function connect($socket)
	{
		//		socket_getpeername($socket, $ip, $port);
		$data = stream_socket_get_name($socket, true);
		
		list($ip, $port) = explode(':', $data);
		$index = self::getindex($socket);
		
		$socket_info = [
			'resource' => $socket,
			'uname' => '',
			'token' => '',
			// 'handshake' => false,
			'handshake' => self::handShake($socket),	//tcp websock需要握手；最好是加个协议判断
			'ip' => $ip,
			'port' => $port,
			'type' => 0,
			'check' => 0,
			'clientid' => '',
			'online' => 1,
			'addtime' => time(),
			'sid' => self::$sid,
		];
		$insert = $socket_info;
		$insert['resource'] = serialize($socket);
		$socket_info['clientid'] = $index;
		self::$sockets[$index] = $socket_info;
		// self::handShake($socket);
	}
	/**
	 * 用公共握手算法握手
	 *
	 * @param $socket
	 * @param $buffer
	 *
	 * @return bool
	 */
	public static function handShake($socket)
	{
		// $buffer = stream_socket_recvfrom($socket,  self::$recvlength, 0);
		$buffer =Socket::gettmpdata($socket);
		$bytes = strlen($buffer);
		if(!$bytes)return false;
		// 获取到客户端的升级密匙
		$key = '';
		if (\preg_match("/Sec-WebSocket-Key: *(.*?)\r\n/i", $buffer, $match)) {
			$key = $match[1];
		} else {
			// $connection->close("HTTP/1.1 200 WebSocket\r\nServer: workerman/" . Worker::VERSION . "\r\n\r\n<div style=\"text-align:center\"><h1>WebSocket</h1><hr>workerman/" . Worker::VERSION . "</div>",
			// 	true);
			return 0;
		}
		if (!$key) return false;
		// 生成升级密匙,并拼接websocket升级头  
		$upgrade_key = base64_encode(sha1($key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true)); // 升级key的算法
		$upgrade_message = "HTTP/1.1 101 Switching Protocols\r\n";
		$upgrade_message .= "Upgrade: websocket\r\n";
		$upgrade_message .= "Sec-WebSocket-Version: 13\r\n";
		$upgrade_message .= "Connection: Upgrade\r\n";
		$upgrade_message .= "Sec-WebSocket-Accept:" . $upgrade_key . "\r\n\r\n";
		stream_socket_sendto($socket, $upgrade_message, 0);
		//		T('sock_client')->update(array('handshake'=>1),array('clientid'=>self::getindex($socket)));
		self::$sockets[self::getindex($socket)]['handshake'] = 1;
		//		socket_getpeername($socket, $ip, $port);
		return true;
	}
	/**
	 * 发送
	 * 
	 * @return
	 */
	public function send($clientid, $msg)
	{
		$socket = self::getsock($clientid);
		
		$this->sendsock($socket, $msg);
	}
	public function sendsock($socket, $msg)
	{
		if (is_string($msg)) {
			$msg = self::build($msg);
		} else {
			$msg = json_encode($msg);
			$msg = self::build($msg);
		}
		if ($socket) stream_socket_sendto($socket, $msg, 0);
	}
	public function SkSend($socket, $msg)
	{
		
		if ($socket) stream_socket_sendto($socket, $msg, 0);
	}
}
