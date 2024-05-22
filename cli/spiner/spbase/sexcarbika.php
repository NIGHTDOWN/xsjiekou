<?php

/**
 * 需要翻墙
 * 列子 ：php opsock 192.168.1.1 8080
 */

namespace ng169\cli\spiner\spbase;

require_once   dirname(dirname(dirname(__FILE__))) . "/clibase.php";



use ng169\Y;
use ng169\cli\Clibase;
use ng169\tool\Curl;
use ng169\tool\Ngmatch;

im(TOOL . "simplehtmldom/simple_html_dom.php");
class sexcarbika extends Clibase
{
    //本地node服务信息
    public  $_booktype = 2; //书籍类型
    public  $_booklang = 5;  //书籍语言
    public  $_bookdstdesc_int = 28; //书籍来源描述
    public  $_bookdstdesc = "色情哔咔男--漫画"; //书籍来源描述
    // public  $_domian = "https://api.picacomic.com/"; //书籍来源描述
    public  $_domian = "https://api.go2778.com/"; //书籍来源描述
    public  $img_domian = "https://s3.go2778.com/static/"; //书籍来源描述

    // go2778.com 备用域名
    public  $debug = true;
    public  $wordrate = 3;  //计算字数的时候的倍数比列
    // -------------------app 破解获取的相关信息
    //一些临时数据，无需变动
    public $upinfo = [];
    public $upcount = 0;
    public $rmbookid = [];
    public $last = 0;
    public $lastbid;
    public $loop = [];
    public $appneedinfo = [
        'authorization' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJfaWQiOiI2NjRiMTQwYWMwZmQ4NmVlNDIwMWFiZDYiLCJlbWFpbCI6ImFhNzUyOTQyNiIsInJvbGUiOiJtZW1iZXIiLCJuYW1lIjoiYWE3NTI5NCIsInZlcnNpb24iOiIyLjIuMS4zLjMuNCIsImJ1aWxkVmVyc2lvbiI6IjQ1IiwicGxhdGZvcm0iOiJhbmRyb2lkIiwiaWF0IjoxNzE2MTk2MzY1LCJleHAiOjE3MTY4MDExNjV9.97WBlf2M-U4eqn92XKgVBI3QsK90Sf-lQ1h75entgnw',
        'app-uuid' => 'webUUID',
        'accept' => 'application/vnd.picacomic.com.v1+json',
        'image-quality' => 'medium',
        'app-channel' => '1',
        'authority' => 'api.go2778.com',
        'origin' => 'https://manhuapica.com',
        'sec-fetch-dest' => 'empty',
        'sec-fetch-mode' => 'cors',
        'sec-fetch-site' => 'cross-site',
        'content-type' => 'application/json; charset=UTF-8',
        'user-agent' => 'Mozilla/5.0 (iPhone; CPU iPhone OS 13_2_3 like Mac OS X) AppleWebKit/605.1.15 (KHTML, like Gecko) Version/13.0.3 Mobile/15E148 Safari/604.1',
    ];
    public $nonce = "t4yrwc3mwp5pa3st2pkytetmrfxftjjs";  //head头里面提取
    public $appleKillFlag = "C69BAF41DA5ABD1FFEDC6D2FEA56B";  //putils.js提取，是定值；通过断点拿固定的返回值
    public $appleVerSion = '~d}$Q7$eIni=V)9\RK/P.RM4;9[7|@/CA}b~OW!3?EV`:<>M7pddUBL5n|0/*Cn';
    // function getAppleKillFlag() {
    //     return deRabbit('U2FsdGVkX1/kZqW9m/2nul1sQl3H9FgxcBFF1fI6tzUtXz4NxMTK3cK3y2JSBrz6');

    // }function deRabbit(dataText) {
    //     return CryptoJS.TripleDES.decrypt(dataText, setVersion).toString(CryptoJS.enc.Utf8);
    // }

    public function start()
    {

        $cachename = date('Ymdhis') . 'obj';
        $this->thinit();
        $page = 7000;
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
        $this->logend($this->upcount ?? 0, $this->upinfo, sizeof($this->rmbookid));
        $this->thcache($cachename);
        $this->thstart(__FILE__, $cachename);
        d("任务结束");
    }

