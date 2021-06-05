<?php


namespace ng169\lib;
use ng169\Y;
use ng169\lib\Log as YLog;
use ng169\lib\Job;

/*use ng169\cache\Rediscache ;*/
use ng169\lib\SocketCache ;
checktop();
define('OS_TYPE_LINUX', 'linux');
define('OS_TYPE_WINDOWS', 'windows');
define('S_OPEN', '1');
define('S_CLOSE', '0');
define('USERMSG', '0');
define('ADMINMSG', '1');
define('SYSMSG', '2');




class Dns extends Y{
	const LISTEN_SOCKET_NUM = 100000;
	const CHECK_REFLASH_TIME = 30;//广播间隔时间30秒；
	const REDIS_PRE_C = ":tokenCid_";//广播间隔时间30秒；
	const REDIS_PRE_U = ":tokenUid_";//广播间隔时间30秒；
	public static $sockets = [];
	public static $sks = [];
	public static $redis=null;
	public static $master;
	public static $ip;
	public static $sid;
	public static $port;
	public static $stop = false;
	public static $userlogin = [];//记录刷新的用户id；防止重复广播
	private static $type = "udp";
	public static $_OS=OS_TYPE_LINUX;
	public static $PCNTL=false;
	public static $EPOLL=false;
	public static $coons=0;//当前连接数
	public static $syscode=0;
	public static $admincode=0;
	public static $sips=[];
	public static $sip=[];
	public static $event_base=NULL;
	public static $debug=true;
	public static $sysconn=[];
	//是否主服务器
	public static $is_master=false;
	/*protected static function lock()
	{
	$fd = fopen(static::$_startFile, 'r');
	if (!$fd || !flock($fd, LOCK_EX)) {
	static::log("Workerman[".static::$_startFile."] already running");
	exit;
	}
	}

   
	protected static function unlock()
	{
	$fd = fopen(static::$_startFile, 'r');
	$fd && flock($fd, LOCK_UN);
	}*/
	//[uid=>time]
	/*检测是否已经开启了该端口服务
	*/
	public static function check_sock_open($ip,$port){
		$port   = $port?$port:DB_SOCKPOST;
		$type   = self::$type;
		$socket = @stream_socket_client("$type://$ip:$port", $errno, $errstr);
		if(gettype($socket) != 'resource'){
			return false;
		}
		fclose($socket);
		unset($socket);
		return true;
	}
	private static function reset_client(){
		/*    T('sock_client')->update(array('online'=>0,'handshake'=>0),array('online'=>1,'handshake'=>1));*/
		T('sock_client')->del(['sid'=>self::$sid]);
	}
	private static function generate_password($length = 8){
		$chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$password = '';
		for($i = 0;$i < $length;$i++){
			$password .= $chars[ mt_rand(0, strlen($chars) - 1) ];
		}return $password;
	}
	private static function reset_system_code(){
		$code = self::generate_password();
		T('sock_type')->update(array('password'=>$code,'addtime' =>time()),array('type'=>3));
		return true;
	}
	private static function reset_admin_code(){
		$code = self::generate_password();
		T('sock_type')->update(array('password'=>$code,'addtime' =>time()),array('type'=>2));
		return true;
	}
	/**
	* Check sapi.
	*
	* @return void
	*/
	protected static function checkSapiEnv(){
		// Only for cli.
		/*if(php_sapi_name() != "cli"){
		exit("only run in command line mode \n");
		}*/
		//检查系统类型
		if(DIRECTORY_SEPARATOR === '\\'){
			self::$_OS = OS_TYPE_WINDOWS;
		}
		//检查epoll
		if(class_exists('\EventBase')){
			self::$EPOLL = true;
			
		}else{
			self::windowshow('Event或LibEvent组件不存在。');
		}
		//检查pcntl
		if(function_exists('pcntl_signal')){
			self::$PCNTL = true;
			
		}else{
			self::windowshow('pcntl组件不存在。');
		}
		
	}
    
