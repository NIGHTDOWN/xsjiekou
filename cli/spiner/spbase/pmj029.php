<?php

/**
 * 爱奇艺漫画
 * 列子 ：php opsock 192.168.1.1 8080
 */
namespace ng169\cli\spiner\spbase;
require_once   dirname(dirname(dirname(__FILE__))) . "/clibase.php";



use ng169\Y;
use ng169\cli\Clibase;

im(TOOL."simplehtmldom/simple_html_dom.php");
class pmj029 extends Clibase
{
    public  $_booktype = 2; //书籍类型
    public  $_booklang = 5;  //书籍语言
    public  $_bookdstdesc_int = 22; //书籍来源描述
    public  $_bookdstdesc = "风车动漫--盗版"; //书籍来源描述
    public  $_domian = "https://www.029pmj.com.cn"; //书籍来源描述
    public  $debug = true;
    public  $wordrate = 3;  //计算字数的时候的倍数比列
    // -------------------app 破解获取的相关信息
    // 签名密钥盐
    public $code = "0n9wdzm8pcyl1obxe0n9qdzm2pcyf1ob";
    // 0n9wdzm8pcyl1obxe0n9qdzm2pcyf1ob
    // 0n9wdzm8pcyl1obxe0n9qdzm2pcyf1ob
    // aes iv
    public $aesiv = "";
    // aes密钥
    public $aeskey = "";
    //用户token
    public $token = "39LWdKy9m2FfgfZlB7m1lFC2EfBXlEnXuIaXWMhm1Vm1f57DLIHzBL61MAm3V4xCgJ5Kk826e";
    // authCookie: 
    public $appneedinfo = [
        'agentVersion' =>    'h5',
        'qiyiId' =>    '4534e842413f659e8f11554a6d9b47e6',
        'srcPlatform' =>    '23',
        'appVer'    => '100.0.0',
        'userId' => '1840355440027648'
    ];
    //远程完结状态值
    public $update_status_end_val = 1;
    //免费状态值
    public $is_un_free_val = 3;
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
        $page = 1000;
        $i = 1;
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
        $this->logend($this->upcount??0, $this->upinfo, sizeof($this->rmbookid));
        $this->thcache($cachename);
        $this->thstart(__FILE__, $cachename);
        d("任务结束");
    }
    // 获取远程小说列表，根据实际情况修改fun
    public function getbooklist($page)
    {
        $post = [
        ];
        $api = "/update/page/$page.html";
        $datatmp = $this->apisign($api,  $post);
        
    
      $dom=   \str_get_html($datatmp);
      $data=[];
      foreach ($dom->find('div.mh-item') as $p) {
        
        $book['id']=$p->find("a")->attr['href'];
      
        $book['name']=$p->find(".mh-cover")->attr['style'];
        $book['pic']=$p->find("a")->attr['title'];
        $book['desc']=$p->find("chapter")->innertext;
        d($book,1);
    }
       
        //返回数据里面数据id字段
        $remote_bookarr_id = "id";
        list($status, $data) = $this->getdata($datatmp, ['code', 'data.comics']);

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

                    $this->getbookdetail($book);
                    d(1, 1);
                }
            }
            return sizeof($data);
        }
        return 0;
    }
    // 获取远程小说详情，根据实际情况修改fun
    public function getbookdetail($book)
    {
        $remote_bookarr_id = "id";
        $remotebookid = $book[$remote_bookarr_id];
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
        // $api = "/api/content/detail";
        // $id = $remotebookid;
        // $datas = $this->apisign($api, [
        //     "id" => $id,
        //     // "type" => "1",
        //     // "token" => $this->token
        // ]);
        // d($datas, 1);
        //第三方内容中对应与本数据库字段对应
        $refield = [
            "bookname" => "title",
            "desc" => "description",
            "update_status" => "serialize_status",
            "wordnum" => "last_chapter_order",
            "section" => "open_episodes_count",
            "bpic" => "image_url",
            "fid" => "id",
        ];
        //更新状态

        // list($statu, $data) = $this->getdata($datas);
        $data = $book;
        if ($data) {
            // $data = $this->fixtoon($data, $refield);
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
    //免费收费状态在这里
    //     "episodeAuthStatus":1,"episodeBossStatus":0,
    // "episodeAuthStatus":3,"episodeBossStatus":2,
    // "episodeAuthStatus":3,"episodeBossStatus":2,

    public $field = [
        "title" => "episodeTitle",
        "isfree" => "episodeAuthStatus",
        "secid" => "episodeId",
        'secnum' => 'episodePageCount'
    ];
    // 获取远程章节列表，根据实际情况修改fun
    public function getseclist($id, $dbid)
    {
        $api = "/views/comicCatalog";
        $data = [
            'comicId' =>    $id,
            'episodeId' =>    '0',
            'episodeIndex' =>    '0',
            'order' =>    '0',
            'size' =>    '10000',
        ];
        //远程与本地字段对应

        $datas = $this->apisign($api, $data);
        //更新字数
        //更新状态
        list($s, $data) = $this->getdata($datas, ['code', 'data.allCatalog.comicEpisodes']);

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
        if ($remote_sec_num == 200) {
            d(6, 1);
        }
        //这里是密文拉取
        $data = $this->getremoc($remote_book_id, $remote_sec_id, $remote_sec_num);
        if (!$data) {
            $data = $this->unlock($remote_book_id, $remote_sec_id, $remote_sec_num);
        }

        //密文解密
        if ($data) {
            // 参数 rondom+bid+cid+字符串“com.internationalization.novel”   MD516位小写 就是解密key
            $out = [];
            // array_push($out, (object) ['url' =>  $data['episodeCover'], "name" =>  '0', "id" => '0']);
            foreach ($data['episodePicture'] as $key => $picobj) {
                $pic = $picobj['imageUrl'];
                // $decodepic = str_replace(['encrypted', 'webp'], ['watermark', 'jpg'], $pic);
                $obj = (object) ['url' =>  $pic, "name" =>  $key, "id" => $key];
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
        // GET https://api-comic.if.iqiyi.com/v1/order/submit?dfp=15c723e853751e4e83a20a5c65f4f4e0dc8f0c601f3d3a78abb94e3ef7b5e8c1af&targetX=app&srcPlatform=35&authCookie=b3NtZr5JeKp3Sf7m1UBT4fxLnm3G2zvrAVg1jcnTqcJAn6WlyfcWKUJq6hCsr87cvu2Fd3&agentVersion=2.0.1&userId=1840355440027648&appChannel=20045006fd6e42f16f37d645c93a62a6&qypid=02023771010000000000&couponType=0&capability=3&comicId=285180070&qiyiId=f71fc82818b895da4e3e39721e6f374c1108&couponCount=0&timeStamp=1624203862083&testMode=0&appVer=2.0.1&field=catalog&agentType=354&channel=20045006fd6e42f16f37d645c93a62a6&orderStrategy=1&episodeId=1502957981740170&apiLevel=25 HTTP/1.1
        // authCookie: b3NtZr5JeKp3Sf7m1UBT4fxLnm3G2zvrAVg1jcnTqcJAn6WlyfcWKUJq6hCsr87cvu2Fd3
        // md5: b31fbec3045fafe04bac55892db6fbad
        // Host: api-comic.if.iqiyi.com
        // Connection: Keep-Alive
        // Accept-Encoding: gzip
        // User-Agent: okhttp/3.12.10.1





        $key = rand(000000, 999999);
        $api = "/v1/order/submit";
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        $data = [
            // "token" => $this->token,
            "episodeId" =>  $sid,
            "comicId" =>  $bid,
            "order" =>  0,
            "size" =>  0,
            // comicId=225640070&episodeId=539170170&order=0&size=0
        ];
        $datas = $this->apisign($api, $data);
        // d($datas, 1);
        list($s, $data) = $this->getdata($datas, ['code', 'data.episodes.0'], 'A00001');

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
    public function h5getremoc($remote_book_id, $remote_sec_id, $remote_sec_num)
    {

        $key = rand(000000, 999999);
        $api = "/read/1.0/batchRead";
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        $data = [
            // "token" => $this->token,
            "episodeId" =>  $sid,
            "comicId" =>  $bid,
            "order" =>  0,
            "size" =>  0,
            // comicId=225640070&episodeId=539170170&order=0&size=0
        ];
        $datas = $this->apisign($api, $data);
        // d($datas, 1);
        list($s, $data) = $this->getdata($datas, ['code', 'data.episodes.0'], 'A00001');

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
                // return $this->unlock($remote_book_id, $remote_sec_id, $remote_sec_num);
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
    public $invide;
    //注册接口
    public function reg()
    {
        $num = $this->getgnum(7);
        $api = "/api/users/loginEmail";
        $id = $num;

        $uid = 'hh616065-0cb3-479f-8a27-' . $this->getgnum(12);
        $datas = $this->apisign($api, [
            '_udid' => $uid,
        ], [
            "type" => $this->appneedinfo['type'],
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
        // $this->autoproxy();
        // $this->setproxy('127.0.0.1', '8888');
        $p = [
            "timeStamp" => time() . rand(100, 999),
        ];
        // if ($api != '/views/comicCatalog') {
        $parem = array_merge($p, $this->appneedinfo, $parem);
        // }


        $head = [
            // 'Origin:https://manhua.iqiyi.com',
            // 'User-Agent: Mozilla/5.0 (Linux; Android 8.0.0; Pixel 2 XL Build/OPD1.170816.004) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Mobile Safari/537.36',
            // 'DNT: 1',
            // 'Content-Type: application/json;',
            // 'Accept: */*',
            // 'Referer: https://manhua.iqiyi.com/comic/category',
            // 'Accept-Encoding: gzip, deflate, br',
            // 'Accept-Language: zh-CN,zh;q=0.9',
            // 'Connection: keep-alive',
            'md5:' . $this->sign($api, $parem),
            'authCookie:' . $this->token
        ];
        $this->head($head);
        // d($parem);
        $url = $api . '?';
        foreach ($parem as $key => $value) {
            # code...
            $url .= $key . '=' . $value . "&";
        }
        $url = substr($url, 0, -1);

        if ($post) {
            $data = $this->post($url, $post);
        } else {
            $data = $this->get($url);
        }

        return $data;
    }
    //签名类返回签名值
    public function sign($api, $data)
    {
        //h5签名规则
        // /read/1.0/batchReadcomicId=225640070&episodeId=541080170&order=0&size=0&qiyiId=4534e842413f659e8f11554a6d9b47e6&timeStamp=1624202750088&srcPlatform=23&appVer=100.0.0&agentVersion=h5&userId=1840355440027648  token
        // 39LWdKy9m2FfgfZlB7m1lFC2EfBXlEnXuIaXWMhm1Vm1f57DLIHzBL61MAm3V4xCgJ5Kk826e  code 0n9wdzm8pcyl1obxe0n9qdzm2pcyf1ob  
        $signstr = $api;
        foreach ($data as $key => $value) {
            # code...
            $signstr .= $key . "=" . $value . "&";
        }
        // $signstr =  $signstr . $this->code;
        $signstr = substr($signstr, 0, -1) . $this->token . $this->code;

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
    public function getdata($data, $field = [], $value = '')
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

