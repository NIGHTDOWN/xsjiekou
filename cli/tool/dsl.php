<?php

/**统计缺失的章节，并且去掉重复章节 */

namespace ng169\cli\tool;

require_once   dirname(dirname(__FILE__)) . "/clibase.php";


use ng169\Y;
use ng169\tool\File;
use \ng169\tool\Image;
use \ng169\cli\Clibase;
// dsl处理
class dsl extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 6;  //书籍语言
    public  $_bookdstdesc_int = 2; //书籍来源描述
    public  $_bookdstdesc = "把图片生成dsl地址"; //书籍来源描述
    public  $_domian = "https://www.sogou.com/websearch/api/getcity "; //书籍来源描述
    public  $debug = true;
    public  $path = '';  //计算字数的时候的倍数比列
    // -------------------app 破解获取的相关信息
    // 签名密钥盐
    public $code = "";
    // aes iv
    public $bookid = "";
    // aes密钥
    public $dbid = "";
    //用户token
    public $db = "";
    public $do = 0;
    public $appneedinfo = [
        "version" => "1.3.5",
        "language" => "MS",
    ];
    
    //一些临时数据，无需变动
    public $list = [];

    public $fail = [];
    public $ok = [];
    public $showret = true;
    public $loop = 1;

    public function start()
    {
        //获取列表书籍列表
        //取1000条分词

        $w = '';
        if ($this->bookid) {
            $w = [$this->dbid => $this->bookid];
        }

        for ($i = 0; $i < 500; $i++) {
            # code...
            $list = T($this->db)->set_field($this->dbid . ' as book_id,bpic_dsl,bpic')->set_limit([$i, 3000]);
            if ($w) {
                // 指定具体id书籍
                $list = $list->set_where($w);
            }
            // $list = $list->set_where(' ISNULL(bpic_dsl) ');
            // $list = $list->set_where(' bpic like "%webp-%" ');
            // $list = $list->set_where(' book_id>2000 ');
            $list = $list->get_all();

            if (sizeof($list) > 0) {
                if (strpos(PHP_OS, 'Linux')!== false && !$this->bookid) {
                    // 执行Linux命令
                    $this->nthread($list);
                } else {
                    $this->loop($list);
                }
            } else {
                break;
            }
        }
        d('执行完成');
    }
    public function nthread($booklist){
        $maxProcesses = 10; // 最多20个子进程
        $activeProcesses = 0;
        $pids = [];
        //吧$booklist拆分$maxProcesses等分
        $chunkSize = ceil(count($booklist) / $maxProcesses);
        $booklist = array_chunk($booklist, $chunkSize);
        foreach ($booklist as $books) {
            // 创建新的子进程
            $pid = pcntl_fork();
    
            if ($pid == -1) {
                // 创建子进程失败
                die('Could not fork');
            } elseif ($pid == 0) {
                // 子进程代码
                $this->loop($books);
                exit; // 子进程结束
            } else {
                // 父进程代码
                $pids[] = $pid;
                $activeProcesses++;
    
                // 限制并发进程数
                if ($activeProcesses >= $maxProcesses) {
                    // 等待子进程结束
                    $status = 0;
                    pcntl_wait($status);
                    $activeProcesses--;
                }
            }
        }
    
        // 等待所有子进程结束
        while ($activeProcesses > 0) {
            $status = 0;
            pcntl_wait($status);
            $activeProcesses--;
        }

    }
    public function loop($booklist)
    {
        if (!$booklist) {
            d('数据错误');
            return false;
        }
        foreach ($booklist as $book) {
           
            if ($book['bpic_dsl']) {
                return false;
            }
            $pic = $book['bpic'];

            // $reg = "/http[\S]*\.webp/";
            // preg_match_all($reg, $book['bpic'], $me);
            // $pic = $me[0][0];
            // d($pic);

            if (!$this->do) {
                // d($pic);
                $dsl = $this->getimg($pic, $book['book_id']);
// d($dsl,1);
                if (!$dsl) {
                    d($book['book_id'] . '失败');
                } else {
                    T($this->db)->update(['bpic_dsl' => $dsl], [$this->dbid => $book['book_id']]);
                    // d($dsl);
                }
            } else {
                $setdsl = "dsl://" . $this->db . '/' . $this->_booktype . "_" . $book['book_id'] . '.png';
                T($this->db)->update(['bpic_dsl' =>  $setdsl], [$this->dbid => $book['book_id']]);
            }
        }


        //执行拉取到本地操作
    }
    public function apisign($api, $parem)
    {
        $this->head($this->appneedinfo);
        $data = $this->post($api, $parem);
        return $data;
    }
    //抓图片
    public function getimg($img, $id)
    {
        $p = $this->path;
        $filename = $this->_booktype . '_' . $id . '.webp';
        $proxy=[];
        if($this->ip){
            $proxy=[
                'ip' => $this->ip,
                'port' => $this->port,
            ];
        }

        $file = Image::imgtolocalwebp($img, null, $filename, $p,$proxy);
        // d($file );
        $mock = 'dsl://' . $file[0];
        if ($file) {
            return $mock;
        }

        return null;
    }

    // 获取远程小说列表，根据实际情况修改fun
    public function do($ip, $port)
    {
        $this->proxylist;
    }

    public function sb($proxystr)
    {
        $rex = "/([\d]{1,3}\.[\d]{1,3}\.[\d]{1,3}\.[\d]{1,3})[\s,\:]*([\d]{2,5})/";
        preg_match_all($rex, $proxystr, $proxy);
        $ip = $proxy[1][0];
        $port = $proxy[2][0];
        return [$ip, $port];
    }

    // 一些非不要类---------------------------------

    //初始化进程
    public function __construct()
    {
        parent::__construct(); //初始化帮助信息
        $gt = $this->getargv(['type', 'do', 'bookid', 'path', 'max',"proxy"]);

        if (isset($gt['type'])) {
            $this->_booktype = $gt['type'];
        }
        if (isset($gt['bookid'])) {
            $this->bookid = $gt['bookid'];
        }
      

        if ($this->_booktype == 1) {
            $this->db = 'book';
            $this->dbid = $this->db . '_id';
        } else {
            $this->db = 'cartoon';
            $this->dbid = $this->db . '_id';
        }

        if (isset($gt['proxy'])) {
            $proxy = explode(':', $gt['proxy']);
            $this->ip = $proxy[0];
            $this->port = $proxy[1];
            p($this->ip . ':' . $this->port);
            return;
        }
        d($this->db);
        $this->path = '/soft/cp/cartoon_section/' . $this->db . '/';
        if ($this->_booktype == 1) {
        }
        if (isset($gt['path'])) {
            $this->path = $gt['path'];
        }

        if (isset($gt['do'])) {
            $this->do = $gt['do'];
            // die();
        }
    }



    public function help()
    {
        d('1、检查把图片抓取到本地生成dsl链接,参数type 指定书籍类型，bookid指定书籍id，path指定保存位置，do是否抓取完成上传到图床之后更新服务器短dsl链接；域名：例子php dsl.php type=1 proxy=127.0.0.1:8080');
    }
    //重新排序书籍

}
$ob = new dsl();


$ob->start();
