<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */

 namespace ng169\cli\spiner\spbase;
 require_once   dirname(dirname(dirname(__FILE__))) . "/clibase.php";
 
 
 
 use ng169\cli\Clibase;
// use \ng169\cli\Clibase;

use ng169\Y;
use phpseclib\Crypt\Random;

class Splanovel extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 6;  //书籍语言
    public  $_bookdstdesc_int = 2; //书籍来源描述
    public  $_bookdstdesc = "ms_lanovel"; //书籍来源描述
    public  $_domian = "https://www.lanovel.club"; //书籍来源描述
    public  $debug = true;
    public  $wordrate = 3;  //计算字数的时候的倍数比列
    // -------------------app 破解获取的相关信息
    // 签名密钥盐
    public $code = "c1774fb28759d14916a641f04df67bca";
    // c1774fb28759d14916a641f04df67bca
    // aes iv
    public $aesiv = "";
    // aes密钥
    public $aeskey = "";
    //用户token
    public $token = "";
    public $appneedinfo = [
        "version" => "1.3.7",
        "language" => "MR",
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
        $cachename = date('Ymdhis') . 'obj';
        $this->thinit();
        $page = 10;
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
            "page" => 1,
            "typeAction" =>  $page,
            "pagesize" => "200",
            "type" => "header",
            "token" => $this->token
        ];
        $api = "/axahq/first/getStartupArray";
        $datatmp = $this->apisign($api,  $post);
        //返回数据里面数据id字段
        // d($datatmp);
        $remote_bookarr_id = "id";
        list($status, $data) = $this->getdata($datatmp);
        if (!$status) {
            $this->debuginfo("列表中断" . $datatmp);
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
        }
        if ($this->isend($remotebookid)) {
            d('本地完结' . $remotebookid);
            return false;
        }
        $api = "/axahq/book/getBookDetailV2";
        $id = $remotebookid;
        $datas = $this->apisign($api, [
            "bid" => $id,
            "type" => "1",
            "token" => $this->token
        ]);
        //第三方内容中对应与本数据库字段对应
        $refield = [
            "bookname" => "name",
            "desc" => "remake",
            "update_status" => "type",
            "wordnum" => "secnum",
            "section" => "chapter_num",
            "bpic" => "img",
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
        "isfree" => "type",
        "secid" => "chapter_num",
        'secnum' => 'secnum'
    ];
    // 获取远程章节列表，根据实际情况修改fun
    public function getseclist($id, $dbid)
    {
        $api = "/axahq/book/getChapterListV2";
        $data = ["bid" => $id, "token" => $this->token];
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
        $sid = $remote_sec_id;
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
        // sleep(10);
        $key = rand(000000, 999999);
        $api = "/axahq/book/getContent";
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        $data = [
            // "token" => $this->token,
            "cid" =>  $sid,
            "bid" => $bid,
            "random" => $key,
            // "token" => 'b3bb46c5124aeaea4fb28c674b61d39c',
            // 'token'=>'eb3dd2150b8aaa2b044444fae7d0f0b7',
            // "cid" =>  "28",
            // "bid" => "105",
            // "time" => '161384113333',
            // "rnumber" => '6732',
            // "random" => '753932',
            // 'token'=>'b78316940871942a1caf3597dabc334d',

        ];
        $datas = $this->apisign($api, $data);
        // d(1,1);
        list($s, $data) = $this->getdata($datas);

        if ($data) {

            return ["key" => $key, "data" => $data];
        } else {
            d("中断原因" . $datas);
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
        $api = "/axahq/book/rewardChapter";
        // $api = "/axahq/book/buyOneChapter";
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        $data = [
            "token" => $this->token,
            "cid" =>  $sid,
            "bid" => $bid,
        ];
        if (($this->loop[$bid . "_" . $sid])) {
            $this->loop[$bid . "_" . $sid] = $this->loop[$bid . "_" . $sid] + 1;
        } else {
            $this->loop[$bid . "_" . $sid] = 1;
        }
        $data2 = $this->apisign($api, $data);
        //这里取状态，解锁状态成功就再次拉取内容
        list($s, $data) =  $this->getdata($data2, ["status", "data"], 1);
        if ($s) {
            // $this->readStatistics($remote_book_id);
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
                $this->reg();
                return $this->unlock($remote_book_id, $remote_sec_id, $remote_sec_num);
            } else {
                // $this->debuginfo("$remote_book_id, $remote_sec_id, $remote_sec_num" . "尝试三次解锁失败");
            }
            //在回调此函数
        }
        $this->debuginfo("$remote_book_id, $remote_sec_id, $remote_sec_num" . "内容拉取失败" . $data2);
        return false;
    }
    public function tasksign()
    {

        $api = "/axahq/set/sign";
        $this->apisign($api, []);
    }
    //注册接口
    public function reg()
    {
        $num = $this->getgnum();
        $api = "/axahq/User/otherLogin";
        $gid = '11609787307781'    . $this->getgnum();
        $fid = $this->getgnum()   . $this->getgnum() . rand(0, 9);
        // $fid = '346955509880258';
        // "tokenId" => '116097873077818170143',
        $post = [
            "tokenId" => $fid,
            "origin" => "android",

            "type" => "fb",
            "headurl" => "https://platform-lookaside.fbsbx.com/platform/profilepic/?asid=" . $fid . "&height=50&width=50&ext=1616477967&hash=AeSHyJO3kWVN72VE6hs",
            "username" => substr(base64_encode(md5(time())), 16)
        ];
        // $post=[
        //     "tokenId" => "346955509880258",
        //     "origin" => "android",
        //     "time" => "1613885967494",
        //     "rnumber" => "7717",
        //     "token" => "95986011340b4a401ba04169a8422e26",

        //     "type" => "fb",
        //     "headurl" => "https://platform-lookaside.fbsbx.com/platform/profilepic/?asid=346955509880258&height=50&width=50&ext=1616477967&hash=AeSHyJO3kWVN72VE6hs",
        //     "username" => "GM Look"
        // ];
        $datas = $this->apisign($api, $post);
        list($status, $data) = $this->getdata($datas, ["status", "data"], 1);
        if ($status) {
            $this->token = $data["token"];
            // $this->rftoken();
            // $this->getprice();
            $this->tasksign();
            return $this->token;
        } else {
            $this->debuginfo("注册中断" . $datas);
        }
    }
    public function rftoken()
    {
        $api = "/axahq/set/refreshFcmToken";
        $datas = $this->apisign($api, []);
        list($status, $data) = $this->getdata($datas, ["status", "data"], 1);
    }
    public function readStatistics($bid)
    {
        $api = "/axahq/book/readStatistics";
        // {"language":"EN","bid":"356","rnumber":"2637"}
        $datas = $this->apisign($api, [
            'bid' => $bid
        ]);
        list($status, $data) = $this->getdata($datas, ["status", "data"], 1);
    }
    public function getprice()
    {
        $api = "/axahq/set/getPrice";
        $datas = $this->apisign($api, [

            'pay_type' => '5'
        ]);
        list($status, $data) = $this->getdata($datas, ["status", "data"], 1);
    }
    //***********************************工具性************************************** */
    //http请求入口，根据实际情况，把一些固定值写进去
    public $ptime;
    public function apisign($api, $parem)
    {

        // $this->setproxy();
        $this->head([
            'Content-Type: application/json; charset=utf-8',
            'Connection: Keep-Alive',
            // 'Accept-Encoding: gzip',
            'Accept: ',


        ]);
        $token = $this->token;
        if (!$this->ptime) {
            $this->ptime = time();
        }
        $time = $this->ptime;
        $p = [
            "time" => $this->ptime . rand(000, 999),
            "rnumber" => Rand(1000, 9000),
            "token" => $token, //可变

        ];
        $parem = array_merge($this->appneedinfo, $p, $parem);
        $parem["sign"] = $this->sign($api, ($parem));
        $post = $parem;
        // $post = [
        //     'sign' => $parem['sign'],
        //     'time' => $parem['time'] . "",
        //     'token' => $parem['token'] . "",
        //     'version' => $parem['version'],
        //     'cid' => $parem['cid'] . "",
        //     'language' => $parem['language'],
        //     'bid' => $parem['bid'] . "",
        //     'rnumber' => $parem['rnumber'] . "",
        // ];
        // foreach ($parem as $key => $value) {
        //     if (!in_array($key, array_keys($post))) {
        //         // array_push($post, [$key => $value]);
        //         if($value!=''){
        //             $post[$key] = $value . "";
        //         }

        //     }
        // }

        // d($post);
        // $post = array_filter($post);
        $data = $this->post($api, json_encode($post));

        return $data;
    }
    //签名类返回签名值
    public function sign($api, $data)
    {
        ksort($data);
        // $signstr = $api; //1.3.5版本前面签名带api地址
        $signstr = "";
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
    public function getdata($data, $field = ["status", "data"], $value = 0)
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
