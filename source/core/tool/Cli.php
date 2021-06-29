<?php


namespace ng169\tool;

use ng169\Y;

checktop();

declare(ticks=1);
//注册子进程退出时调用的函数。SIGCHLD：在一个进程终止或者停止时，将SIGCHLD信号发送给其父进程。

class Cli extends Y
{
    public
    function __construct()
    {

        $help = ($this->getargv(['h', 'help', 'thread']));
        if (isset($help['help'])) {
            $this->help();
            die();
        }
        if (isset($help['thread'])) {
            $this->threadcall();
            die();
        }
    }
    public function threadcall()
    {
    }
    //帮助入口
    public function help()
    {
        echo ("输入脚本加参数help=1显示帮助信息;\n子类覆盖help类，即可重置提示信息；\n getargv获取命令参数");
    }
    /**
     * $shortopts String  获取参数  如 h:f:  获取 -f value  -h value 
     * $longopts array  获取长参数  如 ['h:','f::','help']  获取 -f='value'  -h value  help=1
     */
    public function getargv($longopts = [])
    {
        // $shortopts = '';
        // global $argv;
        // d( $argv,1);
        // //短参数有问题，容易出错，舍弃
        // $options = getopt($shortopts, $longopts);
        // return $options;
        global $argv;

        $argvs = $argv;
        array_shift($argvs);

        $args = array();
        array_walk($argvs, function ($v, $k) use (&$args) {
            @list($key, $value) = @explode('=', $v);
            $args[$key] = $value;
        });

        if (sizeof($longopts) == 0) {
            return $args;
        } else {
            $ret = [];

            foreach ($longopts as $k => $v) {

                if (isset($args[$v])) {
                    $ret[$v] = $args[$v];
                }
            }

            return $ret;
        }
    }
    //cli获取交互输入
    public function getin($desc)
    {
        echo $desc . "：";
        $data = (fgets(STDIN));
        return $data;
    }
















    public static $maxChildPro = 8;

    //当前的子进程数量
    public static $curChildPro = 0;

    //当子进程退出时，会触发该函数,当前子进程数-1
    public  function  sig_handler($sig)
    {
        Cli::$curChildPro;
        switch ($sig) {
            case SIGCHLD:
                echo 'SIGCHLD', PHP_EOL;
                Cli::$curChildPro--;
                break;
        }
    }

    //配合pcntl_signal使用，简单的说，是为了让系统产生时间云，让信号捕捉函数能够捕捉到信号量

    /**
     * $call回调函数
     * $args 参数数组，fork，自动从里面取参数，然后调用回调函数
     */
    public  function clifork($call, $args)
    {
        pcntl_signal(SIGCHLD, [$this, "sig_handler"]);

        while (sizeof($args)) {
            Cli::$curChildPro++;
            $pid = pcntl_fork();
            if ($pid) {
                //父进程运行代码,达到上限时父进程阻塞等待任一子进程退出后while循环继续
                if (Cli::$curChildPro >=  Cli::$maxChildPro) {
                    pcntl_wait($status);
                }
            } else {
                //子进程运行代码
                array_pop($args);
                d($args);
                // $s = rand(2, 6);
                // sleep($s);
                // echo "child sleep $s second quit", PHP_EOL;
                exit;
            }
        }
    }
    public function checkpcntl()
    {
        if (function_exists('pcntl_signal')) {
            return true;
        } else {
            p('pcntl组件不存在');
            return false;
        }
    }
}
