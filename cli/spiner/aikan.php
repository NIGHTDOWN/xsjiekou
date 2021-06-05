<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once   dirname(dirname(__FILE__)) . "/clibase.php";

// use \ng169\cli\Clibase;

use ng169\Y;

class Aikan extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 0;  //书籍语言
    public  $_bookdstdesc_int = 0; //书籍来源描述
    public  $_bookdstdesc = "th_aikan"; //书籍来源描述
    public  $_domian = "https://apiv1.aikoversea.com"; //书籍来源描述
    public  $debug = true;
    public  $wordrate = 1;  //计算字数的时候的倍数比列
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
        // "version" => "1.3.5",
        // "language" => "MS",
    ];
    //远程完结状态值
    public $update_status_end_val = 1;
    //免费状态值
    public $is_un_free_val = 1;
    //一些临时数据，无需变动
    public $upinfo = [];
    public $upcount = 0;
    public $tokens = [];
    public $rmbookid = [];

    public $last = 0;
    public $lastbid;
    public $loop = [];
    public function start()
    {
        // $page = 10;
        // $i = 0;
        $cachename = date('Ymdhis') . 'obj';
        $this->thinit();


        $this->head([
            'devicetoken: b511a21f-9d7d-43be-b9a3-8bec1feda8de',
            'token: ',
            'deviceversion: 5.1.1',
            'deviceos: google Pixel 2',
            'apiSign: 52010b153f1179e7c92967a09fa90c00',
            'version: 1.4.1',
            'timestamp: 1594456217551',
            'uid: ',
            'devicetype: android',
            'apiKey: 9b4af02fddc12d2a38e2deae747beff0',
        ]);
        $i = 1007;

        $page = 1160;
        $this->logstart(__FILE__);
        $this->thcacheobj($cachename);
        //因为爱看的书籍又隐藏，所以直接轮询
        for ($i; $i <= $page; $i++) {
            $pag = $this->getbookdetail($i);
        }
        $this->logend($this->upcount, $this->upinfo, sizeof($this->rmbookid));
        $this->thcache($cachename);
        $this->thstart(__FILE__, $cachename);
      
        d("任务结束");
    }
    // 获取远程小说列表，根据实际情况修改fun
    public function getbooklist($page)
    {

        $post = [
            "page" => $page,
        ];
        $api = "/api/book/new";
        $datatmp = $this->apisign($api,  $post);
        //返回数据里面数据id字段
        $remote_bookarr_id = "book_id";
        list($status, $data) = $this->getdata($datatmp);

        if (!$status) {
            $this->debuginfo("列表中断" . $datatmp);
            return false;
        }
        d("远程拉取小说数量" . sizeof($data));
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

        return $page;
    }
    // 获取远程小说详情，根据实际情况修改fun
    public function getbookdetail($remotebookid)
    {
        if (in_array($remotebookid, $this->rmbookid)) {
            //这本书籍已经拉取过了，不要重复拉取
            return false;
        } else {
            array_push($this->rmbookid, $remotebookid);
        }
        if($this->isend($remotebookid)){
            d('本地完结'.$remotebookid);
            return false; 
        }
        $api = "/api/book/get_bookDetail";
        $id = $remotebookid;
        $datas = $this->apisign($api, [
            "book_id" => $id,
            // "type" => "1",
            // "token" => $this->token
        ]);

        //第三方内容中对应与本数据库字段对应
        $refield = [
            "bookname" => "other_name",
            "desc" => "desc",
            "update_status" => "update_status",
            "wordnum" => "wordnum",
            "section" => "update_section",
            "bpic" => "bpic",
            "fid" => "book_id",
        ];
        //更新状态

        list($statu, $data) = $this->getdata($datas, ["code", "result.data"]);

        if ($data) {
            $this->insertdetail($data, $refield);
        } else {
            $this->debuginfo("详情原因" . $datas);
        }
    }

    public $field = [
        "title" => "title",
        "isfree" => "isfree",
        "secid" => "section_id",
        "secnum" => "secnum",
    ];
    // 获取远程章节列表，根据实际情况修改fun
    public function getseclist($id, $dbid)
    {
        $api = "/api/book/get_section";
        $data = ["book_id" => $id,];
        //远程与本地字段对应
      
        $datas = $this->apisign($api, $data);

        //更新字数
        //更新状态
        list($s, $data) = $this->getdata($datas);

        if ($data) {
            //取得章节列表，对比现有章节数量相同就跳出
            return  $this->section_asyn($id, $dbid, $data, $this->field);
        } else {
            $this->debuginfo("章节中断" . $datas);
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
        // if (!$data) {
        //     $data = $this->unlock($remote_book_id, $remote_sec_id, $remote_sec_num);
        // }
        // //密文解密
        // if ($data) {
        //     // 参数 rondom+bid+cid+字符串“com.internationalization.novel”   MD516位小写 就是解密key
        //     return $this->decode($bid, $sid, $data);
        // } else {
        //     d("$remote_book_id, $remote_sec_id, $remote_sec_num" . "内容拉取失败");
        // }
        return $data;
    }
    // 获取远程章节内容，根据实际情况修改fun
    //获取远程文章内容接口
    public function getremoc($remote_book_id, $remote_sec_id, $remote_sec_num)
    {


        $api = "/api/book/get_wap_content";
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        $data = [
            "section_id" =>  $sid,
            "book_id" => $bid,
        ];
        $datas = $this->apisign($api, $data);
        list($s, $data) = $this->getdata($datas, ['code', 'result.sec_content']);

        if ($data) {
            return $data;
            // return ["key" => $key, "data" => $data];
        } else {
            d("中断原因" . $datas);
            // $this->debuginfo("中断原因" . $datas);

            //章节内容拉取次数
            if (isset($this->loop[$bid . "_" . $sid])) {
                $this->loop[$bid . "_" . $sid] = $this->loop[$bid . "_" . $sid] + 1;
            } else {
                $this->loop[$bid . "_" . $sid] = 1;
            }
        }
        return false;
    }
    //解锁接口
    public function unlock($remote_book_id, $remote_sec_id, $remote_sec_num)
    {
        $api = "/axahq/book/rewardChapter";
        $bid = $remote_book_id;
        $sid = $remote_sec_num;
        $data = [
            "token" => $this->token,
            "cid" =>  $sid,
            "bid" => $bid,
        ];
        $data2 = $this->apisign($api, $data);
        //这里取状态，解锁状态成功就再次拉取内容
        list($s, $data) =  $this->getdata($data2, ["status", "data"], 1);
        if ($s) {
            $data = $this->getremoc($remote_book_id, $remote_sec_id, $remote_sec_num);

            //解锁成功拉取因为 各种原因失败，所以再次尝试
            if (!$data) {
                $data = $this->getremoc($remote_book_id, $remote_sec_id, $remote_sec_num);
            }
            return $data;
        } {
            if ($this->loop[$bid . "_" . $sid] > 2) {
                //d三次失败才把错误原因入库
                $this->debuginfo("解锁中断" . $data2);
            }
            // d($data2, 1);
            //避免死循环
            if ($this->loop[$bid . "_" . $sid] < 3) {
                //更换token继续拉取
                $this->reg();
                return $this->unlock($remote_book_id, $remote_sec_id, $remote_sec_num);
            } else {
                $this->debuginfo("$remote_book_id, $remote_sec_id, $remote_sec_num" . "尝试三次解锁失败");
            }
            //在回调此函数
        }
        $this->debuginfo("$remote_book_id, $remote_sec_id, $remote_sec_num" . "内容拉取失败" . $data2);
        return false;
    }
    //注册接口
    public function reg()
    {
        $num = $this->getgnum();
        $api = "/axahq/User/otherLogin";
        $id = $num . $num . $num;
        $datas = $this->apisign($api, [
            "tokenId" => $id,
            "origin" => "android",
            "time" => time() . rand(000, 999),
            "type" => "google",
            "username" => $id . "@gmail.com"
        ]);
        list($status, $data) = $this->getdata($datas, ["status", "data"], 1);
        if ($status) {
            $this->token = $data["token"];
            return $this->token;
        } else {
            $this->debuginfo("注册中断" . $datas);
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
        $this->head([
            'devicetoken: b511a21f-9d7d-43be-b9a3-8bec1feda8de',
            'token: ',
            'deviceversion: 5.1.1',
            'deviceos: google Pixel 2',
            'apiSign: 52010b153f1179e7c92967a09fa90c00',
            'version: 1.4.1',
            'timestamp: 1594456217551',
            'uid: ',
            'devicetype: android',
            'apiKey: 9b4af02fddc12d2a38e2deae747beff0',
        ]);
        $data = $this->post($api, $parem);
        return $data;
    }
    //签名类返回签名值
    public function sign($api, $data)
    {
        ksort($data);
        $signstr = $api;
        foreach ($data as $key => $value) {
            # code...
            $signstr .= $key . "=" . $value;
        }
        $signstr =  $signstr . $this->code;
        $sign = md5($signstr);
        return $sign;
    }

    //解密类，返回明文
    public function decode($bid, $sid, $data)
    {
        $key = $data["key"] . $bid . $sid . "com.internationalization.novel";
        $key = md5($key);
        $key = substr($key, 8, 16);
        $data = $this->aes_cbc_nopadding($data["data"]["content"], $key, $data["data"]["encryption"]);
        return $data;
    }

    //接口值判断类，$field[0]判断索引，$field[1]需要返回的摄影,$field[0] ==$value 返回treu
    public function getdata($data, $field = ["code", "result"], $value = 1)
    {
        return  $this->check($data, $field, $value);
    }

    // 一些非不要类---------------------------------
    public function getgnum()
    {
        global $tokens;
        $num = rand(0000000, 9999999);
        if (in_array($num, $this->tokens)) {
            return $this->getgnum();
        } else {
            array_push($this->tokens, $num);
            return $num;
        }
    }
    //初始化进程
    public function __construct()
    {
        parent::__construct(); //初始化帮助信息
        $this->setdomain($this->_domian);
        $this->setinfo($this->_booktype, $this->_booklang, $this->_bookdstdesc_int, $this->_bookdstdesc);
        $this->loaddb($this->booktype, $this->booklang);
        $this->logstart(__FILE__);
    }
    //调试类
    // public function debuginfo($info)
    // {
    //     $this->logerror($info);
    //     if ($this->debug) {
    //         d($info, null, null, 1);
    //     }
    // }
    //计算章节字数
    public function calcsecnum($content)
    {
        $num = intval(strlen($content) / $this->wordrate);
        return $num;
    }


    //
    public function setwordrate($int)
    {
        $this->wordrate = $int;
    }
}
$ob = new Aikan();
// $ob->reg();
//ms
$ob->start();

// $ob->getbookdetail(1051);
