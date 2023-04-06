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
	public $readlength = 10241024;
	public  $sendqueue = []; //发送消息队列
	private $ret;
	public static $obj;
	public $skCacheIndex="skcacheindex:";
	private  $loopwait=0;
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
		self::$obj=$this;
	}
	//连接
	public function conn()
	{
		if (Socket::$isServer) {
			return;
		}
		// STREAM_CLIENT_CONNECT、STREAM_CLIENT_ASYNC_CONNECT、STREAM_CLIENT_PERSISTENT，分别是：默认的同步、异步、持久连接
		if (false === ($this->sockets = stream_socket_client("tcp://$this->ip:$this->port", $error_code, $error_message, 30))) {
			d("$this->ip:$this->port".$error_message);
		}
	}
	
	public function send($data)
	{
		//这里搞一个缓存30秒不要对同一个缓存拉起请求
		
		if (Socket::$isServer) {
			return;
		}
		if (!$this->sockets) {
			//如果断开了;尝试连接
			$this->conn();
		}
		if(!$data)return;
		// array_push($this->sendqueue, $data);
		$this->_send($data);
		// fwrite($this->sockets, $msg . "\n");
	}
	public function getone($sql)
	{
		if(!$sql)return;
		$msg = new sqlMsg(1, $sql);
		$time=G_DAY;
		if(!strstr($sql,"where")){
			$key="sqlhash".md5($sql);
			list($bool,$data)=Y::$cache->get($key);
			if($data){
				return $data;
			}
		}else{
			$key=$this->skCacheIndex.md5($sql);
			list($bool,$data)=Y::$cache->get($key);
			if($data){
				$time=2;
				return $data;
			}
		}
		$this->send($msg);
		if(isset($this->ret[0]))$ret= $this->ret[0];
		$ret=$this->ret;
		Y::$cache->set($key,$ret,$time);
		return $ret;
	}
	public function query($sql)
	{
		if(!$sql)return;
		$msg = new sqlMsg(2, $sql);
		$time=G_DAY;
		if(!strstr($sql,"where")){
			$key="sqlhash".md5($sql);
			list($bool,$data)=Y::$cache->get($key);
			if($data){
				return $data;
			}
		
		}else{
			$key=$this->skCacheIndex.md5($sql);
			list($bool,$data)=Y::$cache->get($key);
			if($data){
				$time=2;
				return $data;
			}
		}
		
		//10秒内的缓存
		
		$this->send($msg);
		Y::$cache->set($key,$this->ret,$time);
		return $this->ret;
	}
	public function exec($sql)
	{
		$msg = new sqlMsg(3, $sql);
		$this->send($msg);
		return $this->ret;
	}
	public function insert($sql)
	{
		if(!$sql)return;
		$msg = new sqlMsg(4, $sql);
		$this->send($msg);
		return $this->ret;
	}
	private  $bufdata;
	private  function bufdeal($buffer){		
		$buffer=$this->bufdata.$buffer;
		$msglen=intval(substr($buffer,0,5));	
		if(($msglen+5)>strlen($buffer)){
			//分包,等待下一次接受	
			return "";
		}
		if(($msglen+5)==strlen($buffer)){
			//大小刚好
			$msg=substr($buffer,5,$msglen);
			$this->indata($msg);
			$this->bufdata="";
			return "";
		}
		if(($msglen+5)<strlen($buffer)){
			//粘包,多次数据
			// d("粘包".strlen(self::$bufs[$socket]));
			$msg=substr($buffer,5,$msglen);
			$this->indata($msg);
			$this->bufdata=substr($buffer,5+$msglen);;
			// $this->bufdeal($socket);//再次取数据
			return "";
		}
	} 
	//发送
private function _send($data){
	if(!$data)return;
	try {
		$len = 0;
			$buf = '';
		$msg = Socket::buildMsg($data);
		$bool = fwrite($this->sockets, $msg);
		if ($bool) {
			while(!feof($this->sockets)) {
				$buf .= fgets($this->sockets, $this->readlength);
				// 还不知道数据长度，计算这个包的数据长度
				if (!$len) {
					// your_func_of_get_len里还要判断下目前收到的数据长度是否足够计算出整个包的长度
					$len = intval(substr($buf,0,5))+5;
				}
				// 判断数据是否全部得到，得到就跳出
				if (strlen($buf) >= $len) {
					// 实际上最好要截取下，因为tcp流式的，可能是多个包粘在一起。如果是多个包粘在一起，还要记得保存下个包的部分数据，避免数据丢失，这里省略了
					// $buf = substr($buf, 0, $len);
					// fclose($this->sockets);
					break;
				}
			}
			//完整一个数据包处理
			$this->bufdeal($buf);
			// $response = false;
			// //阻塞,等待接受消息在执行下一条语句
			// // while (!$response) {
			// 	// $this->loopwait++;
			// 	// if($this->loopwait>=5){
			// 	// 	return;
			// 	// }
			// 	$response	=  fread($this->sockets, $this->readlength);
			// 	d($response);
			// 	if ($response) {
			// 		$this->bufdeal($response);
			// 	}
				// break;
			// }
		}
		 else {
			// $this->__send($data);
			// $this->conn();
		}
		}
	 catch (\Throwable $th) {
		d($th);
	}
}

	private function loopsend()
	{
		$this->loopwait=0;
		for ($i = 0; $i < count($this->sendqueue); $i++) {
			try {
				$bool = $this->_senddata($i);
				if ($bool) {
				
					$response = false;
					//阻塞,等待接受消息在执行下一条语句
					while (!$response) {
						$this->loopwait++;
						if($this->loopwait>=5){
							return;
						}
						$response	=  fread($this->sockets, $this->readlength);
						if ($response) {
							$this->bufdeal($response);
						}
						break;
					}
				} else {
					$this->sockets = null;
					$this->conn();
					break;
				}
			} catch (\Throwable $th) {
				d($th);
			}
		}
	}

	private function _senddata($index)
	{
		$msg = $this->sendqueue[$index];
		if(!$msg ){
			unset($this->sendqueue[$index] );
			return false;
		}
		unset($this->sendqueue[$index]);
		// d($msg);
		$msg = Socket::buildMsg($msg);
		$bool = fwrite($this->sockets, $msg);
		return $bool;
	}
	public  function indata($msg){
	
		$this->ret=json_decode(json_decode($msg,1)['data'],1)[0];
		// d($this->ret);
	}
}
