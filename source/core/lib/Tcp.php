<?php


namespace ng169\lib;
use ng169\Y;
use ng169\lib\Log as YLog;
use ng169\lib\Socket;
use ng169\lib\Job;

/*use ng169\cache\Rediscache ;*/
use ng169\lib\SocketCache ;
checktop();




class Tcp extends Socket{
	public static $recvlength=4048; //buffer大小4k
	public function __construct($host,$port,$sslcontent){
		if($sslcontent){
		
			self::$master=stream_socket_server("tcp://{$host}:{$port}", $errno, $errmsg, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN,$sslcontent);
			stream_socket_enable_crypto(self::$master,false);
		}else{
			self::$master=stream_socket_server("tcp://{$host}:{$port}", $errno, $errmsg, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
			
		}
		
		if (!self::$master) { 
		self::error("错误代码：$errno--$errmsg \n");
 
		} 
		self::$sockets["-1"] = ['resource' => self::$master];
		$sid=self::adddblog(S_OPEN);
		self::$sid=$sid;
		self::reset_client();
		self::windowshow("\nServices ID：".self::$sid);
		self::windowshow("\n开启TCP监听：$host:$port");
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
		stream_set_blocking(self::$master , false);
		$read_num= stream_select($sockets, $write, $except, NULL);
		// select作为监视函数,参数分别是(监视可读,可写,异常,超时时间),返回可操作数目,出错时返回false;
		if(false === $read_num){
			self::error([
					'error_select',
					$err_code = socket_last_error(),
					socket_strerror($err_code)
				]);
			return;
		}
		foreach($sockets as $socket){
			// 如果可读的是服务器socket,则处理连接逻辑
			if($socket == self::$master){				
				$client = stream_socket_accept(self::$master,0, $remote_address);
				//				socket_set_nonblock($client);
				// 创建,绑定,监听后accept函数将会接受socket要来的连接,一旦有一个连接成功,将会返回一个新的socket资源用以交互,如果是一个多个连接的队列,只会处理第一个,如果没有连接的话,进程将会被阻塞,直到连接上.如果用set_socket_blocking或socket_set_noblock()设置了阻塞,会返回false;返回资源后,将会持续等待连接。
				if(false === $client){
					self::error([
							'err_accept',
							$err_code = socket_last_error(),
							socket_strerror($err_code)
						]);
					continue;
				}
				else{
					self::connect($client);
					continue;
				}
			}
			else{
				// 如果可读的是其他已连接socket,则读取其数据,并处理应答逻辑
				$buffer = stream_socket_recvfrom($socket,  Tcp::$recvlength, 0);
				$bytes=strlen($buffer);
				
				/*socket_set_nonblock($bytes);*/
				//长度限制10000个字符
				if($bytes < 9){
					$recv_msg = self::disconnect($socket);
				}
				else{
					if(!self::$sockets[self::getindex($socket)]['handshake']){
						$bool = self::handShake($socket, $buffer);
						//这里不成功可能是后台直接推送的信息；要直接执行。
						if(!$bool){
							self::systemdeal($socket,$buffer);
							break;
						}
					}
					else{	
						$recv_msg = self::parse($buffer);
						
						if(self::dealMsg($socket, $recv_msg)){
							self::conns(true);
						}						
					}
				}
			}
		}
	}
	private function epollmodel(){
		$fd=&self::$master;
		//非阻塞模式	
		
		stream_set_blocking(self::$master , false);	
		$epoll=\ng169\lib\Epoll::getInstance();
		self::$event_base=$epoll;
		$epoll->add($fd, \ng169\lib\Epoll::READ, function($fd){
			
				$conn = stream_socket_accept($fd);
			
				\ng169\lib\Tcp::_epoll_fun($conn);
			});
			
		$epoll->run(); 
	}
	public static function _epoll_fun($socket){
		

		$index=self::getindex($socket);
		
		if(!isset(self::$sockets[$index])){
			//看看连接有没有被记录，没记录就建立链接，有记录就处理数
			self::connect($socket);//创建新连接	
			
			self::$event_base->add($socket, \ng169\lib\Epoll::READ, function($conn){
				
					\ng169\lib\Tcp::_epoll_fun_con($conn);
				});		
		}	
	}
	public  static function _epoll_fun_con($socket){
		
		
		$buffer = '';
		$index=self::getindex($socket);	
	
		
		
	
		$buffer = stream_socket_recvfrom($socket,  self::$recvlength, 0);

		$bytes=strlen($buffer);	
//		d($buffer);
		if($bytes < 9){
			$recv_msg = self::disconnect($socket);			
		}
		else{
			if(!self::$sockets[$index]['handshake']){			
				$bool = self::handShake($socket, $buffer);
				//这里不成功可能是后台直接推送的信息；要直接执行。			
				if(!$bool){
					self::systemdeal($socket,$buffer);
				}
			}
			else{	
					
				$recv_msg = self::parse($buffer);						
				if(self::dealMsg($socket, $recv_msg)){
					self::conns(true);
				}			
			}
		}
		//		self::$event_base->del($socket);
	}
	/**
	* 将socket添加到已连接列表,但握手状态留空;
	*
	* @param $socket
	*/
	private static function connect($socket){
		//		socket_getpeername($socket, $ip, $port);
		$data=stream_socket_get_name($socket,true);
		list($ip,$port)=explode(':',$data);
		$index=self::getindex($socket);		
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
			'sid'=>self::$sid,
		];
		$insert = $socket_info;
		$insert['resource'] = serialize($socket);
		$socket_info['clientid'] = $index;
		self::$sockets[$index] = $socket_info;

	}
	/**
	* 用公共握手算法握手
	*
	* @param $socket
	* @param $buffer
	*
	* @return bool
	*/
	public static function handShake($socket, $buffer){

		// 获取到客户端的升级密匙
		$line_with_key = substr($buffer, strpos($buffer, 'Sec-WebSocket-Key:') + 18);
		$key           = trim(substr($line_with_key, 0, strpos($line_with_key, "\r\n")));
		if(!$key)return false;
		// 生成升级密匙,并拼接websocket升级头
		$upgrade_key = base64_encode(sha1($key . "258EAFA5-E914-47DA-95CA-C5AB0DC85B11", true));// 升级key的算法
		$upgrade_message = "HTTP/1.1 101 Switching Protocols\r\n";
		$upgrade_message .= "Upgrade: websocket\r\n";
		$upgrade_message .= "Sec-WebSocket-Version: 13\r\n";
		$upgrade_message .= "Connection: Upgrade\r\n";
		$upgrade_message .= "Sec-WebSocket-Accept:" . $upgrade_key . "\r\n\r\n";
		stream_socket_sendto($socket, $upgrade_message, 0);
		//		T('sock_client')->update(array('handshake'=>1),array('clientid'=>self::getindex($socket)));
		self::$sockets[self::getindex($socket)]['handshake'] = true;
		//		socket_getpeername($socket, $ip, $port);
		return true;
	}
	/**
	* 发送
	* 
	* @return
	*/
	public function send($clientid,$msg){
	$socket=self::getsock($clientid);
		if(is_string($msg)){
			$msg = self::build($msg);
		}
		else{
			$msg = json_encode($msg);
			$msg = self::build($msg);
		}
		if($socket)stream_socket_sendto($socket, $msg, 0);
	}
}

?>
