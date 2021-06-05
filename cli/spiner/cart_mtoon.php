<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once   dirname(dirname(__FILE__)) . "/clibase.php";

// use \ng169\cli\Clibase;

use ng169\Y;

class cart_mtoon extends Clibase
{
    public  $_booktype = 2; //书籍类型
    public  $_booklang = 0;  //书籍语言
    public  $_bookdstdesc_int = 1; //书籍来源描述
    public  $_bookdstdesc = "cart_mtoon"; //书籍来源描述
    public  $_domian = "https://sg.mangatoon.mobi"; //书籍来源描述
    public  $debug = true;
    public  $wordrate = 3;  //计算字数的时候的倍数比列
    // -------------------app 破解获取的相关信息
    // 签名密钥盐
    public $code = "66c10a61bd916c23f3b33810d3785d17";
    // aes iv
    public $aesiv = "";
    // aes密钥
    public $aeskey = "";
    //用户token
    public $token = "";
    public $appneedinfo = [
        'type' => '1', //1是漫画，2是小说

        // '_' => time(),
        '_preference' => 'girl',
        // '_preference' => 'boy',
        '_webp' => 'false',
        '_platform' => 'web',
        '_v' => '1.7.2',
        '_language' => 'th',
        '_token' => '07ba8764f71e613f54b9f0107fbf579c62',
        '_udid' => 'd21d0bcb5f8e7698c2da1cd1c57204fe',
    ];
    //远程完结状态值
    public $update_status_end_val = 1;
    //免费状态值
    public $is_un_free_val = true;
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
    // 获取远程小说列表，根据实际情况修改fun
    public function getbooklist($page)
    {

        $post = [
            "page" => $page,
            // "typeAction" =>  $page,
            // "pagesize" => "200",
            // "type" => "header",
            // "token" => $this->token
        ];
        $api = "/api/content/list";
        $datatmp = $this->apisign($api,  $post);
        // d($datatmp,1);

        //返回数据里面数据id字段
        $remote_bookarr_id = "id";
        list($status, $data) = $this->getdata($datatmp);
        if (!$status) {
            $this->debuginfo("列表中断" . $datatmp);
            return false;
        }

        if (is_array($data) && sizeof($data) > 0) {
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
            return sizeof($data);
        }
        return 0;
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
        if ($this->isend($remotebookid)) {
            d('本地完结' . $remotebookid);
            return false;
        }
        $api = "/api/content/detail";
        $id = $remotebookid;
        $datas = $this->apisign($api, [
            "id" => $id,
            // "type" => "1",
            // "token" => $this->token
        ]);

        //第三方内容中对应与本数据库字段对应
        $refield = [
            "bookname" => "title",
            "desc" => "description",
            "update_status" => "is_end",
            "wordnum" => "wordnum",
            "section" => "open_episodes_count",
            "bpic" => "image_url",
            "fid" => "id",
        ];
        //更新状态
        list($statu, $data) = $this->getdata($datas);
        if ($data) {
            $this->insertdetail($data, $refield);
        } else {
            $this->debuginfo("详情原因" . $data);
        }
    }

