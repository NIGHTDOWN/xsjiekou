<?php
namespace ng169\lib;
use \Event;
use \EventBase;
use ng169\lib\Epoll;
//定时器
class Job{ 
	public static $instance = null;
	public static $cache=null;
	public static $epoll=null;
	public static $event_base=null;
	public static $events=[];
	public static $task=[];
	public static $lastdbcoon=[];
	public static $event=null;
	public static $doing=null;
	public static function setTimeOut($second,$callback){		
		$event = Event::timer(self::$event_base ,$callback,$second);
		self::$events[spl_object_hash($event)]=$event;
		self::$event=$event;	
		$event->addTimer($second);
		self::$event_base ->loop();
	}
	public static function setInterval($time,$fun){
		self::$event->addTimer($time);
		self::$event_base ->loop();
		self::setInterval($time,$fun);
	}
	public static function add($time,$fun){
		self::$event_base=new EventBase;
		self::setTimeOut($time,$fun);
		self::setInterval($time,$fun);
	}
}

?>