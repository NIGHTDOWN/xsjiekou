<?php
require_once   dirname(dirname(__FILE__)) . "/clibase.php";



use ng169\Y;
use ng169\lib\Job;
use ng169\tool\File;

class phpjob extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 6;  //书籍语言
    public  $_bookdstdesc_int = 2; //书籍来源描述
    public  $_bookdstdesc = "修复重复"; //书籍来源描述
    public  $_domian = "https://www.sogou.com/websearch/api/getcity "; //书籍来源描述
    public  $debug = true;
    public  $wordrate = 3;  //计算字数的时候的倍数比列
    // -------------------app 破解获取的相关信息
    // 签名密钥盐

    // aes iv
    public $aesiv = "";
    // aes密钥
    public $aeskey = "";
    //用户token
    public $token = "";
    public $appneedinfo = [
        "version" => "1.3.5",
        "language" => "MS",
    ];
    public $cacheindex = 'phpjob_list';

    public function start()
    {


        $this->logstart(__FILE__);

        Job::add(10, function () {
            $data = $this->gettask();
            $this->listintime($data);
            d($data);
        });

        // Y::$cache->set($this->cacheindex, json_encode($this->ok));


        $this->logend(sizeof($this->ok), [], 1);

        d("任务结束");
    }
    public function apisign($api, $parem)
    {
        $this->head($this->appneedinfo);
        $data = $this->post($api, $parem);

        return $data;
    }
    public function listintime($list)
    {
        foreach ($list as $k => $v) {
            if ($v['hour'] == date('H')) {
                if ($v['doday'] != date('Ymd')) {
                    d('执行');
                    //执行
                    T('phptask')->update(['doday' => date('Ymd')], ['id' => $v['id']]);
                    unset($list[$k]);
                    $this->dotask($v);
                    //更新数据库
                } else {
                    unset($list[$k]);
                    //更新缓存
                }
            }
        }
        Y::$cache->set($this->cacheindex, $list, strtotime('+1 day') - time());
    }
    public function dotask($taskinfo)
    {
        $shell = 'php  ' . $taskinfo['execfile'];
        // exec($shell . " > /dev/null &");
        $this->execInBackground($shell);
    }
    public function gettask()
    {
        list($bool, $data) = Y::$cache->get($this->cacheindex);
        if ($bool) {
            // d('cache');
            return $data;
        } else {
            $data = T('phptask')->get_all(['flag' => 0]);
            // d('db');
            Y::$cache->set($this->cacheindex, $data, G_DAY);
            return $data;
        }
    }
    public function clear()
    {
        Y::$cache->set($this->cacheindex, null, 0);
    }
    public function crontab()
    {
        //列出数据库所有任务
        //生成crontab 记录
        $data = T('phptask')->get_all(['flag' => 0]);

        $string = '';
        foreach ($data as $key => $value) {
            # code...
            // * * * * * /bin/ls
            $string .= "0 {$value['hour']} * * * php " . $value['execfile'] . "\n";
        }
        File::writeFile(ROOT.'/task',$string);
        echo ROOT.'/task';
        echo "请使用 crtontab -u root 目录下的task文件\n";
        echo "请使用 crtontab -l 查看定时任务是否生效";
    }
    // 一些非不要类---------------------------------
    //初始化进程
    public function __construct()
    {

        parent::__construct(); //初始化帮助信息
        $gt = $this->getargv(['clear', 'proxy', 'url', 'crontab', 'max']);
        if ($gt['clear']) {
            $this->clear();
            die();
        }
        if ($gt['crontab']) {
            $this->crontab();
            die();
        }
    }



    public function help()
    {
        d('1、开启定时任务,clear=1,清空缓存');
        d('2、开启系统定时任务,crontab=1，目前只支持每日执行');
    }
    //重新排序书籍

}
$ob = new phpjob();


$ob->start();