    public $field = [
        "title" => "title",
        "isfree" => "is_fee",
        "secid" => "id",
        'secnum' => 'secnum'
    ];
    // 获取远程章节列表，根据实际情况修改fun
    public function getseclist($id, $dbid)
    {
        $api = "/api/content/episodes";
        $data = ["id" => $id];
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
        if (!$data) {
            $data = $this->unlock($remote_book_id, $remote_sec_id, $remote_sec_num);
        }

        //密文解密
        if ($data) {
            // 参数 rondom+bid+cid+字符串“com.internationalization.novel”   MD516位小写 就是解密key
            $out = [];
            foreach ($data as $key => $picobj) {
                $pic = $picobj['url'];
                $decodepic = str_replace(['encrypted', 'webp'], ['watermark', 'jpg'], $pic);
                $obj = (object) ['url' =>  $decodepic, "name" =>  $key, "id" => $key];
                array_push($out, $obj);
            }

            return (object)['cart_sec_content' => $out];
        } else {
            d("$remote_book_id, $remote_sec_id, $remote_sec_num" . "内容拉取失败");
        }
        return false;
    }
    // 获取远程章节内容，根据实际情况修改fun
    //获取远程文章内容接口
    public function getremoc($remote_book_id, $remote_sec_id, $remote_sec_num)
    {

        $key = rand(000000, 999999);
        $api = "/api/cartoons/pictures";
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        $data = [
            // "token" => $this->token,
            "id" =>  $sid,
            // "cartoon_id" => $bid,
            // "random" => $key
        ];
        $datas = $this->apisign($api, $data);

        list($s, $data) = $this->getdata($datas);
        // d($data, 1);
        if ($data) {
            return ($data);
            // return ["key" => $key, "data" => $data];
        } else {
            // d("中断原因" . $datas);
            // $this->debuginfo("中断原因" . $datas);

            //章节内容拉取次数
            // if (isset($this->loop[$bid . "_" . $sid])) {
            //     $this->loop[$bid . "_" . $sid] = $this->loop[$bid . "_" . $sid] + 1;
            // } else {
            //     $this->loop[$bid . "_" . $sid] = 1;
            // }
        }
        return false;
    }
    //解锁接口
    public function unlock($remote_book_id, $remote_sec_id, $remote_sec_num)
    {
        $api = "/api/content/unlockByAdWatch";
        $adid = "8700" . rand(0, 9);
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        if (($this->loop[$bid . "_" . $sid])) {
            $this->loop[$bid . "_" . $sid] = $this->loop[$bid . "_" . $sid] + 1;
        } else {
            $this->loop[$bid . "_" . $sid] = 1;
        }
        $data = [
            "episode_id" => $sid,
            "content_id" =>  $adid,
            // "bid" => $bid,
        ];
        $data2 = $this->apisign($api, [], $data);
        //这里取状态，解锁状态成功就再次拉取内容
        list($s, $data) =  $this->getdata($data2);
        if ($s) {
            $data = $this->getremoc($remote_book_id, $remote_sec_id, $remote_sec_num);

            //解锁成功拉取因为 各种原因失败，所以再次尝试
            if (!$data) {
                $data = $this->getremoc($remote_book_id, $remote_sec_id, $remote_sec_num);
            }
            return $data;
        } {
            if ($this->loop[$bid . "_" . $sid] > 2) {
                //d三次失败才把错误原因入库,原因基本就是解锁满三次
                // $this->debuginfo("解锁中断" . $data2);
            }
            // d($data2, 1);
            //避免死循环
            if ($this->loop[$bid . "_" . $sid] < 3) {
                //更换token继续拉取
                // $this->reg();
                // return $this->unlock($remote_book_id, $remote_sec_id, $remote_sec_num);
            } else {
                // $this->debuginfo("$remote_book_id, $remote_sec_id, $remote_sec_num" . "尝试三次解锁失败");
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
    public function apisign($api, $parem, $post = null)
    {
        // $token = $this->token;
        // $this->setproxy();
        $p = [
            "_" => time(),
        ];
        $parem = array_merge($parem, $p, $this->appneedinfo);
        $parem["sign"] = $this->sign($api, $parem);
        // d($parem);
        $url = $api . '?';
        foreach ($parem as $key => $value) {
            # code...
            $url .= $key . '=' . $value . "&";
        }
        if ($post) {
            $data = $this->post($url, $post);
        } else {
            $data = $this->get($url, []);
        }

        return $data;
    }
    //签名类返回签名值
    public function sign($api, $data)
    {
        ksort($data);
        $signstr = $api;
        foreach ($data as $key => $value) {
            # code...
            $signstr .= $key . "=" . $value . "&";
        }
        // $signstr =  $signstr . $this->code;
        $signstr = substr($signstr, 0, -1) . $this->code;
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
    public function getdata($data, $field = ["status", "data"], $value = 'success')
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
        $this->initsp();
    }
    public function initsp()
    {
        $this->setdomain($this->_domian);
        $this->setinfo($this->_booktype, $this->_booklang, $this->_bookdstdesc_int, $this->_bookdstdesc);
        $this->loaddb($this->booktype, $this->booklang);
        // $this->logstart(__FILE__);
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
}
