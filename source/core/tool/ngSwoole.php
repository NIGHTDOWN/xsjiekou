<?php

namespace ng169\tool;


use Swoole\WebSocket\Server;

class ngSwoole
{

  public $http;
  public $ws;
  public $port;
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
                (M("modelsocket", "im")->loginadmin($frame->fd, $redata['data']));
                  $this->wsadmin[$frame->fd] = $frame->fd;
                  $this->loginfd[$frame->fd] = $frame->fd;
                
                break;
              case 'login':
                M("modelsocket", "im")->login($frame->fd, $redata['data']) ;
                  $this->loginfd[$frame->fd] = $frame->fd;
                  $this->wsclient[$frame->fd] = $frame->fd;
               
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
              case 'msg':
                //全部转发给admin用户
                // $userid=M("modelsocket", "im")->getuid();
                $wsadmin =   M("modelsocket", "im")->getadminfds();
                d($wsadmin);
                foreach ($wsadmin as $fd) {
                  $ws->push($fd, $frame->data);
                }
                break;
              case 'adminmsg':
                
                // $userid=M("modelsocket", "im")->getuid();
                $touid=$redata['data']["touid"];
                if(!$touid){
                  d("未知接收用户");
                }
                $wsclient =   M("modelsocket", "im")->getclientfds($touid);
               d($wsclient);
                foreach ($wsclient as $fd) {
                  //转发给对应用户
                  $ws->push($fd, $frame->data);
                }
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
        echo "Received message: {$frame->data}\n";
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
      M("modelsocket", "im")->loginout($fd);
    });
    $this->http->on('start', function ( $server) {
      echo "IM服务启动成功， 端口 {$this->port}\n";
    });
    $this->http->start();
    
  }
}
