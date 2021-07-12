<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once   dirname(dirname(dirname(__FILE__))) . "/clibase.php";

// use \ng169\cli\Clibase;

use ng169\Y;

class Sphinovel extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 0;  //书籍语言
    public  $_bookdstdesc_int = 3; //书籍来源描述
    public  $_bookdstdesc = "th_hinovel"; //书籍来源描述
    public  $_domian = "https://thapi.hinovelasia.com"; //书籍来源描述
    public  $debug = true;
    public  $wordrate = 16;  //计算字数的时候的倍数比列
    public $in_rmote_db = false;
    // -------------------app 破解获取的相关信息
    // 签名密钥盐
    public $code = "7d83d58f98cf9059b8468270f639d1c7";
    // aes iv
    public $aesiv = "";
    // aes密钥
    public $aeskey = "";
    //用户token
    public $token = "942xty8z-42xt-2xty-xty8-42xtxty8ty8z";
    //远程完结状态值
    public $update_status_end_val = 1;
    //免费状态值
    public $is_un_free_val = 1;
    public $appneedinfo = [
        'appVersion' => '3.1.7',
        'phoneType' => 'samsung',
        'utc' => '9',
        'sign' => 'fa877648fc01546bdef768d40a76d11b',
        'userToken' => '',
        'osVersion' => '9',
        'appType' => 'android',
        'phoneBrand' => 'a02xx',
        'osType' => '1',
        'osUuid' => '942xty8z-42xt-2xty-xty8-42xtxty8ty8z',
        'lang' => 'th',
        'phoneOsVersion' => '10',
        'timestamp' => '1685541524',
    ];
    public $appneedinfo2 = [
        "preference" =>  1,
        "is_r18" => "0",
        "column_id" => "16",
    ];
    //一些临时数据，无需变动
    public $upinfo = [];
    public $upcount = 0;
    public $tokens = [];
    public $rmbookid = [];

    public $last = 0;
    public $lastbid;
    public $loop = [];
    public $pid = 'TH10T40';
    public function start()
    {
        $this->autoproxy();
        $cachename = date('Ymdhis') . 'obj';
        $this->thinit();
        $page = 100;

        $i = 0;
        $this->logstart(__FILE__);
        $this->thcacheobj($cachename);
        if (!$this->get_th_listcache()) {


            for ($i; $i <= $page; $i++) {
                $size = $this->getbooklist($i);
                if (!$size) {
                    //分页已经没东西了，直接退出
                    break;
                }
            }
            $this->set_th_listcache();
        }


        $this->logend($this->upcount, $this->upinfo, sizeof($this->rmbookid));
        $this->thcache($cachename);
        $this->thstart(__FILE__, $cachename);


        d("任务结束");
    }
    //获取列表
    //拆分列表
    //对象存入缓存，
    //shell 回调新线程




    // 获取远程小说列表，根据实际情况修改fun
    public function getbooklist($page)
    {
        // preference=1&is_r18=0&column_id=16&page=2
        $post = [
            "page" =>  $page,
            // "preference" =>  1,
            // "is_r18" => "0",
            // "column_id" => "16",

        ];
        $post = array_merge($post, $this->appneedinfo2);
        $api = "/api/v3.discover/getColumnBooks";
        $datatmp = $this->apisign($api,  $post);

        //返回数据里面数据id字段
        $remote_bookarr_id = "book_id";
        list($status, $data) = $this->getdata($datatmp);

        if (!$status) {
            $this->debuginfo("列表中断", $datatmp);
            return false;
        }
        d("远程拉取小说数量" . sizeof($data));
        if (is_array($data) && sizeof($data) > 0) {
            foreach ($data  as $book) {
                if ($this->isthread) {
                    //压入数组
                    // if (!in_array($book[$remote_bookarr_id], $this->thred_books)) {
                    //     array_push($this->thred_books, $book[$remote_bookarr_id]);
                    // }
                    $this->thpush($book[$remote_bookarr_id]);
                } else {
                    $this->getbookdetail($book[$remote_bookarr_id]);
                }
            }
        }
        return sizeof($data);
    }
    // 获取远程小说详情，根据实际情况修改fun
    public function getbookdetail($remotebookid)
    {
        if (in_array($remotebookid, $this->rmbookid)) {
            //这本书籍已经拉取过了，不要重复拉取
            return false;
        } else {



            array_push($this->rmbookid, $remotebookid);
            //id小于已有id多少的直接跳过
            // if ($this->rmbookid < 1145) {
            //     return false;
            // }
        }
        if ($this->isend($remotebookid)) {
            d('本地完结' . $remotebookid);
            return false;
        }
        $api = "/api/book/detail";
        $id = $remotebookid;
        $datas = $this->apisign($api, [
            "book_id" => $id,
            // "type" => "1",
            // "token" => $this->token
        ]);
        //第三方内容中对应与本数据库字段对应
        $refield = [
            "bookname" => "book_name",
            "desc" => "book_desc",
            "update_status" => "update_status",
            "wordnum" => "word_num",
            "section" => "chapter_num", //章节数量没在详情里面传入
            "bpic" => "book_pic",
            "fid" => "book_id",
        ];
        //更新状态
        list($statu, $data) = $this->getdata($datas);
        if ($data) {
            $this->insertdetail($data, $refield);
        } else {
            $this->debuginfo("详情原因", $data);
        }
    }

    public $field = [
        "title" => "title",
        "isfree" => "is_pay",
        "secid" => "section_id",
        'secnum' => 'word_num'
    ];
    // 获取远程章节列表，根据实际情况修改fun
    public function getseclist($id, $dbid)
    {
        $api = "/api/book/sectionList";
        $data = ["book_id" => $id];
        //远程与本地字段对应

        $datas = $this->apisign($api, $data);
        //更新字数
        //更新状态
        list($s, $data) = $this->getdata($datas);
        if ($data) {
            //取得章节列表，对比现有章节数量相同就跳出
            //必须return 不然无法统计
            return   $this->section_asyn($id, $dbid, $data, $this->field);
        } else {
            $this->debuginfo("章节中断", $datas);
        }
        return false;
    }

    // 获取远程章节内容，根据实际情况修改fun
    //传入远程小说id，章节id，章节序号
    public function getcontent($remote_book_id, $remote_sec_id, $remote_sec_num)
    {
        $bid = $remote_book_id;
        $sid = $remote_sec_num;
        //这里是密文拉取
        $data = $this->getremoc($remote_book_id, $remote_sec_id, $remote_sec_num);
        if (!$data) {
            $data = $this->unlock($remote_book_id, $remote_sec_id, $remote_sec_num);
        }
        //密文解密
        if ($data) {
            // 参数 rondom+bid+cid+字符串“com.internationalization.novel”   MD516位小写 就是解密key
            return $this->decode($bid, $sid, $data);
        } else {
            d("$remote_book_id, $remote_sec_id, $remote_sec_num" . "内容拉取失败");
        }
        return false;
    }
    // 获取远程章节内容，根据实际情况修改fun
    //获取远程文章内容接口
    public function getremoc($remote_book_id, $remote_sec_id, $remote_sec_num)
    {

        // $key = rand(000000, 999999);
        $api = "/api/book/sectionContent";
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        $data = [
            // "token" => $this->token,
            "section_id" =>  $sid,
            // "bid" => $bid,
            // "random" => $key
        ];
        $datas = $this->apisign($api, $data);
        list($s, $data) = $this->getdata($datas);

        if ($data) {

            return $data;
        } else {
            d("中断原因" . $datas);
            // $this->debuginfo("中断原因" . $datas);

            //章节内容拉取次数

        }
        return false;
    }
    //广告任务
    public function task_ad()
    {
        $api = "/api/task/taskReadAdvert";

        $data = [
            "task_type" => 5,
            "local_utc" =>  8,
            "local_time" => time(),
        ];
        $data2 = $this->apisign($api, $data);
        //这里取状态，解锁状态成功就再次拉取内容
        list($s, $data) =  $this->getdata($data2);
        // sleep(1);
    }
    //签到任务
    public function task_sign()
    {
        $api = "/api/sign/userSign";

        $data = [
            "task_type" => 1,
            "local_utc" =>  8,
            "local_time" => time(),
        ];
        $data2 = $this->apisign($api, $data);
        //这里取状态，解锁状态成功就再次拉取内容
        list($s, $data) =  $this->getdata($data2);
    }

    //邀请任务
    public function task_invite()
    {
        $oldback = $this->appneedinfo;
        $num = $this->getgnum();
        $api = "/api/login/login";
        $ids = [
            $num,
            substr($num, 1, 4),
            substr($num, 2, 4),
            substr($num, 4, 4),
            substr($num, 1, 4) . substr($num, 3, 4) . substr($num, 4, 4)
        ];
        $id = implode('-', $ids);
        $this->appneedinfo['osUuid'] = $id;

        // $this->appneedinfo[]
        $datas = $this->apisign($api, [
            "read_like" => 1,
            "invite_code" => $this->pid,
            'openid' => $num,
            'nickname' => $num,
            'account_type' => 2,

            'email' => $num . '@gg.com'
        ]);
        // invite_code=THDDLT
        list($status, $data) = $this->getdata($datas);

        if ($status) {
            // $this->token = $data["token"];
            // $this->pid = $data["invite_code"];
            // return $this->token;
            $this->appneedinfo = $oldback;

            return true;
        } else {
            $this->debuginfo("邀请失败", $datas);
        }
    }
    public function task_invite1()
    {
        $oldback = $this->appneedinfo;
        $num = $this->getgnum();
        $api = "/api/login/temporary";
        $ids = [
            $num,
            substr($num, 1, 4),
            substr($num, 2, 4),
            substr($num, 4, 4),
            substr($num, 1, 4) . substr($num, 3, 4) . substr($num, 4, 4)
        ];
        $id = implode('-', $ids);
        $this->appneedinfo['osUuid'] = $id;

        // $this->appneedinfo[]
        $datas = $this->apisign($api, [
            "read_like" => 1,
            "invite_code" => $this->pid,
        ]);
        // invite_code=THDDLT
        list($status, $data) = $this->getdata($datas);

        if ($status) {
            // $this->token = $data["token"];
            // $this->pid = $data["invite_code"];
            // return $this->token;
            $this->appneedinfo = $oldback;

            return true;
        } else {
            $this->debuginfo("邀请失败", $datas);
        }
    }
    public function task_read()
    {
        $api = "/api/task/readBookTime";
        $data = [
            "task_type" => 6,
            "local_utc" =>  8,
            "local_time" => time(),
        ];
        $data2 = $this->apisign($api, $data);
        //这里取状态，解锁状态成功就再次拉取内容
        list($s, $data) =  $this->getdata($data2);
    }
    public function task_read2()
    {
        $api = "/api/task/readBookTime";
        $data = [
            "task_type" => 7,
            "local_utc" =>  8,
            "local_time" => time(),
        ];
        $data2 = $this->apisign($api, $data);
        //这里取状态，解锁状态成功就再次拉取内容
        list($s, $data) =  $this->getdata($data2);
    }
    public function getuserinfo()
    {
        $api = "/api/user/getUserInfo";
        $data = [
            // "task_type" => 6,
            // "local_utc" =>  8,
            // "local_time" => time(),
        ];
        $data2 = $this->apisign($api, $data);
        //这里取状态，解锁状态成功就再次拉取内容
        list($s, $data) =  $this->getdata($data2);
        // d($data);
    }
    //解锁接口
    public function unlock($remote_book_id, $remote_sec_id, $remote_sec_num)
    {



        // 任务金币模式
        //签到
        $this->task_sign();
        $this->task_read();
        $this->task_read2();
        //广告任务5次
        $this->task_ad();
        $this->task_ad();
        $this->task_ad();
        $this->task_ad();
        //邀请任务
        // $this->task_invite();
        $this->getuserinfo(); //金币加了；继续下一波


        // $this->task_ad();
        // $this->task_ad();
        // $this->task_ad();

        $api = "/api/user/unlock";
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        if (($this->loop[$bid . "_" . $sid])) {
            $this->loop[$bid . "_" . $sid] = $this->loop[$bid . "_" . $sid] + 1;
        } else {
            $this->loop[$bid . "_" . $sid] = 1;
        }
        $data = [
            "book_id" => $bid,
            "section_id" =>  $sid,
            "book_type" => 1,
            "is_free" => 1,
            "is_auto" => 0,
        ];
        $data2 = $this->apisign($api, $data);
        //这里取状态，解锁状态成功就再次拉取内容
        list($s, $data) =  $this->getdata($data2);
        if ($s) {
            $data = $this->getremoc($remote_book_id, $remote_sec_id, $remote_sec_num);

            //解锁成功拉取因为 各种原因失败，所以再次尝试
            // if (!$data) {

            //     $data = $this->getremoc($remote_book_id, $remote_sec_id, $remote_sec_num);
            // }
            return $data;
        } {
            // sleep(60);
            // $this->reg();
            // $this->task_invite(); //金币加了；继续下一波
            if ($this->loop[$bid . "_" . $sid] > 2) {
                //d三次失败才把错误原因入库,原因基本就是解锁满三次
                // $this->debuginfo("解锁中断" . $data2);
                // $this->reg();
            }
            // d($data2, 1);
            //避免死循环
            if ($this->loop[$bid . "_" . $sid] < 3) {
                //更换token继续拉取
                $this->reg();
                return $this->unlock($remote_book_id, $remote_sec_id, $remote_sec_num);
            } else {
                // $this->debuginfo("$remote_book_id, $remote_sec_id, $remote_sec_num" . "尝试三次解锁失败");
            }
            //在回调此函数
        }
        $this->debuginfo("$remote_book_id, $remote_sec_id, $remote_sec_num" . "内容拉取失败", $data2);
        return false;
    }
    //注册接口
    public function reg2()
    {
        $num = $this->getgnum();
        $api = "/api/login/login";
        $ids = [
            $num,
            substr($num, 1, 4),
            substr($num, 2, 4),
            substr($num, 3, 4),
            substr($num, 1, 4) . substr($num, 3, 4) . substr($num, 4, 4)
        ];
        $id = implode('-', $ids);
        $this->appneedinfo['osUuid'] = $id;

        // $this->appneedinfo[]
        $datas = $this->apisign($api, [
            "read_like" => 1,
            // "invite_code" => $this->pid,
            'openid' => $num,
            'nickname' => $num,
            'account_type' => 2,

            'email' => $num . '@gg.com'

        ]);
        // invite_code=THDDLT
        list($status, $data) = $this->getdata($datas);

        if ($status) {
            $this->token = $data["token"];
            $this->pid = $data["invite_code"];
            return $this->token;
        } else {
            $this->debuginfo("注册中断", $datas);
        }
    }
    public function reg()
    {
        //停顿一分钟，不然会识别恶意导致无法执行

        $num = $this->getgnum();
        $api = "/api/login/temporary";
        $ids = [
            $num,
            substr($num, 1, 4),
            substr($num, 2, 4),
            substr($num, 3, 4),
            substr($num, 1, 4) . substr($num, 3, 4) . substr($num, 4, 4)
        ];
        $id = implode('-', $ids);
        $this->appneedinfo['osUuid'] = $id;

        // $this->appneedinfo[]
        $datas = $this->apisign($api, [
            "read_like" => 1,
            // "invite_code" => $this->pid,

        ]);
        // d($datas);
        // invite_code=THDDLT
        list($status, $data) = $this->getdata($datas);

        if ($status) {
            $this->token = $data["token"];
            $this->pid = $data["invite_code"];
            return $this->token;
        } else {
            $this->appneedinfo['osUuid'] = $this->token;
            $this->debuginfo("注册中断", $datas);
        }
    }
    //***********************************工具性************************************** */
    //http请求入口，根据实际情况，把一些固定值写进去

    public function apisign($api, $parem)
    {
        // $token = $this->token;
        // $p = [
        //     "time" => time(),
        //     "rnumber" => Rand(1000, 9999),
        //     "token" => $token, //可变
        // ];
        // $parem = array_merge($parem, $p, $this->appneedinfo);
        // $parem["sign"] = $this->sign($api, $parem);



        
        // $this->setproxy('127.0.0.1', '8888');
        // $this->ip = '127.0.0.1';
        // $this->post = '8888';
        // $this->setproxy();
        $this->sign(null, null);
        $this->appneedinfo['userToken'] = $this->token ? $this->token : $this->appneedinfo['osUuid'];
        // $this->appneedinfo['osUuid'] = $this->appneedinfo['userToken'];
        $this->head($this->appneedinfo);

        $data = $this->post($api, $parem);
        return $data;
    }
    //签名类返回签名值
    public function sign($api, $data)
    {
        $secret = $this->code;
        $this->appneedinfo['timestamp'] = time();
        //获取所有请求的参数
        $AllPar['timestamp'] = $this->appneedinfo['timestamp'];
        $AllPar['lang'] = $this->appneedinfo['lang'];
        $AllPar['appType'] = $this->appneedinfo['appType'];
        $AllPar['appVersion'] = $this->appneedinfo['appVersion'];
        $AllPar['osType'] = $this->appneedinfo['osType'];
        $AllPar['osVersion'] = $this->appneedinfo['osVersion'];
        $AllPar['phoneBrand'] =  $this->appneedinfo['phoneBrand'];
        $AllPar['phoneType'] =  $this->appneedinfo['phoneType'];
        $AllPar['phoneOsVersion'] = $this->appneedinfo['phoneOsVersion'];
        $AllPar['osUuid'] = $this->appneedinfo['osUuid'];
        $AllPar = (array_filter($AllPar)); //根据键对数组进行升序排序
        ksort($AllPar);
        $hash_data = '';
        foreach ($AllPar as $k => $v) {
            $hash_data .= $k . "=" . ($v) . "&";
        }
        $_apiSign = hash_hmac('md5', $hash_data, $secret);
        $this->appneedinfo['sign'] = $_apiSign;
        return $_apiSign;
    }
    public function test()
    {
        $this->domian = 'http://xsapi.ng169.com';
        $data = $this->apisign('/api/index/run', null);
        d($data, 2);
    }
    //解密类，返回明文
    public function decode($bid, $sid, $data)
    {
        // $key = $data["key"] . $bid . $sid . "com.internationalization.novel";
        // $key = md5($key);
        // $key = substr($key, 8, 16);
        $key = '2cccfdc6be34423e';
        $iv = '2019919anyuekeji';
        $data = $this->aes_cbc_nopadding($data["section_content"], $key, $iv);
        return $data;
    }

    //接口值判断类，$field[0]判断索引，$field[1]需要返回的摄影,$field[0] ==$value 返回treu
    public function getdata($data, $field = ["result", "data"], $value = 1)
    {
        return  $this->check($data, $field, $value);
    }

    // 一些非不要类---------------------------------
    public function getgnum()
    {
        global $tokens;
        $num = $this->generate_password(8);
        if (in_array($num, $this->tokens)) {
            return $this->getgnum();
        } else {
            array_push($this->tokens, $num);
            return $num;
        }
    }
    public function generate_password($length = 8)
    {
        // 密码字符集，可任意添加你需要的字符 
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $chars = strtolower($chars);
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            // 这里提供两种字符获取方式 
            // 第一种是使用 substr 截取$chars中的任意一位字符； 
            // 第二种是取字符数组 $chars 的任意元素 
            // $password .= substr($chars, mt_rand(0, strlen($chars) – 1), 1); 
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }
    //初始化进程
    public function __construct()
    {
        parent::__construct(); //初始化帮助信息
        $this->initsp();
    }
    public function initsp()
    {
        $this->setdomain($this->_domian);
        $this->setinfo($this->_booktype, $this->_booklang, $this->_bookdstdesc_int, $this->_bookdstdesc);
        $this->loaddb($this->booktype, $this->booklang);
    }
    //
    public function setwordrate($int)
    {
        $this->wordrate = $int;
    }
}
// $ob = new sphinovel();
// $ob->reg();
// $ob->start();
