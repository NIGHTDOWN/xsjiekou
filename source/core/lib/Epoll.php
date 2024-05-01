<?php
namespace ng169\lib;
use \Event;
use \EventBase;

class Epoll
{
    protected $poll;

    protected $events;

    public static $instance = null;

    const READ = \Event::READ | \Event::PERSIST;

    const WRITE = \Event::WRITE | \Event::PERSIST;

    public static function getInstance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
            self::$instance->poll = new EventBase;
        }

        return self::$instance;
    }
	public function __clone(){
		return false;
	}
    public function add($fd, $what, $cb, $arg = null)
    {
        switch ($what) {
            case self::READ:
                $event = new Event($this->poll, $fd, self::READ, $cb, $arg);
                break;
            case self::WRITE:
                $event = new Event($this->poll, $fd, self::WRITE, $cb, $arg);
                break;
            default:
                $event = new Event($this->poll, $fd, $what, $cb, $arg);
                break;
        }

        $event->add();
        $this->events[(int) $fd][$what] = $event;
    }

    public function del($fd, $what = 'all')
    {
        $events = $this->events[(int) $fd];
        if ($what == 'all') {
            foreach ($events as $event) {
                $event->free();
            }
        } else {
            if ($what != self::READ && $what != self::WRITE) {
                throw new \Exception('不存在的事件');
            }

            $events[$what]->free();
        }
    }

    public function run()
    {
        $this->poll->loop();
    }

    public function stop()
    {
        foreach ($this->events as $events) {
            foreach ($events as $event) {
                $event->free();
            }
        }
        $this->poll->stop();
    }
	public function time($second,$callback){
		$event = Event::timer(self::$event_base ,$callback,$second);
		$this->events[spl_object_hash($event)]=$event;
		//加参数可以理解为多少秒以后deal

		$event->addTimer($second);
		d(11);
	}
}