	public static function starts($host, $port){
	
	
		//检查系统环境
		
		self::checkSapiEnv();
		//设置错误输出
		set_error_handler( function($code, $msg, $file, $line){
				/* Worker::safeEcho("$msg in file $file on line $line\n");*/
				self::error("错误代码：$code--$msg in file $file on line $line\n");
			});
			
		//检测是否已经监听；
		//数据库用户表下线		
		self::initcode();//初始化密码
		self::daemonize();//守护进程
		self::installSignal();//注册信号处理
		$type = self::$type;
		self::$ip=$host;
		self::$port=$port;
		self::$redis=SocketCache::getCache();
		//重置系统密码
		$host='0.0.0.0';
		
		if($type == 'tcp'){

			self::$master = stream_socket_server("tcp://{$host}:{$port}", $errno, $errmsg, STREAM_SERVER_BIND | STREAM_SERVER_LISTEN);
		}
		if($type == 'udp'){

			self::$master = stream_socket_server("udp://{$host}:{$port}", $errno, $errmsg, STREAM_SERVER_BIND );

		}

		self::$sockets["-1"] = ['resource' => self::$master];

		$sid=self::adddblog(S_OPEN);
		//		self::syscon();
		self::$sid=$sid;
		self::reset_client();
		
		self::windowshow("\nServices ID：".self::$sid);
		self::windowshow("\n开启监听：$host:$port");
		
			
		
		if(G_DEBUG){
					
			self::doServer();
		}else{
			@self::doServer();
		}
				
			
		
	}
	//分布式连接
	protected static function syscon1($sid=null){	
		if(self::$is_master && $sid==null){
			//连接主master
			if(self::$PCNTL){
				//如果有pcntl另外开线程
				$pid=pcntl_fork();
				if($pid<0){
					self::windowshow('子进程失败');
				}elseif($pid==0){
					self::windowshow('子进程--连接master');
					//					 = T('sock_type')->set_field(array('password'))->get_one(array('type'=>3));
					$systemcode=self::$syscode;
					if(!$systemcode){
						YLog::txt('system结构密码丢失');
						return false;
					}
					$send['stype'] =3;
					$send['code'] = $systemcode['password'];
					$send['data'] = self::yscode(serialize($data));
					$msg    = serialize($send);	
						
					$ip=self::$is_master['ip'];
					$port=self::$is_master['port'];
					$client = stream_socket_client("tcp://$ip:$port", $errno, $errmsg, 1);
					if($client){
						
						self::$sysconn[self::$is_master['sid']]=$client;
					}
					/*d(self::$sid);*/
					/*$i=360000;
					while($i){
					$i--;	
					}	*/		
					exit();
				}else{

					pcntl_wait($status);
					/*	self::windowshow('父进程');*/
				}
			}
		}
		if($sid){
			$server=T('sockserver')->get_one(['sid'=>$sid,'flag'=>1]);
			if(!$server)return false;
			if(self::$PCNTL){
				//如果有pcntl另外开线程
				$pid=pcntl_fork();
				if($pid<0){
					self::windowshow('子进程失败');
				}elseif($pid==0){
					self::windowshow('子进程--连接slave');
					//					 = T('sock_type')->set_field(array('password'))->get_one(array('type'=>3));
					$systemcode=self::$syscode;
					if(!$systemcode){
						YLog::txt('system结构密码丢失');
						return false;
					}
					$send['stype'] =3;
					$send['code'] = $systemcode['password'];
					//					$send['data'] = self::yscode(serialize($data));
					$msg    = serialize($send);	
						
					$ip=$server['ip'];
					$port=$server['port'];
					$client = stream_socket_client("tcp://$ip:$port", $errno, $errmsg, 1);
					if($client){
						
						self::$sysconn[$server['sid']]=$client;
					}
					/*d(self::$sid);*/
					/*$i=360000;
					while($i){
					$i--;	
					}	*/		
					exit();
				}else{

					pcntl_wait($status);
					/*	self::windowshow('父进程');*/
				}
			}
		
		
		
		
		
		}
		return false;
	} 
	protected static function syscon($sid=null){	
		if(self::$is_master && $sid==null){
			//连接主master
			if(1){
				//如果有pcntl另外开线程
				if(1){
					self::windowshow('子进程--连接master');
					//					 = T('sock_type')->set_field(array('password'))->get_one(array('type'=>3));
					$systemcode=self::$syscode;
					
					if(!$systemcode){
						YLog::txt('system结构密码丢失');
						return false;
					}
					$send['stype'] =3;
					$send['code'] = $systemcode;
					//					$send['data'] = self::yscode(serialize($data));
					$msg    = serialize($send);	
						
					$ip=self::$is_master['ip'];
					$port=self::$is_master['port'];
					$client = stream_socket_client("tcp://$ip:$port", $errno, $errmsg, 1);
					if($client){
						
						self::$sysconn[self::$is_master['sid']]=$client;
					}
				
				}
			}
		}
		if($sid){
			$server=T('sockserver')->get_one(['sid'=>$sid,'flag'=>1]);
			if(!$server)return false;
			if(1){
				//如果有pcntl另外开线程
				if(1){
					self::windowshow('子进程--连接slave');
					//					 = T('sock_type')->set_field(array('password'))->get_one(array('type'=>3));
					$systemcode=self::$syscode;
					if(!$systemcode){
						YLog::txt('system结构密码丢失');
						return false;
					}
					$send['stype'] =3;
					$send['code'] = $systemcode;
					//					$send['data'] = self::yscode(serialize($data));
					$msg    = serialize($send);	
						
					$ip=$server['ip'];
					$port=$server['port'];
					$client = stream_socket_client("tcp://$ip:$port", $errno, $errmsg, 1);
					if($client){
						
						self::$sysconn[$server['sid']]=$client;
					}
					/*d(self::$sid);*/
					/*$i=360000;
					while($i){
					$i--;	
					}	*/		
					/*exit();*/
				}
			}
		
		
		
		
		
		}
		return false;
	}
	//守护进程
	protected static function daemonize(){
		if( static::$_OS !== OS_TYPE_LINUX){
			return;
		}
		umask(0);
		$pid = pcntl_fork();
		if(-1 === $pid){
			throw new Exception('fork fail');
		} elseif($pid > 0){
			exit(0);
		}
		if(-1 === posix_setsid()){
			throw new Exception("setsid fail");
		}
		// Fork again avoid SVR4 system regain the control of terminal.
		$pid = pcntl_fork();
		if(-1 === $pid){
			throw new Exception("fork fail");
		} elseif(0 !== $pid){
			exit(0);
		}
	}
	//注册信号处理
	protected static function installSignal(){
		if(static::$_OS !== OS_TYPE_LINUX){
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
	public static function adddblog($flag){
		$where=['ip'=>self::$ip,'port'=>self::$port];
		$isin=T('sockserver')->set_where($where)->get_one($where);
		$is_master=T('sockserver')->set_where($where)->get_one(['flag'=>1,'ismaster'=>1]);
		if($is_master){
			self::$is_master=$is_master;
		}
		if($isin){
			$info=['starttime'=>time(),'flag'=>$flag];
			if(!$is_master){
				$info['ismaster']=1;
			}
			T('sockserver')->update($info,$where);
			return $isin['sid'];
		}else{
			if($is_master){
				$info=['ip'=>self::$ip,'port'=>self::$port,'starttime'=>time(),'flag'=>1,'conns'=>0];
			}else{
				$info=['ip'=>self::$ip,'port'=>self::$port,'starttime'=>time(),'flag'=>1,'conns'=>0,'ismaster'=>1];
			}
			return T('sockserver')->add($info);
		}
		return true;
	}
	/**
	* 显示在控制台
	* @param undefined $msg 消息
	* 
	* @return
	*/
	public static function windowshow($msg){
		echo "\n".$msg;
	}
	/**
	* 踢用户下线
	* 
	* @return
	*/
	public static function kick($client){
		$socket=self::$sockets[$client]['resource'];
		self::disconnect($socket);
		unset(self::$sockets[$client]);
	}
	/**
	* 关闭服务器
	* 
	* @return
	*/
	public static function closeserver(){
		if(!isset(self::$master))return false;
		socket_close();
		self::$stop=true;
		unset(self::$sockets);
		//		unset($this);
		self::adddblog(S_CLOSE);
		self::reset_client();//清空当前私有连接客户端
	}
	private static function doServer(){
		if(self::$EPOLL){
			self::_epoll_model();
		}else{
			while(!self::$stop){
				self::_select_model();
			}
			
			
		}
	}
	//select模式，性能低
	private static function _select_model(){

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
				$buffer = stream_socket_recvfrom($socket,  4048, 0); //buffer大小4k
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
	public  static function _epoll_fun_con($socket){
		$buffer = '';
		$index=self::getindex($socket);		
		if(is_resource($socket)){
			//最大4m
			/*while($content = fread($socket, 4048)){
			$buffer .= $content;
			}*/
		}
		$buffer = stream_socket_recvfrom($socket,  4048, 0);
	
		$bytes=strlen($buffer);		
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
	public static function _epoll_fun($socket){
		

		$index=self::getindex($socket);
		if(!isset(self::$sockets[$index])){
			//看看连接有没有被记录，没记录就建立链接，有记录就处理数
			self::connect($socket);//创建新连接	
			
			self::$event_base->add($socket, \ng169\lib\Epoll::READ, function($conn){
					\ng169\lib\Socket::_epoll_fun_con($conn);
				});		
		}	
	}
	//epoll模式，性能高
	private static function _epoll_model(){	
		$fd=&self::$master;
		//非阻塞模式	
		stream_set_blocking(self::$master , false);	
		
		$epoll=\ng169\lib\Epoll::getInstance();
		self::$event_base=$epoll;
		$epoll->add($fd, \ng169\lib\Epoll::READ, function($fd){
			
				$conn = stream_socket_accept($fd);
				stream_set_blocking($conn, false);
				\ng169\lib\Socket::_epoll_fun($conn);
			});
			
		$epoll->run(); 
	}
	/**
	* 增减连接数
	* @param undefined $bool 0减，1加
	* 
	* @return
	*/
	private static function conns($bool){
		return false;
		if(!self::$sid)return false;
		if($bool){
			self::$coons+=1;
			
		}else{
			self::$coons-=1;
		}
		
		return T('sockserver')->update(['conns'=>self::$coons],['sid'=>self::$sid]);
	}
	public static function getindex($socket){
		/*$sockets = array_column(self::$sockets,'resource','clientid');
		$index   = array_keys($sockets,$socket);*/
		/*socket_getpeername($socket, $ip, $port);*/
		
		
		/*$index=(long2ip($ip).$port);*/
		/*$ipstr= preg_replace( '/[\.\:]/', '', $ip); */
		/*	$index=self::$sid.($ipstr.$port);*/
		$sip=self::$sid;
		$index=$sip."SIP".intval($socket);
		
		return $index;
	
	}
	public static function getsock($clientid){
		if(isset(self::$sockets[$clientid])){
			return  self::$sockets[$clientid]['resource'];
		}
		return false;;
	}
	public static function gettk($clientid){
		if(isset(self::$sockets[$clientid])){
			return  self::$sockets[$clientid]['token'];
		}
		return false;;
	}
	public static function getuidtk($uid){
		$cachetk=Socket::$redis->get(self::REDIS_PRE_U.$uid);
		if(!$cachetk){
			
			$usertk=T('user')->set_field('token')->get_one(['uid'=>$uid]);
				
			if($usertk){
				Socket::$redis->set(self::REDIS_PRE_U.$uid,$usertk['token']);
				return $usertk['token'];
			}else{
				return false;
			}
				
		}
		return $cachetk;
	}
	/**
	* 将socket添加到已连接列表,但握手状态留空;
	*
	* @param $socket
	*/
	public static function connect($socket){
		//		socket_getpeername($socket, $ip, $port);
		$data=stream_socket_get_name($socket,true);
		list($ip,$port)=explode(':',$data);
		//		$index=spl_object_hash($socket);
		/*$index=($ip.$port);*/
	
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
		/*$id = T('sock_client')->add($insert);*/
		//直接写数据库压力大，先进内存，确认登入在写数据库  
		$socket_info['clientid'] = $index;
		self::$sockets[$index] = $socket_info;
		
		/*self::broadcast($msg);*/
		/*return 1;*/
	}

	//检测用户是否刷新
	public static function reflash($uid){
		$time = (int)self::$userlogin[$uid];

		$go_time = time() - $time;

		self::$userlogin[$uid] = time();

		if($go_time > self::CHECK_REFLASH_TIME)return true;
		return false;
	}
	/**
	* 客户端关闭连接
	*
	* @param $socket
	*
	* @return array
	*/
	public static function disconnect($socket){

		/*$recv_msg = [
		'type' => 'logout',
		'content' => self::$sockets[self::getindex($socket)]['uname'],
		];*/
		if(self::$EPOLL){
			self::$event_base->del($socket);	//epoll退出
		}
		if(self::$sockets[self::getindex($socket)]['check']){
			T('sock_client')->update(array('online'   =>0,'handshake'=>0),array('clientid'=>self::getindex($socket)));
		}
		unset(self::$sockets[self::getindex($socket)]);
		self::conns(false);
		/*self::broadcast($msg);*/
		return true;
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
		//		socket_write($socket, $upgrade_message, strlen($upgrade_message));// 向socket里写入升级信息
		stream_socket_sendto($socket, $upgrade_message, 0);
		//		T('sock_client')->update(array('handshake'=>1),array('clientid'=>self::getindex($socket)));
		self::$sockets[self::getindex($socket)]['handshake'] = true;
		//		socket_getpeername($socket, $ip, $port);

		return true;
	}

	/**
	* 解析数据
	*
	* @param $buffer
	*
	* @return bool|string
	*/
	private  static function parse($buffer){
		$decoded = '';
		$len     = ord($buffer[1]) & 127;
		if($len === 126){
			$masks = substr($buffer, 4, 4);
			$data  = substr($buffer, 8);
		}
		else
		if($len === 127){
			$masks = substr($buffer, 10, 4);
			$data  = substr($buffer, 14);
		}
		else{
			$masks = substr($buffer, 2, 4);
			$data  = substr($buffer, 6);
		}
		for($index = 0; $index < strlen($data); $index++){
			$decoded .= $data[$index] ^ $masks[$index % 4];
		}
		return $decoded;
		return unserialize($decoded, true);
	}

	/**
	* 将普通信息组装成websocket数据帧
	*
	* @param $msg
	*
	* @return string
	*/
	private  static function build($msg){
		$frame = [];
		$frame[0] = '81';
		$len = strlen($msg);
		if($len < 126){
			$frame[1] = $len < 16 ? '0' . dechex($len) : dechex($len);
		}
		else
		if($len < 65025){
			$s = dechex($len);
			$frame[1] = '7e' . str_repeat('0', 4 - strlen($s)) . $s;
		}
		else{
			$s = dechex($len);
			$frame[1] = '7f' . str_repeat('0', 16 - strlen($s)) . $s;
		}

		$data = '';
		$l    = strlen($msg);
		for($i = 0; $i < $l; $i++){
			$data .= dechex(ord($msg{$i}));
		}
		$frame[2] = $data;

		$data = implode('', $frame);

		return pack("H*", $data);
	}
	private  static function checksystem($code){

		
		return ($code==self::$syscode);
		/* $bool;*/
	}
	private static  function checkadmin($code){
		//		return T('sock_type')->get_one(array('type'    =>2,'password'=>$code));
		return ($code==self::$admincode);
	}
	private  static function initcode(){
		$bool = T('sock_type')->get_one(array('type'    =>3));
		
		self::$syscode=$bool['password'];
		$bool = T('sock_type')->get_one(array('type'    =>2));
		self::$admincode=$bool['password'];
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
	public static function socksend($clientid,$msg){
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
	
	private static  function dealMsg($socket, $recv_msg){
		/*d($recv_msg);*/
		$recv_msg = (json_decode($recv_msg,1));
		$type     = isset($recv_msg['stype'])?$recv_msg['stype']:'';
		$code     = isset($recv_msg['code'])?$recv_msg['code']:'';
		$recv_msg['fun']     = isset($recv_msg['fun'])?$recv_msg['fun']:'run';
		/*$code     = $recv_msg['code'];*/
		if(!isset($recv_msg['action']) && isset($recv_msg['control'])){
			$recv_msg['action']=$recv_msg['control'];			
		}
		//无参数退出；		
		if(!isset($recv_msg['action'])){
			return false;
		}	
		
		switch($type){
			case 2:
			
			if(self::checkadmin($recv_msg['code'])){
				/*Lang::sockload();*/
				self::admindeal($socket,$recv_msg);
			}
			case 3:
			
			if(self::checksystem($recv_msg['code'])){
				/*Lang::sockload();*/
				self::systemdeal($socket,$recv_msg);
			}
			break;
			default:					
			self::userdeal($socket,$recv_msg);
		}		
		return 1;
		/*  return self::build(serialize($response));*/
	}
	//用户发来信息
	private static function userdeal($socket, $recv_msg){
		return self::sockdoing($socket,'user',$recv_msg);
	}
	public static function sendmaster($sip,$data){
		//检测是否链接sip服务器；
		//没链接sip，就转master
		//master重连
		//发送数据
		if(self::$sysconn[$sip] && is_resource(self::$sysconn[$sip])){
			stream_set_blocking(self::$sysconn[$sip] , false);
			stream_socket_sendto(self::$sysconn[$sip], $data."\n");
			return 1;
		}else{
			//是否本身就是主master ，是就直接连slave
			if(self::$is_master){
				if(self::$is_master['sid']==self::$sid){
					//直接连
					self::syscon($sip);
					stream_set_blocking(self::$sysconn[$sip] , false);
					stream_socket_sendto(self::$sysconn[$sip], $data."\n");
				}else{
					//连master
					self::syscon(self::$is_master['sid']);
					stream_set_blocking(self::$sysconn[self::$is_master['sid']] , false);
					stream_socket_sendto(self::$sysconn[self::$is_master['sid']], $data."\n");
				}
			}else{
				//直接连接slave
				self::syscon($sip);
				stream_set_blocking(self::$sysconn[$sip] , false);
				stream_socket_sendto(self::$sysconn[$sip], $data."\n");
			}
			
			
			
		}
		return 1;
	}
	public static function phps($action,$datacontent = null){
		$systemcode = T('sock_type')->set_field(array('password'))->get_one(array('type'=>3));
		if(!$systemcode){
			YLog::txt('system结构密码丢失');
			return false;
		}
		$ip   = getip();
		$port = DB_SOCKPOST;
		$data['action'] = $action;
		$data['data'] = $datacontent;
		$send['code'] = $systemcode['password'];
		$send['data'] = self::yscode(serialize($data));
		$msg    = serialize($send);
		$client = stream_socket_client("tcp://$ip:$port", $errno, $errmsg, 1);
		fwrite($client, $msg."\n");
		fclose($client);
		return true;
	}
	
	public static function yscode($data){
		/*YLog::txt($data);
		YLog::txt(sizeof($data));*/
		/*$ysdata = gzcompress(base64_encode($data));*/
		/*YLog::txt($ysdata);
		YLog::txt(strlen($ysdata));*/
		return $data;
	}
	public static function unyscode($data){

		$ysdata = unserialize($data);
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
	public static function phpsend($ip,$port,$data){
		$systemcode = T('sock_type')->set_field(array('password'))->get_one(array('type'=>3));
		if(!$systemcode){
			YLog::txt('system结构密码丢失');
			return false;
		}
		$send['stype'] =3;
		$send['code'] = $systemcode['password'];
		$send['data'] = self::yscode(serialize($data));
		$msg    = serialize($send);		
		$client = stream_socket_client("tcp://$ip:$port", $errno, $errmsg, 1);
		if($client){
			fwrite($client, $msg."\n");
			fclose($client);
			return true;
		}
		return false;
	}
	public static function phpsend_live($sip,$data){
		
		$send['stype'] =3;
		$send['code'] = self::$syscode;
		$send['data'] = self::yscode(serialize($data));
		$msg    = serialize($send);
		/*$job=Job::getInstance();
		$job->add('5',function(){
			d('5秒之后的事情');
		});*/
		if(isset(self::$sysconn[$sip]) && is_resource(self::$sysconn[$sip])){
			
			
			fwrite(self::$sysconn[$sip], $msg."\n");
			return true;
		}
		$server = T('sockserver')->get_one(array('sid'=>$sip,'flag'=>1));
		$ip=$server['ip'];
		$port=$server['port'];
		$client = stream_socket_client("tcp://$ip:$port", $errno, $errmsg, 1);
		if($client){
			self::$sysconn[$sip]=$client;

			fwrite($client, $msg."\n");
			/*fclose($client);*/
			return true;
		}
		return false;
	}
	//系统发来信息
	private static  function systemdeal($socket,$data){
		
		//检测发信息的端口是否同一个ip；
		//检测系统密码正确；	
		$index=self::getindex($socket);
		$data = @unserialize($data);
			
		if(!$data)return false;
		if(!isset($data['stype']) || $data['stype']!=3)return false;
		$code   = $data['code'];
		$recv   = self::unyscode($data['data']);
				
		if(self::$sockets[$index]['handshake']!=2){
			$bool   = self::checksystem($code);
			if($bool){
				self::$sockets[$index]['handshake']==2;
			}else{
				self::disconnect($socket);
				return false;	
			}
		}	
			
		if(!isset($recv['action']))return false;
		$action = $recv['action'];
		$fun= isset($recv['fun'])?$recv['fun']:'run';		
		$type   = 'system';
		
		if($bool){
			$bool2 = self::get_typea_ction_file($type,$recv['action']);		
			if($bool2){
				$classname = "ng169\\sock\\{$type}\\".$action;
				if(!class_exists($classname)){
					self::error($type.'下执行文件类错误');
					return true;
				}			
				$class     = new $classname();
				if(method_exists($class, $fun) &&  $fun!=''){
					
					$class->init($socket,$recv);
					
					$class->$fun($recv);
					return true;
				}
				elseif(method_exists($class, $fun)){
					$class->run($recv);
					return true;
				}else{
					self::error($type.'下'.$action.'执行文件错误');
					self::disconnect($socket);
					return false;
				}
			}
		}
		return true;
	}
	//管理员发来信息
	private static function admindeal($socket,$recv_msg){
		return self::sockdoing($socket,'admin',$recv_msg);
	}

	/*public static function send($clentid,$data){
	$client = self::$sockets[$clentid];
	if(isset($client)){
	self::socksend($client['resource'],$data);
	return true;
	}
	return false;
	}*/
	/**
	* 广播消息
	*
	* @param $data
	*/
	public static function broadcast($data){
		/*if(!is_string($data)){
		$data=serialize($data);
		}*/
		foreach(self::$sockets as $k=>$socket){
			if($k != '-1'){
				$d['action'] = 'login';
				$d['data'] = $data;
				$d2['data'] = $d;
				self::socksend($socket['resource'],$d2);
			}
		}
	}
	/**
	* 记录错误信息
	*
	* @param array $info
	*/
	private static function error($info){

		if(self::$debug){
			self::windowshow($info);
		}else{
			YLog::txt($info,LOG.'websocket_error.log');
		}
		
	}
	private static function get_typea_ction_file($type,$action){
		$sockfile = SOURCE."/sock/{$type}Sock.php";
		im($sockfile);
		$file     = SOURCE."sock/{$type}/$action.php";

		if(file_exists($file)){
			/* require_once ($file);*/
			im($file);			
			return true;
		}
		else{
			self::error($type.'下'.$action.'执行文件'.$file.'不存在');
			return false;
			/*  d('API '.$apiname. ' IS NOT EXIST',1);*/
		}
		return true;
	}
	private static function sockdoing($sock,$type,$recv){		
		$action=	$recv['action'];
		$fun=$recv['fun'];
		$data=$recv['data'];
		$bool = self::get_typea_ction_file($type,$action);	
		if($bool){
			$classname = "ng169\\sock\\{$type}\\".$action;
			$class     = new $classname();					
			$class->init($sock,$recv);				
			if(!class_exists($classname)){				
				self::error($type.'下执行文件类错误');
				return true;
			}			
			if(method_exists($class, $fun) &&  $fun!=''){
				$class->$fun($data);
				return true;
			}
			elseif(method_exists($class, 'run')){
				$class->run($data);
				return true;
			}
			else{
				self::error($type.'下'.$action.'执行文件错误');
				return false;
			}		
			unset($control);
		}
	}
}

?>
