<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */





require_once   dirname(dirname(dirname(__FILE__))) . "/clibase.php";

// use \ng169\cli\Clibase;

use ng169\Y;

class txt_qq extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 5;  //书籍语言
    public  $_bookdstdesc_int = BOOK_FROM_TYPE::qq; //书籍来源描述
    public  $_bookdstdesc = "china_qq_txt"; //书籍来源描述
    public  $_domian = "https://novel.html5.qq.com"; //书籍来源描述
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
        // 'Q-UA2' => 'QV=3&PL=ADR&PR=QB&PP=com.tencent.mtt&PPVN=12.1.5.5043&TBSVC=45001&CO=BK&COVC=045825&PB=GE&VE=GA&DE=PHONE&CHID=73387&LCID=15548&MO= NX563J &RL=1080*1920&OS=7.1.1&API=25&DS=64&RT=64&REF=qb_0&TM=00',
        // 'Q-UA' => 'ADRQBX121_GA/1215043&X5MTT_3/052151&ADR&6812014& NX563J &73387&15548&Android7.1.1 &V3',
        // 'X-QB-URL' => 'qb://ext/novelreader?mode=normal&banner_intro_show=1&ch=004760&reqid=29e38a86c2de505f8947d9e713a288cb58249732061286163980948945411993245&srctabid=181&tabfrom=bottom&sceneid=FeedsTab_NovelFeedsBMQB_FeedsHotRankBMQB&strageid=3217511_3565011_2980920_4553386_3010452_2192037_4057080_4108340_2680021_4853999_4523285_870123_1810409_2706115_2876002_3377818&traceid=0929305&bookId=1135238492&module=novelReader&component=novelReader',
        'QIMEI36' => '1ad9bc8c9cfb2e949e77100b10001cc14c1f',
        'Q-GUID' => '29e38a86c2de505f8947d9e713a288cb',
        'from_browser_novel_reader_qbrn' => '1',
        'Q-Auth' => '4f360edf11d2a42ce4d4257bfbcde469bfe0f658a1b9a99b',
        'referer' => 'https://novel.html5.qq.com',
        'QAID' => '01979F0927AD32EACA83B722D5B51F31',
        'x-qbrn-cookie' => '=Q-H5-ACCOUNT=; Q-H5-USERTYPE=0; Q-H5-SKEY=; Q-H5-LSKEY=; Q-H5-TOKEN=; Q-H5-OPENID=; Q-H5-QBID=; Q-H5-GUID=29e38a86c2de505f8947d9e713a288cb; Q-H5-ID=; _n_t_=; Q-H5-ADRIGHT=; sSessionKey=; sSessionAuth=; qbid=',
        // 'User-Agent' => 'Mozilla/5.0 (Linux; U; Android 7.1.1; zh-cn; NX563J Build/NMF26X) AppleWebKit/533.1 (KHTML, like Gecko) Mobile Safari/533.1',
        'from_browser_qbrn' => '1',
        'Q-QIMEI' => 'e4db149232eb2d35',
        // 'Apn-Type' => '1-0',

        // 'Accept-Encoding' => 'gzip',
        // 'content-type' => "text/plain;charset='UTF-8'",

        // 'Accept' => 'application/vnd.wap.xhtml+xml,application/xml,text/vnd.wap.wml,text/html,application/xhtml+xml,image/jpeg;q=0.5,image/png;q=0.5,image/gif;q=0.5,image/*;q=0.6,video/*,audio/*,*/*;q=0.6',

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
        // https://novel.html5.qq.com/qbread/intro-same?&sub2=2&count=100&page=17
        $size = 0;
        $post = [
            "page" => $page,
            "sub2" => 2,
            "count" => "200",
            // "type" => "header",
            // "token" => $this->token
        ];
        // $this->appneedinfo[''] = '';
        $api = "/qbread/intro-same";
        $datatmp = $this->apisign($api,  $post);
        preg_match_all("/\"resourceID\"\:\"([\d]{1,})\"/", $datatmp, $ids);
        // preg_match_all("/[\d]{10}/", $datatmp, $ids);
        // d($ids, 1);
        // d($datatmp, 1);
        if (!$ids[1]) {
            $this->debuginfo("获取书籍列表失败" . $datatmp);
        }
        //返回数据里面数据id字段
        $remote_bookarr_id = "id";
        // list($status, $data) = $this->getdata($datatmp);
        $status = sizeof($ids[1]);
        $data = $ids[1];
        if (!$status) {
            $this->debuginfo("列表中断" . $datatmp);
            //取数据库记录的id，循环拉去
            $data = T('book')->set_where(["ftype" => $this->_bookdstdesc, "lang" => $this->_booklang])->set_field('fid')->get_all();
            // return false;
            $remote_bookarr_id = "fid";
        } else {
            $size = sizeof($data);
        }

        if (is_array($data) && sizeof($data) > 0) {
            d("远程拉取小说数量" . sizeof($data));
            foreach ($data  as $book) {

                if ($this->isthread) {
                    //压入数组
                    // if (!in_array($book[$remote_bookarr_id], $this->thred_books)) {
                    //     array_push($this->thred_books, $book[$remote_bookarr_id]);
                    // }
                    $this->thpush($book);
                } else {
                    $this->getbookdetail($book);
                }
            }
            return $size;
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
        $api = "/qbread/intro";
        $id = $remotebookid;
        $datas = $this->apisign($api, [
            "bookid" => $id,
            // traceid: 0823017
            // sceneid: FeedsTab
            // strageid: 103513_1739322_1810412_2679921_2680021_2706114_2980920_3010451_3133527_3217512_3377818_3565011_4540125_4853999_5147562_5161235_870123
            // reqid: 43bad519f8a67925866374e313b788cb921840141133916398066366276821235
            // ch: 005438
            // tabfrom: top
            // bookid: 1100477698
        ]);

        //第三方内容中对应与本数据库字段对应
        $refield = [
            "bookname" => "resourceName",
            "desc" => "summary",
            "update_status" => "isfinish",
            "wordnum" => "contentsize",
            "section" => "serialnum",
            "bpic" => "picurl",
            "fid" => "resourceID",
            'writer_name' => 'author',
            'category_id' => 'sex',
            'cate_id' => 'subject',
            'lable' => 'tag'
        ];
        //更新状态
        preg_match("/window.__INITIAL_STATE__=(\{.{1,}\});/Ui", $datas, $pbook);
        if (!$pbook[1]) {
            $this->debuginfo("获取书籍详情失败");
        }

        $statu = $pbook[1] ? true : false;
        $data = $pbook[1];
        $data = json_decode(trim($data), 1);
        $data = $data['intro']['currentBook'];

        // d($data, 1);
        // list($statu, $data) = $this->getdata($datas);
        if ($data) {
            // $data = $this->fixtoon($data, $refield);
            $this->insertdetail($data, $refield);
        } else {
            $this->debuginfo("详情原因" . $data);
        }
    }
    //获取男女类别
    public function get_category_id($data)
    {
        if (!$data) return 0;
        if ($data == 2) {
            return 29;
        }
        if ($data == 1) {
            return 30;
        }
    }
    //获取标签
    public function get_lable($cateid, $data)
    {
        if (!$data) return 0;
        $datas = explode('|', $data);
        $instr = '';
        if (!sizeof($datas)) return 0;

        foreach ($datas as $str) {
            if ($str == '') break;

            $instr .= "'" . $str . "',";
        }

        $instr = trim($instr, ',');

        $datas = T('tag')->whereIn('tagname', $instr)->get_all();
        $id = array_column($datas, 'tagid');
        $ret = '';

        foreach ($id as $lb) {
            $ret .= "L" . $lb . ',';
        }
        return trim($ret);
    }
    //获取分类
    public function get_cate_id($cateid, $data)
    {

        if (!$data) return 0;
        $list = T('category')->set_where(['pid' => $cateid, 'depth' => 2])->get_all();

        foreach ($list as $v) {
            $domain = strstr($v['category_name'], $data);
            if ($domain) {
                return $v['category_id'];
            } else {
                $ret = $this->loopfind($v['category_name'], $data);
                if ($ret) {
                    return $v['category_id'];
                }
            }
        }
        return false;
    }
    public function loopfind($str, $find)
    {

        $find = $this->mbStrSplit($find);
        foreach ($find as $s) {
            $domain = strstr($str, $s);
            if ($domain) {
                return true;
            }
        }
        return false;
    }
    public function mbStrSplit($string, $len = 1)
    {
        $start = 0;
        $strlen = mb_strlen($string);
        while ($strlen) {
            $array[] = mb_substr($string, $start, $len, "utf8");
            $string = mb_substr($string, $len, $strlen, "utf8");
            $strlen = mb_strlen($string);
        }
        return $array;
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
        "title" => "serialName",
        "isfree" => "isFree",
        "secid" => "serialID",
        'secnum' => 'secnum'
    ];
    // 获取远程章节列表，根据实际情况修改fun
    public function getseclist($id, $dbid)
    {
        $api = "/qbread/api/book/all-chapter";
        $data = ["bookId" => $id];
        //远程与本地字段对应

        $datas = $this->apisign($api, $data);
        //更新字数
        //更新状态

        list($s, $data) = $this->getdata($datas, ['ret', 'rows'], '0');
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
            //尝试再次拉取
            $data = $this->getremoc($remote_book_id, $remote_sec_id, $remote_sec_num);
            // $data = $this->unlock($remote_book_id, $remote_sec_id, $remote_sec_num);
        }
        // d($data, 1);
        //密文解密
        if ($data) {
            // 参数 rondom+bid+cid+字符串“com.internationalization.novel”   MD516位小写 就是解密key
            return $data;
            // $out = [];
            // foreach ($data as $key => $picobj) {
            //     $pic = $picobj['url'];
            //     $decodepic = str_replace(['encrypted', 'webp'], ['watermark', 'jpg'], $pic);
            //     $obj = (object) ['url' =>  $decodepic, "name" =>  $key, "id" => $key];
            //     array_push($out, $obj);
            // }

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
        $api = "/be-api/content/ads-read";
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        // {"ContentAnchorBatch":[{"BookID":"1135238492","ChapterSeqNo":["4"]}],"Scene":"chapter"}
        // $data = [
        //     // "token" => $this->token,
        //     // "id" =>  $sid,
        //     // "cartoon_id" => $bid,
        //     // "random" => $key
        //     "ContentAnchorBatch" => ["BookID" => "$bid", "ChapterSeqNo" => ["$sid"]], "Scene" => "chapter"
        // ];
        $data = '{"ContentAnchorBatch":[{"BookID":"' . $bid . '","ChapterSeqNo":["' . $sid . '"]}],"Scene":"chapter"}';
        // $this->appneedinfo['strageid'] = '3217511_3565011_2980920_4553386_3010452_2192037_4057080_4108340_2680021_4853999_4523285_870123_1810409_2706115_2876002_3377818&traceid=0929305&bookId=' . $bid . '&module=novelReader&component=novelReader';
        // $this->appneedinfo['sceneid'] = 'FeedsTab_NovelFeedsBMQB_FeedsHotRankBMQB&';
        // $this->appneedinfo['reqid'] = '29e38a86c2de505f8947d9e713a288cb58249732061286163980948945411993245&srctabid=181&tabfrom=bottom&';

        $datas = $this->apisign2($api, null, $data);
        // d($datas, 1);
        list($s, $data) = $this->getdata($datas, ['ret', 'data.Content'], '0');
        $str = $data[0]['Content'][0];

        if ($data) {
            return ($data[0]['Content'][0]);
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

        $p = [
            "_" => time(),
        ];
        // $parem = array_merge($p, $this->appneedinfo, $parem);
        // $parem["sign"] = $this->sign($api, $parem);
        // d($parem);
        $url = $api . '?';
        foreach ($parem as $key => $value) {
            # code...
            $url .= $key . '=' . $value . "&";
        }
        $url = trim($url, '&');
        $this->head($this->appneedinfo);
        if ($post) {
            $data = $this->post($url, $post);
        } else {
            $data = $this->get($url, []);
        }

        return $data;
    }
    public function apisign2($api, $parem, $post = null)
    {

        $p = [
            "_" => time(),
        ];
        // $parem = array_merge($p, $this->appneedinfo, $parem);
        // $parem["sign"] = $this->sign($api, $parem);
        // d($parem);
        $url = $api . '';
        if ($parem) {
            foreach ($parem as $key => $value) {
                # code...
                $url .= $key . '=' . $value . "&";
            }
        }

        $url = trim($url, '&');
        $this->appneedinfo['Content-Type'] = 'application/json';
        $this->head($this->appneedinfo);
        if ($post) {
            $data = $this->post($url, ($post));
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
