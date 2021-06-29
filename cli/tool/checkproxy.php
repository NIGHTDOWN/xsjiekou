<?php

/**统计缺失的章节，并且去掉重复章节 */




require_once   dirname(dirname(__FILE__)) . "/clibase.php";



use ng169\Y;

class checkproxy extends Clibase
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
    public $code = "c1774fb28759d14916a641f04df67bca";
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
    public $cacheindex = 'proxy_ok_list';
    //一些临时数据，无需变动
    public $list = [];

    public $fail = [];
    public $ok = [];
    public $showret = true;
    public $loop = 0;
    public function proxy()
    {
        $list = [
            // '197.248.30.125:80',
        ];
        if (!sizeof($list)) {
            $data = T('option')->set_where(['option_name' => 'wait_chek_proxy'])->get_one();
            $list = explode(',', $data['option_value']);
        }
        $this->list = $list;
        return $list;
    }
    public function start()
    {
        // Y::$cache->set($this->cacheindex, 1111);
        $this->logstart(__FILE__);
        $list = $this->proxy();
        if ($this->checkpcntl()) {
            $this->clifork([$this, 'do'], $list);
        }

        foreach ($list as $v => $b) {
            list($ip, $port) = $this->sb($b);
            if ($ip && $port) {
                $this->do($ip, $port);
            } else {
                p($b . '识别失败');
            }
        }
        // Y::$cache->set($this->cacheindex, json_encode($this->ok));
        $this->logend(sizeof($this->ok), ['ok' => $this->ok, 'fail' => $this->fail], sizeof($this->list));
        $gt = $this->getargv(['showret', 'proxy', 'url', 'showlast', 'max', 'update']);
        if (isset($gt['update'])) {
            $in = '';
            foreach ($this->ok as $v) {
                $in .=   array_values($v)[0] . ',';
            }
            T('option')->update(['option_value' => $in], ['option_name' => 'ok_proxy']);
        }
        p($this->ok);
        p("任务结束");
    }
    public function apisign($api, $parem)
    {
        $this->head($this->appneedinfo);
        $data = $this->post($api, $parem);
        return $data;
    }
    public function showlast()
    {
        list($bool, $data) = Y::$cache->get($this->cacheindex);
        if ($bool) {
            p($data);
        } else {
            p('上次无记录或者无可用代理');
        }
    }
    // 获取远程小说列表，根据实际情况修改fun
    public function do($ip, $port)
    {

        $this->loop++;
        $this->setproxy($ip, $port);
        global $Stime;
        $Stime = microtime(true);
        $data = $this->apisign($this->_domian, '');
        $ttl = debugtime();
        $ttl = $this->loop . '_' . $ttl;
        if ($data && $data != 'fail') {
            array_push($this->ok, [$ttl => "$ip $port"]);
            p("__ $ip:$port __" . "\n" . ' 成功');
        } else {
            array_push($this->fail, [$ttl => "$ip $port"]);
            p("__ $ip:$port __" . "\n" . ' 失败');
        }
        if ($this->showret) {
            p($data);
        }
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
        $gt = $this->getargv(['showret', 'proxy', 'url', 'showlast', 'max', 'update']);
        // if (isset($gt['type'])) {
        //     $this->_booktype = $gt['type'];
        // }
        if (isset($gt['url'])) {
            $this->_domian = $gt['url'];
        }
        if (isset($gt['showret'])) {
            $this->showret = $gt['showret'];
        }
        if (isset($gt['showlast'])) {
            $this->showlast();
            die();
        }
        if (isset($gt['proxy'])) {
            list($ip, $port) = $this->sb($gt['proxy']);
            if ($ip && $port) {
                $this->do($ip, $port);
            } else {
                p($gt['proxy'] . '识别失败');
            }
            die();
        }
    }



    public function help()
    {
        p('1、检查代理可用性,参数proxy 指定识别地址，url指定识别，showret是否显示请求返回内容,showlast是否显示上次成功记录；update更新可用代理信息;域名：例子php checkproxy.php proxy=192.168.2.106:8888 url=https://www.baidu.com showret=1');
    }
    //重新排序书籍

}
$ob = new checkproxy();


$ob->start();