    public function processBookArray($bookArray)
    {
        return $bookArray;
        // 过滤id，保留数字
        //    $id = Ngmatch::geturllast($bookArray['id'],3);
        //    $pic = Ngmatch::geturlimgone($bookArray['pic']);
        //     return array(
        //         'id' => $id,
        //         'name' => $bookArray['name'],
        //         'pic' => $pic,
        //         // 'desc' => $desc
        //     );
    }
    // 获取远程小说列表，根据实际情况修改fun
    public function getbooklist($page)
    {
        // $this->setproxy("127.0.0.1","6666");
        $post = [];
        $api = "comics?page={$page}";
        $datatmp = $this->apisign($api,  $post);
        list($status, $datatt) = $this->getdata($datatmp, ["code", "data"], 200);

        if (!$status) {
            d("拉列表失败");
            return;
        }
        $dtlist = $datatt['comics']['docs'];
        // $dom =   \str_get_html($datatmp);
         $data = [];
        foreach ($dtlist as $p) {
            $book = [];
            $book['id'] = $p['id'];
            $book['pic'] = $this->img_domian . $p['thumb']['path'];
            $book['name'] = $p['title'];
            $book['tmpcate'] = implode(",", $p['categories']);
            $book = $this->processBookArray($book);
           
            array_push($data, $book);
        }
        //返回数据里面数据id字段
        $remote_bookarr_id = "id";
        if (is_array($data) && sizeof($data) > 0) {
            d("远程拉取小说数量" . sizeof($data));
            foreach ($data  as $k => $book) {
                if ($this->isthread) {
                    Y::$cache->set("spck_" . $book[$remote_bookarr_id], $book, G_DAY * 2);
                    $this->thpush($book[$remote_bookarr_id]);
                } else {
                    $this->getbookdetail($book);
                    // d($book,1);
                }
            }
            return sizeof($data);
        }
        return 0;
    }
    private $seclist = [];
    //获取远程章节
    private function gethttpsec($id)
    {
        if (isset($this->seclist[$id])) return  $this->seclist[$id];
        $do = true;
        $page = 1;
        $data = [];
        $datat = [];
        while ($do) {
            $api = "comics/$id/eps?page=$page";
            //远程与本地字段对应
            $datatmp = $this->apisign($api, $data);
            // d(Curl::getBashCurl($api,[],$this->appneedinfo));
           
            list($status, $datatt) = $this->getdata($datatmp, ["code", "data"], 200);
           
            if (!$status) {
                $do = false;
                return;
            }
            if($page==$datatt['eps']['pages']){
                $do = false; 
            }
           
            $datat=array_merge($datat,$datatt['eps']['docs']);
            $page++;
        }

        //更新字数
        //更新状态
        // $html = str_get_html($datas);
        // $hbox = $html->find('.page-links')[0];
        // $sec = $hbox->find('a');
        if ($datat) {
            $data = [];
            foreach ($datat as $key => $pli) {
                # code...
                $id = $key + 1;
                $row = array(
                    'title' => $pli['title'],
                    'isfree' => 1,
                    'secid' => $pli['order'],
                    'secnum' => '100',
                );
                // preg_match_all('/\d+/', $row['secid'], $matches);
                if (($row['secid']) > 0) {
                    //过滤掉加载更多的按钮
                    array_push($data, $row);
                }
            }
        }
         $data = array_reverse($data); //章节列表需要倒叙
        $this->seclist[$id] = $data;
        return  $this->seclist[$id];
    }
    // 获取远程小说详情，根据实际情况修改fun
    public function getbookdetail($book)
    {
        if (!is_array($book)) {
            $ck = Y::$cache->get("spck_" . $book);
            if (is_array($ck[1])) {
                $book = $ck[1];
            } else {
                return;
            }
        }
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
        $id = $remotebookid;
        // $api = "comics/$id/recommendation";
        $api2 = "comics/$id";
        // $api = "/detail?pid=3&id=$remotebookid";
       
        // $datas = $this->apisign($api, []);
        $datas = $this->apisign($api2, []);
        $this->appneedinfo['app-platform']="zh-CN,zh;q=0.9";
        $this->appneedinfo['app-platform']="android";
        // d(Curl::getBashCurl($api2,[],$this->appneedinfo));
        list($status, $bookh) = $this->getdata($datas, ["code", "data"], 200);
        // $html = str_get_html($datas);
         $sec = $this->gethttpsec($remotebookid);
        if(!$status)return;
        //第三方内容中对应与本数据库字段对应
        $refield = [
            "cartoon_name" => "name",
            "bookname" => "name",
            "writer_name" => "writer_name",
            "desc" => "desc",
            "update_status" => 'update_status',
            "wordnum" => "wordnum",
            "section" => "section",
            "bpic" => "pic",
            "fid" => "id",
            'bpic_detail' => "bpic_detail",
            'tmpcate' => "tmpcate",
            'tmptag' => "tmptag",
        ];
        $data = $book;
        $book=$bookh['comic'];
        if ($sec) {
            $data['desc'] = $book['description'];
            $data['section'] = sizeof($sec);
            $data['wordnum'] = $book['section'];
            $data['update_status'] = $book['finished']?1:2;
            // $data['tmptag'] = $data['finished']?1:2;
            $data['tmptag'] = implode(",", $book['tags']);
            $data['tmpcate'] =   implode(",", $book['categories']);
        }
      
        if ($data) {
            // $data = $this->fixtoon($data, $refield);
            $this->insertdetail($data, $refield);
        } else {
            $this->debuginfo("详情原因" . $data);
        }
    }
    //免费收费状态在这里
    public $field = [
        "title" => "title",
        "isfree" => "isfree",
        "secid" => "secid",
        'secnum' => 'secnum'
    ];
    // 获取远程章节列表，根据实际情况修改fun
    public function getseclist($id, $dbid)
    {
        $data = $this->gethttpsec($id);
        if ($data) {
            //取得章节列表，对比现有章节数量相同就跳出
            //必须return 不然无法统计
            return   $this->section_asyn($id, $dbid, $data, $this->field);
        } else {
            $this->debuginfo("章节中断");
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
        }
        //这里是密文拉取
        $data = $this->getremoc($remote_book_id, $remote_sec_id, $remote_sec_num);
        //密文解密
        if ($data) {
            // 参数 rondom+bid+cid+字符串“com.internationalization.novel”   MD516位小写 就是解密key
            $out = [];
            // array_push($out, (object) ['url' =>  $data['episodeCover'], "name" =>  '0', "id" => '0']);
            foreach ($data as $key => $picobj) {
                $pic =  Ngmatch::fiximgstr($picobj);
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
        $page=1;
        $data = [];
        $do=true;
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        while ($do) {
            $api = "comics/$remote_book_id/order/$remote_sec_id/pages?page=$page";
            $datas = $this->apisign($api, []);
            list($status, $bookh) = $this->getdata($datas, ["code", "data"], 200);
            if(!isset($bookh['pages']))return;
            $blist=$bookh['pages']['docs'];
            if ($blist) {
                foreach ($blist as $key => $value) {
                    $src =$this->img_domian.$value['media']['path'];
                    array_push($data, $src);
                }
            }
           
            if($page==$bookh['pages']['pages']){
                $do=false; 
            }
            $page++;
        }
         
        if ($data) {
            return ($data);
        } else {
            d("中断原因" . $datas);
        }
        return false;
    }
    //解锁接口
    //***********************************工具性************************************** */
    //http请求入口，根据实际情况，把一些固定值写进去
    public function apisign($api, $parem, $post = null)
    {
        $time = time();
       //  $time =  "1716212710";
        $sign = $this->sign($api, "GET", $time);
        $this->appneedinfo['time'] = $time;
        $this->appneedinfo['nonce'] = $this->nonce;
        $this->appneedinfo['signature'] = $sign;
        // "comics?page=3&c=Cosplay&s=dd1716199121t4yrwc3mwp5pa3st2pkytetmrfxftjjsGETC69BAF41DA5ABD1FFEDC6D2FEA56B"
        $this->head($this->appneedinfo);
        //    $s= Curl::getBashCurl($this->domian.$api,[],$this->appneedinfo);
        // d($s);
        // js数据
        // var raw = url.replace(BaseUrl, "") + ts + getNonce() + method + appleKillFlag;

        $data = $this->get($api);
        return $data;
        // $data = $this->post($api, $parem);
    }
    public function sign($api, $method, $time)
    {
        // var raw = url.replace(BaseUrl, "") + ts + getNonce() + method + appleKillFlag;
        $str = $api . $time . $this->nonce . $method . $this->appleKillFlag;
        $str = strtolower($str);
        // 密钥（对应你的 appleVersion）  
        $appleVersion = $this->appleVerSion;
        // 使用 hash_hmac 函数生成 HMAC-SHA256 哈希值  
        $hmac = hash_hmac('sha256', $str, $appleVersion, true);
        // 将二进制哈希值转换为十六进制字符串  
        $hmacHex = bin2hex($hmac);
        return $hmacHex;
    }
    //接口值判断类，$field[0]判断索引，$field[1]需要返回的摄影,$field[0] ==$value 返回treu
    public function getdata($data, $field = [], $value = '')
    {
        return  $this->check($data, $field, $value);
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
}
