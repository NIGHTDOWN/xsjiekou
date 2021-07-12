<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once   dirname(dirname(dirname(__FILE__))) . "/clibase.php";

// use \ng169\cli\Clibase;

use ng169\tool\Code;
use ng169\tool\File;
use ng169\Y;

class mtoon_txt extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 0;  //书籍语言
    public  $_bookdstdesc_int = 1; //书籍来源描述
    public  $_bookdstdesc = "txt_mtoon"; //书籍来源描述
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
        'type' => '2', //1是漫画，2是小说

        // '_' => time(),
        '_preference' => 'girl',
        // '_preference' => 'boy',
        '_webp' => 'false',
        '_platform' => 'web',
        '_v' => '2.01.02',
        '_language' => 'th',
        '_token' => '897aeecc13b29bebec65101f2d7b528a65',
        '_udid' => 'da616065-0cb3-479f-8a27-fc19385d10d3',
        /////////////////////////

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
    public function decode($dataurl)
    {
        $mt =  ([161, 158, 189, 103, 2, 8, 54, 66, 27, 65, 108, 98, 114, 215, 107, 119, 96, 242, 19, 248, 230, 72, 218, 166, 239, 246, 252, 245, 137, 179, 243, 206, 197, 236, 9, 145, 249, 225, 0, 176, 28, 13, 250, 244, 35, 48, 57, 216, 16, 127, 220, 73, 21, 224, 124, 199, 228, 85, 191, 154, 162, 140, 160, 200, 234, 50, 113, 62, 5, 229, 178, 104, 133, 195, 86, 194, 11, 42, 134, 89, 193, 120, 4, 47, 152, 192, 126, 101, 63, 196, 208, 172, 38, 163, 150, 132, 240, 112, 117, 146, 255, 118, 141, 58, 110, 41, 81, 144, 188, 88, 32, 175, 46, 59, 167, 68, 93, 139, 227, 121, 251, 182, 180, 60, 94, 136, 156, 201, 147, 29, 78, 143, 40, 109, 185, 202, 138, 164, 130, 186, 170, 31, 45, 91, 18, 173, 100, 187, 254, 39, 97, 155, 74, 111, 223, 26, 203, 34, 67, 23, 237, 177, 207, 231, 20, 204, 159, 71, 125, 80, 174, 241, 221, 92, 84, 90, 168, 122, 153, 247, 77, 213, 64, 6, 184, 10, 116, 37, 149, 129, 99, 83, 115, 123, 128, 135, 33, 70, 238, 253, 214, 56, 76, 210, 226, 44, 51, 25, 82, 157, 53, 106, 131, 148, 151, 142, 198, 183, 169, 55, 212, 95, 43, 211, 36, 75, 209, 102, 14, 171, 190, 7, 12, 105, 181, 15, 24, 61, 17, 52, 87, 222, 30, 3, 233, 232, 22, 165, 219, 79, 217, 69, 1, 235, 205, 49]);
        $gt =  ([39, 197, 251, 159, 23, 170, 21, 209, 188, 18, 9, 13, 212, 105, 14, 200, 43, 100, 89, 161, 62, 27, 29, 19, 239, 134, 234, 109, 24, 112, 173, 133, 95, 32, 73, 91, 35, 107, 196, 125, 226, 113, 20, 94, 81, 143, 75, 44, 151, 220, 156, 246, 117, 41, 85, 240, 122, 187, 193, 15, 189, 175, 157, 211, 37, 26, 40, 178, 243, 6, 229, 179, 202, 233, 74, 114, 154, 204, 48, 165, 57, 127, 8, 207, 65, 61, 201, 206, 86, 195, 77, 22, 110, 181, 237, 254, 97, 160, 47, 138, 69, 221, 12, 140, 70, 191, 68, 255, 180, 5, 210, 245, 250, 56, 80, 249, 205, 144, 106, 174, 166, 121, 99, 244, 162, 194, 185, 82, 53, 84, 88, 230, 214, 64, 135, 228, 42, 58, 103, 52, 158, 218, 10, 124, 46, 167, 198, 208, 216, 222, 217, 153, 155, 59, 132, 223, 98, 142, 123, 152, 90, 199, 111, 129, 76, 146, 66, 118, 172, 71, 164, 1, 219, 247, 79, 36, 28, 4, 141, 72, 50, 137, 149, 120, 139, 236, 128, 227, 38, 115, 253, 241, 83, 203, 49, 213, 238, 232, 30, 186, 182, 184, 183, 176, 16, 148, 3, 92, 130, 0, 93, 34, 54, 25, 67, 150, 33, 102, 192, 168, 242, 2, 231, 87, 252, 55, 171, 177, 136, 248, 31, 96, 119, 163, 11, 45, 7, 60, 78, 131, 147, 104, 116, 215, 225, 190, 224, 126, 63, 169, 101, 235, 145, 51, 17, 108]);
        $tmpa = [];
        $tmpa = array_pad($tmpa, sizeof($gt), 0);
        $content = File::readHttpContent($dataurl);

        $code = new Code();


        $bin = $this->asc2bin($content);


        $size = sizeof($bin);
        $a = $size % sizeof($mt);
        $n = $gt;
        if ($a > 0) {
            $n =  $tmpa;


            for ($i = 0; $i < sizeof($gt); $i++) {
                $o = $i + $a;
                $o >= sizeof($gt) && ($o -= sizeof($gt));
                $n[$o] = $gt[$i];
            }
        }
        $s =  $tmpa;
        for ($r = 0; $r < sizeof($n); $r++) {
            $s[$n[$r]] = $r;
        }

        for ($c = 0; $c < $size; $c++) {
            $l = $s[$bin[$c]];
            $e[$c] = $mt[$l];
        }
        // $string = bindec($bin); //十六进制字符串 8f6d3b
        $z = '';
        foreach ($e as $b) {
            $z .= $code->ascii_decode($b);
        }
        // d($z);
        return $z;
    }
    public function asc2bin($temp)
    {
        $data = [];
        $len = strlen($temp);
        for ($i = 0; $i < $len; $i++) {
            // $data .= sprintf('%08b', ord(substr($temp, $i, 1)));
            $datatmp = ord(substr($temp, $i, 1));
            array_push($data, $datatmp);
        }
        return $data;
    }
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


        //返回数据里面数据id字段
        $remote_bookarr_id = "id";
        list($status, $data) = $this->getdata($datatmp);
        if (!$status) {
            $this->debuginfo("列表中断" . $datatmp);
            $data = T('book')->set_where(["ftype" => $this->bookdstdesc, "lang" => $this->booklang])->set_field('fid')->get_all();
            // return false;
            $remote_bookarr_id = "fid";
            // return false;
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
            $data = $this->fixtoon($data, $refield);
            $this->insertdetail($data, $refield);
        } else {
            $this->debuginfo("详情原因" . $data);
        }
    }
    public function fixtoon($detail, $refield)
    {
        $desc = $detail[$refield['desc']];
        $bpic = $detail[$refield['bpic']];
        preg_match('/\s.*MangaToon.*/', $desc, $booldesc);
        preg_match('/\.[\w]{3,4}(-[\w]{1,})$/', $bpic,  $boolbpic);
        if ($booldesc[0]) {
            $detail[$refield['desc']] = str_replace($booldesc[0], '', $desc);
        }
        if ($boolbpic[1]) {
            $detail[$refield['bpic']] = str_replace($boolbpic[1], '', $bpic);
        }
        return $detail;
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
            // $out = [];
            // foreach ($data as $key => $picobj) {
            //     $pic = $picobj['url'];
            //     $decodepic = str_replace(['encrypted', 'webp'], ['watermark', 'jpg'], $pic);
            //     $obj = (object) ['url' =>  $decodepic, "name" =>  $key, "id" => $key];
            //     array_push($out, $obj);
            // }
            return $this->decode($data);
            // return (object)['cart_sec_content' => $out];
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
        $api = "/api/fictions/content";
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
                $this->reg();
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
    //注册接口
    public $invide;
    //注册接口
    public function reg()
    {
        $num = $this->getgnum(7);
        $api = "/api/users/loginEmail";
        $id = $num;
        $uid = $num . '-0cb3-479f-8a27-' . $this->getgnum(12);
        $datas = $this->apisign($api, [
            '_udid' => $uid,
        ], [
            "type" => 1,
            "password" => "y123456",
            "mail" => $id . "@gmail.com"
        ]);
        list($status, $data) = $this->getdata($datas, ["status", "access_token"], 'success');

        if ($status) {
            $this->token = $data;
            $this->bindinvite($this->token, $uid);
            return $this->token;
        } else {
            $this->debuginfo("注册中断" . $datas);
        }
    }
    public function getuser()
    {
        if ($this->invide) {
            return;
        }
        $api = "/api/users/profile";
        $datas = $this->apisign($api, [
            // '_token' => $token,
            // '_udid' => $uid,
        ], [
            // "invite_code" => 'KYNL7H',
        ]);
        list($status, $data) = $this->getdata($datas, ["status", "data"], 'success');
        if ($status) {
            $this->invide = $data['invite_code'];
            return $this->invide;
        } else {
            $this->debuginfo("获取用户信息失败" . $datas);
        }
    }
    public function bindinvite($token, $uid)
    {
        $this->getuser();
        //如果邀请码为空，获取邀请码
        $api = "/api/invite/bindInviteCode";
        $datas = $this->apisign($api, [
            '_token' => $token,
            '_udid' => $uid,
        ], [
            "invite_code" => $this->invide,
        ]);
        list($status, $data) = $this->getdata($datas, ["status", "access_token"], 'success');

        if ($status) {
            $this->token = $data;
            return $this->token;
        } else {
            $this->debuginfo("邀请失败" . $datas);
        }
    }
    //***********************************工具性************************************** */
    //http请求入口，根据实际情况，把一些固定值写进去
    public function apisign($api, $parem, $post = null)
    {



        // $this->setproxy('127.0.0.1', '9999');
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
        // d($url);
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

    //接口值判断类，$field[0]判断索引，$field[1]需要返回的摄影,$field[0] ==$value 返回treu
    public function getdata($data, $field = ["status", "data"], $value = 'success')
    {
        return  $this->check($data, $field, $value);
    }

    // 一些非不要类---------------------------------
    public function getgnum($size = 8)
    {
        $num = $this->generate_password($size);
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
