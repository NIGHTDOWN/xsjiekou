<?php

namespace ng169\tool;

use Swoole\Http\Server;
use Swoole\WebSocket\Server;
use Swoole\Server\Port;

class ngSwoole
{

  public $http;
  public function start($port)
  {

    $this->http = new \Server('0.0.0.0', $port);
    $this->http->on('request', function ($request, $response) {
      $response->end("Welcome to WebSocket chat room!");
    });




    $ws = $this->http->on('websocket', function ($ws, $frame) {
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

    $this->http->on('open', function ($ws, $request) {
      echo "Client: Connect\n";
    });

    $this->http->on('close', function ($ws, $fd) {
      unset($clients[$fd]);
      echo "Client: Close\n";
    });

    $this->http->start();
  }
}
