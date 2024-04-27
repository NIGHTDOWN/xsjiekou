<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */

use ng169\Y;
use ng169\tool\Out;


require_once   dirname(dirname(dirname(__FILE__))) . "/clibase.php";
// use \ng169\cli\Clibase;
//cookie里面提取token；拼接参数取MD5摘要为sign
// 拼接参数如下：
// let jsv = "2.6.1";  //版本
// let data = {};          
// let g = "12574478"; //appkey 浏览器链接上面可以获得
// let i = new Date().getTime();   //时间戳毫秒级别
// let token = getTokenFromCookie();   //token计算   计算cookie里面_m_h5_tk值"_"前面的那串32位长度
// // _m_h5_c   _m_h5_tk
// 参数是“token + "&" + i + "&" + g + "&" + data”
class taobaobase extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 5;  //书籍语言
    public  $_bookdstdesc_int = BOOK_FROM_TYPE::qq; //书籍来源描述
    public  $_bookdstdesc = "tbdesc"; //书籍来源描述
    public  $_domian = "https://h5api.m.taobao.com/h5/"; //接口
    public  $debug = true;
    public  $wordrate = 3;  //计算字数的时候的倍数比列
    // -------------------app 破解获取的相关信息
    // 签名密钥盐
    public $code = "66c10a61bd916c23f3b33810d3785d17";

    public $cookie = "t=c840dd28db1e04f8ffe0415456e36550; thw=cn; cna=GVktHsW5OFECAXhVc2S8Qsjs; 3PcFlag=1713798789243; lgc=%5Cu6211%5Cu662F%5Cu6768%5Cu5FD7%5Cu4F1F0895; dnk=%5Cu6211%5Cu662F%5Cu6768%5Cu5FD7%5Cu4F1F0895; tracknick=%5Cu6211%5Cu662F%5Cu6768%5Cu5FD7%5Cu4F1F0895; sgcookie=E100L22eVkIueLssGmxJzG8SweZ6eQQfkztMBdb1D0JqTJc386ezq7GrSdGrsgCvn7QiQ2wXsBSYnqAGnwiHN5jPxnEyc%2FGTVGvA%2FBQAeBQlubhEdR8I0A04ABsh6c1jUlWI; havana_lgc2_0=eyJoaWQiOjQ4MjA1NjgxMiwic2ciOiJmYjdlMjg3ZjU0MTRiZDA5NWI0OTc3NjE5MWIwNzA3ZiIsInNpdGUiOjAsInRva2VuIjoiMUxLWGZ5OWNOTjNYT2E4RkdZcDJsQmcifQ; _hvn_lgc_=0; havana_lgc_exp=1712984864429; wk_cookie2=10209e5326a51bc93ca21048daa73ca9; wk_unb=VyTxRXqxXduw; uc3=vt3=F8dD3e3cqF2YJ4l5YuA%3D&nk2=rUtEoeYXZBfXmbAgdwA%3D&lg2=URm48syIIVrSKA%3D%3D&id2=VyTxRXqxXduw; uc4=id4=0%40VXPh%2BcMw1myhwXOptoE0PHFOCI4%3D&nk4=0%40r7rCNYzd7oTvhWBFn7Uub4CZaBBJMq7gug%3D%3D; _cc_=WqG3DMC9EA%3D%3D; miid=1652294589740362049; mtop_partitioned_detect=1; _m_h5_tk=78cb7eedd9812d43ce21ad804e6e8062_1714232824249; _m_h5_tk_enc=7317117dc16187619cacf4fefe8e60da; mt=ci=-1_0; cookie2=2b209001657af0f4146164207efacc87; _tb_token_=5633ee63ee13; _samesite_flag_=true; tfstk=e0sXKDwQijcfn_gmNSwyVoaBRG-_TsZEcA9OKOnqBnKx6fBpacyD0Px11Q1MgE-Afam1KOj4nhyDmtxMXWPUYG6cnhcVCaz8YmwN4JVUTkrPntxMXW7N6DyOxhHSaJP1QbgIJcRUIkJjXDiCVLFM9tOpELjWBEOpyQithg9XlBBA4fieOfm-512qfLOUF8giS1_o_DygxTIpkLvbT8wSNFYvELOUF8gKrEpkUZy7Fjwh.; uc1=cookie14=UoYfparveNehlA%3D%3D; isg=BI6OWsbTZNVsPtN9Rwr58Lp132RQD1IJHbPZDbjX-hFNGy51IJ-iGTRdU0d3A0oh";
    // aes iv
    public $jsv = "2.6.1";      //appkey 浏览器链接上面可以获得
    public $appkey = "12574478"; //appkey 浏览器链接上面可以获得
    public $aesiv = "";
    // aes密钥
    public $aeskey = "";
    //用户token
    public $token = "";

    public $appneedinfo = [
        'Accept' => 'application/json',
        'Accept-Language' => 'zh-CN,zh;q=0.9',
        'Content-type' => 'application/x-www-form-urlencoded',
        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.5735.289 Safari/537.36',
        'sec-ch-ua' => '"Not.A/Brand";v="8", "Chromium";v="114", "Google Chrome";v="114"',
        'sec-ch-ua-mobile' => '?0',
        'Sec-Fetch-Site' => 'same-site',
        'Sec-Fetch-Mode' => 'cors',
        'sec-ch-ua-platform' => '"Windows"',
        'Referer' => 'https://item.taobao.com/',
        'Sec-Fetch-Dest' => 'empty',
        'Origin' => 'https://item.taobao.com',
        'Cookie' => '',
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
    public function setck($ck)
    {
        if (!$ck) return false;
        $this->cookie = $ck;
        $this->appneedinfo["Cookie"] = $this->cookie;
    }
    public $last = 0;
    public $lastbid;
    public $loop = [];
    /**
     * group分组 男女
     */
    public function start($group = 0)
    {
        // $get = get(['string' => ["appid" ]]);

        //  $data=  T('spuser')->set_where($get)->find();

        //  if($data){
        //     Out::jout($data['num']);
        //  }else{
        //     Out::page404();
        //  }





        $this->setproxy();
        // $this->ip="127.0.0.1";
        // $this->port="8888";
        $this->appneedinfo["Cookie"] = $this->cookie;
        $this->getbookdetail("42216585950");
        // $this->autoproxy();
        // $cachename = date('Ymdhis') . 'obj' . $group;
        // $this->bookdstdesc = $this->_bookdstdesc . $group;
        // $this->thinit();
        // $page = 100;
        // $i = 0;
        // $cate = [
        //     [1505, 1501, 1504, 1502, 1506, 1503,], //男生
        //     [1524, 1523, 1518, 1517, 1516, 1707, 1522] //女生
        // ];
        // $this->logstart(__FILE__);
        // $this->thcacheobj($cachename);
        // if (!$this->get_th_listcache()) {

        //     foreach ($cate[$group] as $cc) {
        //         for ($i = 0; $i <= $page; $i++) {
        //             $size = $this->getbooklist($i, $cc, $group);
        //             if (!$size) {
        //                 //分页已经没东西了，直接退出
        //                 break;
        //             }
        //         }
        //     }

        //     $this->set_th_listcache();
        // }
        // $this->logend($this->upcount, $this->upinfo, sizeof($this->rmbookid));
        // $this->thcache($cachename);
        // $this->thstart(__FILE__, $cachename);
        d("任务结束");
    }
    // 获取远程小说列表，根据实际情况修改fun
    public function getbooklist($page, $cate, $manorwoman)
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
        $pagesize = 1000;
        // $api = "/qbread/intro-same";
        // $datatmp = $this->apisign($api,  $post);
        // preg_match_all("/\"resourceID\"\:\"([\d]{1,})\"/", $datatmp, $ids);

        // if (!$ids[1]) {
        //     $this->debuginfo("获取书籍列表失败" . $datatmp);
        // }
        // $status = sizeof($ids[1]);
        // $data = $ids[1];
        $post = '{"query":"query {\n    groups(param: {\n    cond: {\n      id: ' . $cate . '\n      condName: \"test\"\n      sortBy: GroupSortByHotCount\n      pageQuery: {\n        first: ' . $pagesize . '\n        after: \"' . ($page * $pagesize) . '\"\n      }\n      openFilterSupportPageQuery: {\n        first: 13\n        after: \"0\"\n      }\n      \n    selectRangeFilter: {\n      key: BookFilterCategory1\n      value: ' . (!$manorwoman ? 'BookCategory1Male' : 'BookCategory1Female') . '\n  }\n      \n      \n    }\n    epubFilter: true\n  }){\n      id\n      info {\n        groupID\n        books {\n          id\n          bookBaseInfo {\n            id\n            name\n            picURL\n            category2 \n            isOriginal\n            isExclusiveAdsFree\n            summary\n            isFinished\n            category1ID\n            category2ID\n            category3ID\n            bookState\n          }\n          bookFeatureInfo {\n            bookShowScore\n          }\n        }\n        openFilterSupported {\n          key\n          value {\n            label\n            value\n          }\n        }\n        pageInfo {\n          cursor\n          total\n          hasNextPage\n        }\n      }\n    }\n  }"}';
        $api = "/be-api/gql";
        $datatmp = $this->apisign2($api, null, $post);


        //返回数据里面数据id字段
        $remote_bookarr_id = "id";
        list($status, $data) = $this->getdata($datatmp);
        $datatmp = json_decode($datatmp, 1);
        $datatmp = $datatmp['data']['groups']['0']['info'][0]['books'];
        if (!$datatmp) return 0;
        $data = array_column($datatmp, 'id');
        // d($data, 1);
        $status = sizeof($data);
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
    public function getbookdetail($pid)
    {



        $api = "mtop.taobao.pcdetail.data.get/1.0/";
        $id = $pid;
        $parem['data'] = '{"id":"' . $id . '","detail_v":"3.3.2","exParams":"{\"id\":\"' . $id . '\",\"queryParams\":\"id=' . $id . '\",\"domain\":\"https://item.taobao.com\",\"path_name\":\"/item.htm\"}"}';
        $datas = $this->apisign($api, $parem['data']);
        d($datas ,1);
        return $datas;
        // d($datas);
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
    public function getbookdetaillink($pid)
    {



        $api = "mtop.taobao.pcdetail.data.get/1.0/";
        $id = $pid;
        $parem['data'] = '{"id":"' . $id . '","detail_v":"3.3.2","exParams":"{\"id\":\"' . $id . '\",\"queryParams\":\"id=' . $id . '\",\"domain\":\"https://item.taobao.com\",\"path_name\":\"/item.htm\"}"}';
        $datas = $this->apisignlink($api, $parem['data']);
        return $datas;
        // d($datas);
        //第三方内容中对应与本数据库字段对应
       
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
    private function gettokenFormCookie()
    {
        if ($this->token) return $this->token;
        $tk = $this->appneedinfo["Cookie"];
        if (!$tk) {
            $this->debuginfo("未配置cookie");
        }
        preg_match('/_m_h5_tk=([^_;]*)/', $tk, $matches);
        if (!empty($matches[1])) {
            // echo "Value of _m_h5_tk: " . $matches[1];
            $this->token = $matches[1];
            return $matches[1];
        } else {
            $this->debuginfo("token获取失败");
            return false;
        }
    }
    //http请求入口，根据实际情况，把一些固定值写进去
    public function apisign($api, $parem, $post = null)
    {
        
        $apiinfo = explode("/", $api);
        $parem = [
            "jsv" => $this->jsv,
            "appKey" => $this->appkey,
            "t" => time() . rand(100, 900),
            // "t" => '1714226343519',
            'api' => $apiinfo[0],
            'v' => $apiinfo[1],
            'isSec' => 0,
            'ecode' => 0,
            'timeout' => 10000,
            'ttid' => urlencode("2022@taobao_litepc_9.17.0"),
            'AntiFlood' => "true",
            'AntiCreep' => "true",
            'dataType' => "json",
            'valueType' => "string",
            'preventFallback' => "true",
            'type' => "json",
            "data" => $parem
        ];
        // $id=$parem['id']; 
       
        // $parem = array_merge($p, $parem);
        // $parem["data"] =json_encode($post);
        $parem["sign"] = $this->sign($api, $parem);
        $parem["data"] = urlencode($parem["data"]);
        // d($parem);
        $url = $api . '?';

        foreach ($parem as $key => $value) {
            # code...
            $url .= $key . '=' . $value . "&";
        }

        $url = trim($url, '&');
        $this->head($this->appneedinfo);


        $data = $this->get($url);
       
        return $data;
    }
    public function apisignlink($api, $parem, $post = null)
    {

        $apiinfo = explode("/", $api);
        $parem = [
            "jsv" => $this->jsv,
            "appKey" => $this->appkey,
            "t" => time() . rand(100, 900),
            // "t" => '1714226343519',
            'api' => $apiinfo[0],
            'v' => $apiinfo[1],
            'isSec' => 0,
            'ecode' => 0,
            'timeout' => 10000,
            'ttid' => urlencode("2022@taobao_litepc_9.17.0"),
            'AntiFlood' => "true",
            'AntiCreep' => "true",
            'dataType' => "json",
            'valueType' => "string",
            'preventFallback' => "true",
            'type' => "json",
            "data" => $parem
        ];
        // $id=$parem['id']; 
        // $parem = array_merge($p, $parem);
        // $parem["data"] =json_encode($post);
        $parem["sign"] = $this->sign($api, $parem);
        $parem["data"] = urlencode($parem["data"]);
        // d($parem);
        $url = $api . '?';
        foreach ($parem as $key => $value) {
            # code...
            $url .= $key . '=' . $value . "&";
        }
        $url = trim($url, '&');
        $this->head($this->appneedinfo);
        // $data = $this->get($url);
        return ["link"=>$url,"head"=>$this->appneedinfo];
    }
    public function apisign2($api, $parem, $post = null)
    {


        // $parem = array_merge($p, $this->appneedinfo, $parem);
        // $parem["sign"] = $this->sign($api, $parem);
        // d($parem);
        $url = $api . '?';
        if ($parem) {
            foreach ($parem as $key => $value) {
                # code...
                $url .= $key . '=' . $value . "&";
            }
        }

        $url = trim($url, '&');
        $url = trim($url, '?');
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
        $oken = $this->gettokenFormCookie();
        $signstr = $oken . "&" . $data["t"] . "&" . $data["appKey"] . "&" . ($data['data']);
        // d($signstr);
        // $signstr = substr($signstr, 0, -1) . $this->code;
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
