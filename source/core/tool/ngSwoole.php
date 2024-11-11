<?php

namespace ng169\tool;


use Swoole\WebSocket\Server;

class ngSwoole
{

  public $http;
  public $ws;
  public $admin=[]; //管理员   id=>[fd1,fd2,fd3]
  public $client=[]; //用户    id=>[fd1,fd2,fd3]
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
      echo "Received message: {$frame->data}\n";
      d($ws);
      $ws->push($frame->fd, "Server: " . $frame->data);
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

    // $this->http->on('open', function ($ws, $request) {
    //   echo "Client: Connect\n";
    // });

    // $this->http->on('close', function ($ws, $fd) {
    //   unset($clients[$fd]);
    //   echo "Client: Close\n";
    // });

    $this->http->start();
  }
}
