<?php


namespace ng169\lib;

use ng169\Y;
use ng169\lib\Log as YLog;

use sockbase;

checktop();


class sqlMsg
{
	public $type = 2;
	// public $sql ;
	// public $sqltype = 0; //类型0返回状态,1返回一行,2返回list
	public $data;
	public function __construct($sqltype, $sql)
	{
		$this->data = json_encode(['sqltype' => $sqltype, 'sql' => $sql]);
		// $this->sqltype = $sqltype;
		// $this->sql = $sql;
	}
}
class SocketClient extends Y
{


	public  $sockets = null;

	public  $debug = false;
	public  $ip = "127.0.0.1";
	public  $port = false;
	public  $sendqueue = []; //发送消息队列

	public  function info($msg)
	{
		if (self::$debug) {
			d($msg);
		}
	}
	public function __construct($ip, $port)
	{

		$this->ip = $ip;
		$this->port = $port;
		$this->conn();
	}
	//连接
	public function conn()
	{
		// STREAM_CLIENT_CONNECT、STREAM_CLIENT_ASYNC_CONNECT、STREAM_CLIENT_PERSISTENT，分别是：默认的同步、异步、持久连接
		if (false === ($this->sockets = stream_socket_client("tcp://$this->ip:$this->port", $error_code, $error_message, 3))) {
			d($error_message);
		}
	}
	public function send($data)
	{
		if (!$this->sockets) {
			//如果断开了;尝试连接
			$this->conn();
		}
		array_push($this->sendqueue, $data);
		$this->loopsend();
		// fwrite($this->sockets, $msg . "\n");
	}
	public function getone($sql)
	{
		$msg = new sqlMsg(1, $sql);
		$this->send($msg);
	}
	public function query($sql)
	{
		$msg = new sqlMsg(2, $sql);
		$this->send($msg);
	}
	public function exec($sql)
	{
		$msg = new sqlMsg(0, $sql);
		$this->send($msg);
	}
	public function insert($sql)
	{
		$msg = new sqlMsg(0, $sql);
		$this->send($msg);
	}
	private function loopsend()
	{
		for ($i = 0; $i < count($this->sendqueue); $i++) {
			# code...
			try {
				//code...
				$bool = $this->_senddata($i);

				if ($bool) {
					unset($this->sendqueue[$i]);
				} else {
					$this->sockets = null;
					$this->conn();
					break;
				}
			} catch (\Throwable $th) {
				d($th);
				//throw $th;
			}
		}
	}
	private function _senddata($index)
	{
		$msg = $this->sendqueue[$index];
		$msg=Socket::buildMsg($msg);
		d($msg);
		$bool = fwrite($this->sockets, $msg . "\n");
		return $bool;
	}
}
