<?php


namespace ng169\lib;
use ng169\Y;
use ng169\lib\Log as YLog;
use ng169\lib\Job;

/*use ng169\cache\Rediscache ;*/
use ng169\lib\SocketCache ;
use ng169\lib\Socket;
checktop();

class Udp extends Socket{
	public static $recvlength=4048; //buffer大小4k
	public function __construct($host,$port,$sslcontent=false){
		
			self::$master=stream_socket_server("udp://{$host}:{$port}", $errno, $errmsg, STREAM_SERVER_BIND);
		
		if (!self::$master) { 
		self::error("错误代码：$errno--$errmsg \n");
 
		} 
		self::$sockets["-1"] = ['resource' => self::$master];
		$sid=self::adddblog(S_OPEN);
		self::$sid=$sid;
		self::reset_client();
		self::windowshow("\nServices ID：".self::$sid);
		self::windowshow("\n开启UDP监听：$host:$port");
		return $this;
	}
	public function recv(){
		if(self::$EPOLL){
			$this->epollmodel();
		}
		else{
			while(!self::$stop){
				$this->selectmodel();
			}
		}
	}
	private function selectmodel(){
		$sockets = array_column(self::$sockets, 'resource');
		do{ 
			//接收客户端发来的信息 
			$inMsg=stream_socket_recvfrom(self::$master, $this->recvlength, 0, $peer);
			self::_epoll_fun($peer,$inMsg);
		} while($inMsg !== false);


		
	}
	private function epollmodel(){
		$fd=&self::$master;
		//非阻塞模式	
		stream_set_blocking($fd , false);	
		$epoll=\ng169\lib\Epoll::getInstance();
		self::$event_base=$epoll;
		$epoll->add($fd, \ng169\lib\Epoll::READ, function($fd){
				$msg=stream_socket_recvfrom($fd, self::$recvlength, 0, $peer);
				stream_set_blocking($fd , false);
				\ng169\lib\Udp::_epoll_fun($peer,$msg);
			});
			
		$epoll->run(); 
	}
	public static function _epoll_fun($peer,$msg){
		
		$index=self::getindex($peer);
			
		if(!isset(self::$sockets[$index])){
			//看看连接有没有被记录，没记录就建立链接，有记录就处理数
			self::connect($peer);//创建新连接	
			
		}	
		
//		$msg = self::parse($msg);	
		if($msg && $peer && Socket::dealMsg($peer, $msg)){
					Socket::conns(true);
			}
		
	}
	
	/**
	* 将socket添加到已连接列表,但握手状态留空;
	*
	* @param $socket
	*/
	private static function connect($socket){
		//		socket_getpeername($socket, $ip, $port);
		/*$data=stream_socket_get_name($socket,true);*/
		if(!$socket)return false;
		list($ip,$port)=explode(':',$socket);
		$index=Socket::getindex($socket);		
		$socket_info = [
			'resource' => $socket,
			'uname' => '',
			'token' => '',
			'handshake' => false,
			'ip' => $ip,
			'port' => $port,
			'type'=>0,
			'check'=>0,
			'clientid'=>'',
			'online'=>1,
			'addtime'=>time(),
			'sid'=>Socket::$sid,
		];
		$insert = $socket_info;
		$insert['resource'] = serialize($socket);
		$socket_info['clientid'] = $index;
		Socket::$sockets[$index] = $socket_info;
		return true;
	}
	
	/**
	* 发送
	* 
	* @return
	*/
	public function send($clientid,$msg){
		
//		$socket=self::getsock(self::getindex($clientid));
		$socket=self::getsock($clientid);
		if(is_string($msg)){
//			$msg = self::build($msg);
		}
		else{
			$msg = json_encode($msg);
//			$msg = self::build($msg);
		}		
		if($socket)stream_socket_sendto(self::$master, $msg, 0,$socket);
	}
}

?>
