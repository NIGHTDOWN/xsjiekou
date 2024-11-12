<?php

/**
 */

namespace ng169\tool;

require_once    "clibase.php";

im(TOOL . "ngSwoole.php");

class Im extends \ng169\cli\Clibase
{
    public $dovo;
    public $port = 1199;
    public function __construct()
    {
        parent::__construct(); //初始化帮助信息
        $gt = $this->getargv(['do', 'port',]);
        $this->dovo = $gt['do'];
        $this->port = $gt['port'] ?? $this->port;
    }
    public function start()
    {

        if (!$this->dovo) {
            $this->_start();
        } else {
            switch ($this->dovo) {
                case 'start':
                    $this->_start();
                    break;
                case 'stop':
                    $this->stop();
                    break;
                case 'reload':
                    $this->reload();
                    break;
                case 'status':
                    $this->status();
                    break;
            }
        }
    }

    private function _start()
    {
        $this->cleardb();
        $sw = new \ng169\tool\ngSwoole();
        $sw->start($this->port);
    }
    private function reload()
    {
        $this->stop();
        $this->_start();
    }
    private function status() {}
    private function cleardb() {
        T("sock_client")->update(["online"=>0]);
    }

    private function stop()
    {
        // 使用 shell_exec 执行命令，查找占用指定端口的进程 PID
        // 使用 shell_exec 执行命令，查找占用指定端口的进程 PID
    $command = "lsof -i :{$this->port} | grep LISTEN | awk '{print \$2}'";
    $output = shell_exec($command);
    // 解析输出，获取 PID
    $pids = explode(PHP_EOL, $output);
    // 遍历 PID 列表，尝试终止每个进程
    foreach ($pids as $pid) {
        if (!empty($pid)) {
            // 使用 kill 命令终止进程
            $killCommand = "kill -9 {$pid}";
            shell_exec($killCommand);
            echo "Killed process with PID: {$pid}\n";
        }
    }

    // 输出停止信息
    echo "Server stopped.\n";
        // $command = "netstat -ano | findstr :{$this->port}";
        // $output = shell_exec($command);

        // // 解析输出，获取 PID
        // preg_match_all('/\s+(\d+)\s+/', $output, $matches);
        // $pids = $matches[1];

        // // 遍历 PID 列表，尝试终止每个进程
        // foreach ($pids as $pid) {
        //     // 使用 taskkill 命令终止进程
        //     $killCommand = "taskkill /F /PID $pid";
        //     shell_exec($killCommand);
        //     echo "Killed process with PID: $pid\n";
        // }

        // // 输出停止信息
        // echo "Server stopped.\n";
    }
    //帮助doc参数stop ；start ；reload；restart ；status ；stop ；start ；reloa
    public function help()
    {
        d("接收参数do: start ；stop ；reload；status ；stop\n
      接收参数port: 端口\n
      ");
    }
}
//启动数据库连接池server

$ob = new Im();
$ob->start();
