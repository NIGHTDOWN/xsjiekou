<?php

/**
 */

namespace ng169\cli;

require_once    "./sock/sockbase.php";

use ng169\lib\Socket;
use ng169\Y;
use ng169\cli\sock\sockbase;

im(API . "vendor/autoload.php");

use Workerman\Worker;
use PHPSocketIO\SocketIO;

define('GLOBAL_START', true);
$ims = []; //connectObj 客户端列表
//数据库连接池
class Im extends Clibase
{
    public function io()
    {
        $io = new SocketIO(4563);
        // $io->on('connect', function(){
        //     d('connect success');
        // });
        $io->on('connection', function ($socket) {
            $socket->addedUser = false;
            // when the client emits 'new message', this listens and executes
            $socket->on('new message', function ($data) use ($socket) {
                d($data);
                // we tell the client to execute 'new message'
                $socket->broadcast->emit('new message', array(
                    'username' => $socket->username,
                    'message' => $data
                ));
            });
            // when the client emits 'add user', this listens and executes
            $socket->on('add user', function ($username) use ($socket) {
                if ($socket->addedUser)
                    return;
                global $usernames, $numUsers;
                // we store the username in the socket session for this client
                $socket->username = $username;
                ++$numUsers;
                $socket->addedUser = true;
                $socket->emit('login', array(
                    'numUsers' => $numUsers
                ));
                // echo globally (all clients) that a person has connected
                $socket->broadcast->emit('user joined', array(
                    'username' => $socket->username,
                    'numUsers' => $numUsers
                ));
            });

            // when the client emits 'typing', we broadcast it to others
            $socket->on('typing', function () use ($socket) {
                $socket->broadcast->emit('typing', array(
                    'username' => $socket->username
                ));
            });

            // when the client emits 'stop typing', we broadcast it to others
            $socket->on('stop typing', function () use ($socket) {
                $socket->broadcast->emit('stop typing', array(
                    'username' => $socket->username
                ));
            });

            // when the user disconnects.. perform this
            $socket->on('disconnect', function () use ($socket) {
                global $usernames, $numUsers;
                if ($socket->addedUser) {
                    --$numUsers;
                    // echo globally that this client has left
                    $socket->broadcast->emit('user left', array(
                        'username' => $socket->username,
                        'numUsers' => $numUsers
                    ));
                }
            });
        });

        if (!defined('GLOBAL_START')) {
            Worker::runAll();
        }
    }
  
    public function init()
    {
        //请求链接
        // ws://127.0.0.1:2020/socket.io/?EIO=3&transport=websocket
        $this->io();
        // $this->web();
        Worker::runAll();
    }
}
//启动数据库连接池server
$a = new Im();
$a->init();
