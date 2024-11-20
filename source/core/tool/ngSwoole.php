<?php

namespace ng169\tool;

use ng169\db\daoClass;
use Swoole\WebSocket\Server;

class ngSwoole
{

  public $http;
  public $ws;
  public $port;

  public $dbPool; //数据库连接池
  public $channel; //Channel
  public $wsadmin = []; //管理员   id=>[fd1,fd2,fd3]
  public $wsclient = []; //用户    id=>[fd1,fd2,fd3]
  public $loginfd = []; //用户    id=>[fd1,fd2,fd3]
  public function start($port)
  {
    $this->port = $port;

    $this->http = new \Swoole\WebSocket\Server('0.0.0.0', $port);
    $this->http->on('request', function ($request, $response) {
      $response->end("Welcome to WebSocket chat room!");
    });
    // 设置跨域头部，允许所有来源
    $this->http->on('request', function ($request,  $response) {
      $response->header('Access-Control-Allow-Origin', '*');
      $response->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
      $response->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS');
      if ($request->server['request_uri'] == '/ws') {
        // 如果是 WebSocket 握手请求，不做任何响应，因为 Swoole 会自动处理
        return;
      }
      $response->end("Welcome to WebSocket chat room!");
    });
    // 初始化数据库连接池和Channel
    $this->initPoolAndChannel();

    // Bug 修复：添加 onMessage 回调处理函数
    $this->http->on('message', function ($ws, $frame) {
    $this->ws=$ws;
      $redata = json_decode($frame->data, true);
      if (!$redata) {
        d("非法数据" . $frame->data);
      } else {
        // 登录验证
        if (!in_array($frame->fd, $this->loginfd)) {
          if (isset($redata['action'])) {
            switch ($redata['action']) {
              case 'loginadmin':
                $this->wsadmin[$frame->fd] = $frame->fd;
                $this->loginfd[$frame->fd] = $frame->fd;
                ($this->loginadmin($frame->fd, $redata['data']));
                break;
              case 'login':
                $this->loginfd[$frame->fd] = $frame->fd;
                $this->wsclient[$frame->fd] = $frame->fd;
                $this->login($frame->fd, $redata['data']);
                break;
              case 'heartbeat':
                break;
              default:
                //关闭连接fsdfdsf
                // $ws->close($frame->fd);
                break;
            }
          }
        } else {
          //正常数据发送
          if (isset($redata['action'])) {
            switch ($redata['action']) {
              case 'login':
                //匿名重新登入
                $this->loginfd[$frame->fd] = $frame->fd;
                $this->wsclient[$frame->fd] = $frame->fd;
                $this->login($frame->fd, $redata['data']);
                break;
              case 'msg':
                //全部转发给admin用户
                $this->getadminfds($frame->fd, $frame->data);
              
                // echo "Received message: {$frame->data}\n";
                break;
              case 'adminmsg':

             
                $touid = $redata['data']["touid"];
                if (!$touid) {
                  d("未知接收用户");
                }
                $this->getclientfds($touid,$frame->data);
                // echo "Received message: {$frame->data}\n";
                break;
              case 'shell':
               
                $touid = $redata['data']["touid"];
                if (!$touid) {
                  d("未知接收用户");
                }
                $this->getclientfds($touid,$frame->data);
                // echo "Received message: {$frame->data}\n";
                break;
              case 'event':
              
                $touid = $redata['data']["touid"];
                if (!$touid) {
                  d("未知接收用户");
                }
                $this->getclientfds($touid,$frame->data);
                break;
              case 'upfile':
              
                $touid = $redata['data']["touid"];
                if (!$touid) {
                  d("未知接收用户");
                }
                $this->getclientfds($touid,$frame->data);
                break;
              case 'heartbeat':


                break;
              default:
                //关闭连接
                $ws->close($frame->fd);
                break;;
            }
          }
        }

        // d($ws);
        // $ws->push($frame->fd, "Server: " . $frame->data);
      }

      //恢复消息

    });

    //断开连接
    $this->http->on('close', function ($ws, $fd) {
      // 从客户端列表中删除断开连接的客户端
      d("断开连接");
      if (isset($this->client[$fd])) {
        unset($this->client[$fd]);
      }
      if (isset($this->admin[$fd])) {
        unset($this->admin[$fd]);
      }
      if (isset($this->loginfd[$fd])) {
        unset($this->loginfd[$fd]);
      }

      $this->loginout($fd);
    });
    $this->http->on('start', function ($server) {
      echo "IM服务启动成功， 端口 {$this->port}\n";
    });
    $this->http->start();
  }
  function send($ws, $fd, $data)
  {
    $ws->push($fd, $data);
  }
  function loginuser($ws, $fd, $uid)
  {
    $data = [
      "action" => "login",
      "data" => $uid,
    ];
    $ws->push($fd, json_encode($data));
  }
  private function initPoolAndChannel()
  {
    $this->dbPool = new \Swoole\Coroutine\Channel(10); // 创建一个容量为10的Channel
    $this->channel = new \Swoole\Coroutine\Channel(10); // 创建一个容量为10的Channel

    // 初始化数据库连接池
    go(function () {
      while (true) {
        $mysql = daoClass::getdbobj("main");
        if ($mysql) {
          $this->dbPool->push($mysql);
        } else {
          // 处理连接失败的情况
          echo "数据库连接失败\n";
        }
        \Co::sleep(1); // 每隔1秒尝试创建一个新的数据库连接
      }
    });
  }

  // 获取数据库连接
  public function getDb()
  {
    return $this->dbPool->pop();
  }

  // 释放数据库连接
  public function releaseDb($mysql)
  {
    $this->dbPool->push($mysql);
  }
  public function loginout($fd){
    \go(function () use ($fd) {
      $db = $this->getDb();
      $user = M("modelsocket", "im")->loginout($db);
    });
  }
  public function loginadmin($fd, $data){
    \go(function () use ($fd, $data) {
      $db = $this->getDb();
      $user = M("modelsocket", "im")->loginadmin($db,$fd, $data);
    });
  }
  public function login($fd, $data){
    \go(function () use ($fd, $data) {
      $db = $this->getDb();
      $uid = M("modelsocket", "im")->login($db,$fd, $data,$this->http);
      $this->loginuser($this->ws, $fd, $uid);
    });
  }
  public function getadminfds($fd, $data){
    \go(function () use ($fd, $data) {
      $db = $this->getDb();
      $wsadmin = M("modelsocket", "im")->getadminfds($db);
      foreach ($wsadmin as $tfd) {
        // $ws->push($fd, $frame->data);
        $this->send($this->ws, $tfd, $data);
      }
    });
  }
  public function getclientfds($tuid, $data){
    \go(function () use ($tuid, $data) {
      $db = $this->getDb();
      $wsadmin = M("modelsocket", "im")->getclientfds($tuid);
      foreach ($wsadmin as $tfd) {
        // $ws->push($fd, $frame->data);
        $this->send($this->ws, $tfd, $data);
      }
    });
  }
}
