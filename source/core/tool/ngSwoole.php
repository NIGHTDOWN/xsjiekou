<?php

namespace ng169\tool;

use ng169\db\daoClass;
use Swoole\WebSocket\Server;

class ngSwoole
{

  public $http;
  public $ws;
  public $port;
  public $poolsize = 100;

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
    $this->http->set([
      'worker_num'               => 3,
      // 表示 10 秒遍历所有连接
      'heartbeat_check_interval' => 10,
      // 表示连接最大允许空闲的时间
      'heartbeat_idle_time'	   => 60,
  ]);
    // Bug 修复：添加 onMessage 回调处理函数
    $this->http->on('message', function ($ws, $frame) {
      $this->ws = $ws;
      echo "Received message: {$frame->data}\n";
      // 处理接收到的消息
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
              case 'active':
                $checkfd = $redata['data']; //通过发消息给；如果消息成功表示在线；不在表示不在线；
                if (!$ws->exist($checkfd)) {

                  $this->loginout($checkfd); // 下线处理
                } else {

                  $this->send($ws, $checkfd, $frame->data);
                  $this->send($ws, $frame->fd, $frame->data); //回复管理员在线
                }
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
                $this->getclientfds($touid, $frame->data);
                // echo "Received message: {$frame->data}\n";
                break;
              case 'shell':

                $touid = $redata['data']["touid"];
                if (!$touid) {
                  d("未知接收用户");
                }
                $this->getclientfds($touid, $frame->data);
                // echo "Received message: {$frame->data}\n";
                break;
              case 'event':

                $touid = $redata['data']["touid"];
                if (!$touid) {
                  d("未知接收用户");
                }
                $this->getclientfds($touid, $frame->data);
                break;
              case 'upfile':

                $touid = $redata['data']["touid"];
                if (!$touid) {
                  d("未知接收用户");
                }
                $this->getclientfds($touid, $frame->data);
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
    // PHP Warning:  Swoole\WebSocket\Server::push(): the connected client of connection[9] is not a websocket client or closed in /d/www/xsjiekou/source/core/tool/ngSwoole.php on line 178
    //捕获这个warn
    if (!$ws->exist($fd)) {
      d("用户已离线");
      return;
    }

    try {
      // 尝试向客户端发送数据
      $result =$ws->push($fd, $data);
    
      if ($result === false) {
        // 发送失败，可能客户端已断开连接
        d("发送消息失败，客户端可能已断开连接");
        $this->loginout($fd);
        // 从相关列表中移除该客户端
        if (isset($this->wsclient[$fd])) {
            unset($this->wsclient[$fd]);
        }
        if (isset($this->wsadmin[$fd])) {
            unset($this->wsadmin[$fd]);
        }
        if (isset($this->loginfd[$fd])) {
            unset($this->loginfd[$fd]);
        }
    }
    } catch (\Exception $e) {
      // 发送失败，可能客户端已断开连接
      d("发送消息失败，客户端可能已断开连接");
      $this->loginout($fd);
      // 从相关列表中移除该客户端
      if (isset($this->wsclient[$fd])) {
        unset($this->wsclient[$fd]);
      }
      if (isset($this->wsadmin[$fd])) {
        unset($this->wsadmin[$fd]);
      }
      if (isset($this->loginfd[$fd])) {
        unset($this->loginfd[$fd]);
      }
    }
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
    $this->dbPool = new \Swoole\Coroutine\Channel($this->poolsize); // 创建一个容量为10的Channel  (channel 是协程的一个重要特性，它可以实现协程之间的通信和同步操作)
    // 初始化数据库连接池
    go(function () {
      while (true) {
        $mysql = daoClass::getdbobj("main");
        if ($mysql) {
          $this->dbPool->push($mysql);
        } else {
          // 处理连接失败的情况
          d("数据库连接失败\n");
        }
        \Co::sleep(1); // 每隔1秒尝试创建一个新的数据库连接
      }
    });
  }

  // 获取数据库连接
  public function getDb()
  {
    $db = $this->dbPool->pop();
    if (!$db || !$this->checkDbConnection($db)) {
      // $this->releaseDb($db); // 释放无效的连接
      $db = daoClass::getdbobj("main");
    }
    return $db;
  }
  // 释放数据库连接
  public function releaseDb($mysql)
  {
    $this->dbPool->push($mysql);
  }
  private function checkDbConnection($db)
  {
    try {
      $db->query("SELECT 1");
      return true;
    } catch (\PDOException $e) {
      return false;
    }
  }
  //离线
  public function loginout($fd)
  {
    \go(function () use ($fd) {
      $db = $this->getDb();
      $user = M("modelsocket", "im")->loginout($db, $fd);
      if ($user && $user["type"] != 0) {
        $data = ["action" => "logout", "data" => $user];
        $this->getadminfds($fd, json_encode($data));
      }
      $this->releaseDb($db);
      $this->ws->close($fd);
    });
  }
  //管理员登入
  public function loginadmin($fd, $data)
  {
    \go(function () use ($fd, $data) {
      $db = $this->getDb();
      $user = M("modelsocket", "im")->loginadmin($db, $fd, $data);
      // 给所有管理员发消息
      $this->releaseDb($db);
    });
  }
  //用户登入
  public function login($fd, $data)
  {
    \go(function () use ($fd, $data) {
      $db = $this->getDb();
      $user = M("modelsocket", "im")->login($db, $fd, $data, $this->http);
      $this->loginuser($this->ws, $fd, $user['uname']);
      //上线通知管理员
      $data = ["action" => "login", "data" => $user];
      $this->getadminfds($fd, json_encode($data));
      $this->releaseDb($db);
    });
  }
  //给管理员发小消息
  public function getadminfds($fd, $data)
  {
    \go(function () use ($fd, $data) {
      $db = $this->getDb();
      $wsadmin = M("modelsocket", "im")->getadminfds($db);
      foreach ($wsadmin as $tfd) {
        // $ws->push($fd, $frame->data);
        $this->send($this->ws, $tfd, $data);
      }
      $this->releaseDb($db);
    });
  }
  //给用户发消息
  public function getclientfds($tuid, $data)
  {
    \go(function () use ($tuid, $data) {
      $db = $this->getDb();
      $wsadmin = M("modelsocket", "im")->getclientfds($db, $tuid);
      foreach ($wsadmin as $tfd) {

        $this->send($this->ws, $tfd, $data);
      }
      $this->releaseDb($db);
    });
  }
}
