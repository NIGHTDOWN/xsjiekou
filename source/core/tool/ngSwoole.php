<?php

namespace ng169\tool;


use Swoole\WebSocket\Server;

class ngSwoole
{

  public $http;
  public $ws;
  public $admin = []; //管理员   id=>[fd1,fd2,fd3]
  public $client = []; //用户    id=>[fd1,fd2,fd3]
  public $loginfd = []; //用户    id=>[fd1,fd2,fd3]
  public function start($port)
  {

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


    // Bug 修复：添加 onMessage 回调处理函数
    $this->http->on('message', function ($ws, $frame) {
      // {"data":{"uid":1},"stype":1,"action":"loginadmin","fun":"","tid":1}
      $redata = json_decode($frame->data, true);
      if (!$redata) {
        d("非法数据" . $frame->data);
      } else {
        // 登录验证
        if (!in_array($frame->fd, $this->loginfd)) {
          if (isset($redata['action'])) {
            switch ($redata['action']) {
              case 'loginadmin':

                if (M("modelsocket", "im")->loginadmin($frame->fd, $redata['data']))
                  $this->loginfd[$frame->fd] = $frame->fd;
                break;
              case 'login':
                if (M("modelsocket", "im")->login($frame->fd, $redata['data']))
                  $this->loginfd[$frame->fd] = $frame->fd;

                break;
              default:
                //关闭连接
                $ws->close($frame->fd);
                break;;
            }
          }
        } else {
          //正常数据发送
          if (isset($redata['action'])) {
            switch ($redata['action']) {
              case 'msg':

              default:
                //关闭连接
                $ws->close($frame->fd);
                break;;
            }
          }
        }
        echo "Received message: {$frame->data}\n";
        // d($ws);
        $ws->push($frame->fd, "Server: " . $frame->data);
      }

      //恢复消息

    });
    $this->ws = $this->http->on('websocket', function ($ws, $frame) {
      static $clients = [];
      if ($frame->opcode == WEBSOCKET_OPCODE_TEXT) {
        if ($frame->data == 'ping') {
          $ws->push($frame->fd, 'pong');
          return;
        }
        if ($frame->fd == -1) {
          foreach ($clients as $fd) {
            $ws->push($fd, $frame->data);
          }
        } else {
          $clients[$frame->fd] = $frame->fd;
          $ws->push($frame->fd, 'Welcome to WebSocket chat room!');
          foreach ($clients as $fd) {
            if ($fd != $frame->fd) {
              $ws->push($fd, $frame->data);
            }
          }
        }
      }
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
    });

    $this->http->start();
  }
}
