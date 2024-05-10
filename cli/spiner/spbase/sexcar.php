<?php

/**
 * 爱奇艺漫画
 * 列子 ：php opsock 192.168.1.1 8080
 */

namespace ng169\cli\spiner\spbase;

require_once   dirname(dirname(dirname(__FILE__))) . "/clibase.php";



use ng169\Y;
use ng169\cli\Clibase;
use ng169\tool\Curl;

im(TOOL . "simplehtmldom/simple_html_dom.php");
class sexcar extends Clibase
{
    public  $_booktype = 2; //书籍类型
    public  $_booklang = 5;  //书籍语言
    public  $_bookdstdesc_int = 23; //书籍来源描述
    public  $_bookdstdesc = "色情--盗版"; //书籍来源描述
    public  $_domian = "https://qq.com.nxl5mb.top"; //书籍来源描述
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
    public $token = "";
    // authCookie: 
    public $appneedinfo = [];
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

        $this->logend($this->upcount ?? 0, $this->upinfo, sizeof($this->rmbookid));

        $this->thcache($cachename);
        $this->thstart(__FILE__, $cachename);
        d("任务结束");
    }
    public function processBookArray($bookArray)
    {
        // 过滤id，保留数字
        // $id = is_string($bookArray['id']) ? preg_replace('/[^0-9]/', '', $bookArray['id']) : null;

        preg_match('/id=(\d+)/', $bookArray['id'], $matches);
        if (!empty($matches)) {
            $id = $matches[1];
            // echo $id; // 输出: 463
        } else {
            d('没有找到匹配的 ID 值。');
        }


        // 保留pic地址，去除可能的CSS样式声明
        // $pic = is_string($bookArray['pic']) ? trim($bookArray['pic']) : null;\
        $picMatch = array();
        if (is_string($bookArray['pic']) && preg_match('/url\(([^\)]+)\)/', $bookArray['pic'], $picMatch)) {
            $pic = trim($picMatch[1]);
        } else {
            $pic = null;
        }
        if ($pic) {
            $pic = $this->domian . '' . $pic;
        }
        // 过滤desc，去除首位空格
        // $desc = is_string($bookArray['desc']) ? trim($bookArray['desc']) : null;

        // 返回处理后的数组
        return array(
            'id' => $id,
            'name' => $bookArray['name'],
            'pic' => $pic,
            // 'desc' => $desc
        );
    }

   
    // 获取远程小说列表，根据实际情况修改fun
    public function getbooklist($page)
    {
        $post = [];
        $api = "/list?pid=3&page=" . $page;
        $datatmp = $this->apisign($api,  $post);
        d($datatmp, 1);
        $dom =   \str_get_html($datatmp);
        $data = [];
        foreach ($dom->find('div.vod-item-box') as $p) {
            $book = [];
            $book['id'] = $p->find("a")[0]->attr['href'];
            $book['pic'] = $p->find("img")[0]->attr['src'];
            $book['name'] = $p->find(".vod-name")[0]->innertext;
            // $book['desc'] = $p->find(".chapter")[0]->innertext;
            // d($p->find("chapter"));
            $book = $this->processBookArray($book);
            d($book, 1);
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
                    // $this->thpush($book);
                } else {
                    $this->getbookdetail($book);
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
        $api = "/book/$id/";
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
        // list($s, $data) = $this->getdata($datas, ['code', 'data.allCatalog.comicEpisodes']);
        $html = str_get_html($datas);
        $sec = $html->find('#chapterlistload')[0]->find('li a');
        if ($sec) {
            $data = [];
            foreach ($sec as $key => $pli) {
                # code...
                $row = array(
                    'title' => $pli->innertext,
                    'isfree' => 1,
                    'secid' => $pli->attr['href'],
                    'secnum' => '100',
                );
                preg_match_all('/\d+/', $row['secid'], $matches);

                if (is_array($matches)) {
                    $row['secid'] = $matches[0][1];
                }
                array_push($data, $row);
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
        $api = "/book/$remotebookid/";

        $datas = $this->apisign($api, [
            // "id" => $id,
            // "type" => "1",
            // "token" => $this->token
        ]);
        $html = str_get_html($datas);


        $pd = $html->find('.banner_detail_form .info')[0];
        // $sec = $html->find('#chapterlistload')[0];
        $sec = $this->gethttpsec($remotebookid);
        //  d($pd->innertext,1);
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
        ];
        // d($pd->find('.tip')[0]->find('.block')[0]->find('span')[0]->innertext);
        // d($refield,1);
        //更新状态

        // list($statu, $data) = $this->getdata($datas);
        $data = $book;
        if ($sec) {

            $data['section'] = sizeof($sec);
            $data['wordnum'] = $data['section'];
        }
        if ($pd) {
            $data['update_status'] = $pd->find('.tip')[0]->find('.block')[0]->find('span')[0]->innertext;
        }
        if ($pd) {
            $data['desc'] = trim($pd->find('.content')[0]->innertext);
        }
        if ($data['update_status'] == "已完结") {
            $data['update_status'] = 2;
        } else {
            $data['update_status'] = 1;
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
    public function fixsecs($list = null)
    {
        if ($list == null) {
            $tb = $this->dbbook;
            $in['lang'] =  $this->booklang;
            $in['ftype'] = $this->bookdstdesc;
            $list = T($tb)->set_field($this->db_id)->set_where($in)->set_where($this->db_id . ">61820")->get_all(null, 1);
            $this->thread($list, 'fixsecs');
        } else {
            foreach ($list as $key => $value) {
                $this->fixsec($value[$this->db_id]);
            }
        }
    }
    public function catchbook($list = null)
    {
        if ($list == null) {
        


            $this->thread($list, 'catchbook');
        } else {
            foreach ($list as $key => $value) {
                $this->getbookdetail($value);
            }
        }
    }
    public function fiximgurl($list = null)
    {
        if ($list == null) {
            $tb = $this->dbbook;
            $in['lang'] =  $this->booklang;
            $in['ftype'] = $this->bookdstdesc;
            $list = T($tb)->set_field($this->db_id)->set_where($in)->get_all();
        } else {
            foreach ($list as $key => $value) {
                $this->fiximgurls($value[$this->db_id]);
                d($value[$this->db_id]);
            }
        }
    }
    public function fiximgstr($str)
    {
        if (strpos($str, 'content.mkzcdn.com') === false) {
            // 如果不包含，则直接返回原始字符串
            return "";
        }
        // 正则表达式匹配整个图片URL，包括参数
        // 捕获组 (?<=\.com\/)[\w\/-]+ 用于匹配 .com/ 之后的部分，直到出现空格或问号
        // $pattern = '/content\.mkzcdn\.com\/(comic\/page\/\d{8}\/[a-zA-Z0-9]+-\d+x\d+\.jpg)\!page-800-x\?auth_key=[^"]+/';
        // 替换域名为 oss.mkzcdn.com 并移除问号及其后面的参数
        $replacedStr = preg_replace('/content.mkzcdn.com/', 'oss.mkzcdn.com', $str);
        $replacedStr = preg_replace('/\![^\s|^\"]+\"/', '\"', $replacedStr);
        return $replacedStr;
    }
    public function fiximgurls($bookid)
    {
        if (!$bookid) return;
        //这里如果是更新的；吧图片详情的所有连接域名切换成
        //  吧  http://content.mkzcdn.com这个域名；最好 是带上参数；出来的是webp压缩的；方便抓取本地化。
        //    http://oss.mkzcdn.com/image/20211121/6199f366a7b29-1200x2511.jpg!page-800-x?auth_key=1714929763-0-0-28ef364e01ce53cc50f2b18be578c201
        //把更新的重复的；状态改成停止； 
        $dbsec = $this->dbsec;
        $dbseccontent = 'cart_sec_content_' . $this->booklang;
        $datasec = T($dbsec)->set_field("list_order,title,cart_sec_content,v.cart_section_id," . $this->db_id)->set_where([$this->db_id => $bookid, 'status' => 1])->order_by("list_order desc")->join_table(['t' => $dbseccontent, 'cart_section_id', 'cart_section_id'])->get_all();
        if (sizeof($datasec) > 0) {
            //    $rand=rand(0,sizeof($datasec)-1);
            foreach ($datasec as $key => $value) {
                $w = ['cart_section_id' => $value['cart_section_id']];
                $up['cart_sec_content'] = $this->fiximgstr($value['cart_sec_content']);

                if ($up['cart_sec_content'] != null) {
                    T($dbseccontent)->update($up, $w);
                }
            }
        }
    }
    public function fixsec($bookid)
    {

        if (!$bookid) return;
        //这里如果是更新的；吧图片详情的所有连接域名切换成
        //  吧  http://content.mkzcdn.com这个域名；最好 是带上参数；出来的是webp压缩的；方便抓取本地化。
        //    http://oss.mkzcdn.com/image/20211121/6199f366a7b29-1200x2511.jpg!page-800-x?auth_key=1714929763-0-0-28ef364e01ce53cc50f2b18be578c201
        //把更新的重复的；状态改成停止； 
        $dbsec = $this->dbsec;
        $datasec = T($dbsec)->set_field("list_order,title,cart_section_id," . $this->db_id)->set_where([$this->db_id => $bookid, 'status' => 1])->order_by("list_order desc")->get_all();

        if (sizeof($datasec) > 1) {
            $rand = rand(0, sizeof($datasec) - 1);

            foreach ($datasec as $key => $value) {
                $w = ['cart_section_id' => $value['cart_section_id']];
                $up['list_order'] = $key;
                # code...
                // T($dbsec)->set_field("list_order,title,cart_section_id,".$this->db_id)->set_where()->order_by("list_order desc")->get_all(null,1);
                T($dbsec)->update($up, $w);
            }
        }
    }
    // 获取远程章节内容，根据实际情况修改fun
    //传入远程小说id，章节id，章节序号
    public function getcontent($remote_book_id, $remote_sec_id, $remote_sec_num)
    {
        $bid = $remote_book_id;
        $sid = $remote_sec_num;
        if ($remote_sec_num == 200) {
            // d(6, 1);
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
            foreach ($data as $key => $picobj) {
                $pic =  $this->fiximgstr($picobj);
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

        $key = rand(000000, 999999);
        $api = "/chapter/$remote_book_id/$remote_sec_id/";
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        $data = [
            // "token" => $this->token,
            // "episodeId" =>  $sid,
            // "comicId" =>  $bid,
            // "order" =>  0,
            // "size" =>  0,
            // comicId=225640070&episodeId=539170170&order=0&size=0
        ];
        $datas = $this->apisign($api, $data);

        $html = str_get_html($datas);
        $imgs = $html->find('.comiclist img');
        $data = [];
        if ($imgs) {
            foreach ($imgs as $key => $value) {
                array_push($data, $value->attr['data-original']);
            }
        }

        // d($datas, 1);
        // list($s, $data) = $this->getdata($datas, ['code', 'data.episodes.0'], 'A00001');

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
        $data = $this->httpdecode($data);
        return $data;
    }
    //解密http请求数据
    /**
     * 请求的页面出来的是2跟js密文
     * let y="ddd";   需要原密文提取
     *let str="ddds"; 需要原密文提取
     *let t = CryptoJS.AES.decrypt(y, 'window.atob').toString(CryptoJS.enc.Utf8)
     *let h = CryptoJS.AES.decrypt(str, t).toString(CryptoJS.enc.Utf8)
     *然后解密得到新的html然后提取对应数据
     */
    public function  httpdecode($hstr)
    {

        $y = "";
        $str = "";
        if (!$hstr) {
            d("没拉倒数据");
            // return false;
        }
        $pattern = '/let\s+y\s*=\s*\'(.*?)\'/';

        preg_match($pattern, $hstr, $matches);
        if (!empty($matches)) {
            $y = $matches[1];
            // echo $id; // 输出: 463
        } else {
            d('没有找到匹配的 y 值。');
        }
        $patterns = '/let\s+str\s*=\s*\'(.*?)\'/';
        preg_match($patterns, $hstr, $matches);
        if (!empty($matches)) {
            $str = $matches[1];
            // echo $id; // 输出: 463
        } else {
            d('没有找到匹配的 y 值。');
        }
       
        
        $key = 'window.atob'; // 必须是32字节的二进制数据  
      
        $y = 'U2FsdGVkX1855ZWHDfuWj1Yk/F0zs8AD+CqFuvXrfGpskSTwFXgEPDTSbdZF1JyqO7XVZYboKiAWJ0TOWu6XNw==';
        //    $t = openssl_decrypt(base64_decode($y), 'aes-128-cbc', $key, OPENSSL_NO_PADDING);  
        // $key=base64_encode($key);
        $s="33c70ec55d96903ca940bd5f0d00b7fe";
         $am=$this->aesDecrypt($y, $key);
         d($am);
        // $jm=$this->aesDecrypt($am, $key);
        // d("jiemi:".$jm);
        $t = $this->decrypt($y, $key);
        d($t);

        d(1, 1);
    }
    function decrypt($encrypted, $key, $iv = null)
    {
        $purl=new Curl();
        $h=$purl->post('http://192.168.2.109:3000/decode',['str'=>'U2FsdGVkX1855ZWHDfuWj1Yk/F0zs8AD+CqFuvXrfGpskSTwFXgEPDTSbdZF1JyqO7XVZYboKiAWJ0TOWu6XNw==','key'=>'window.atob']);
        d($h);
        // Base64解码密文
    //     if ($iv === null && strlen($encrypted) >= 16) {  
    //         $iv = substr($encrypted, 0, 16);  
    //         $encrypted = substr($encrypted, 16);  
    //     }  
      
    //     // Base64 解码加密字符串  
    //     $encrypted = base64_decode($encrypted);  
    //  d($iv);
    //     // 解密  
    //     $decrypted = openssl_decrypt(  
    //         $encrypted,  
    //         'AES-128-CBC', // 加密算法  
    //         hex2bin($key), // 密钥需要是二进制格式，这里假设密钥是十六进制字符串  
    //         0,             // 选项，0 表示没有额外的加密选项  
    //         $iv            // 初始化向量  
    //     );  
      
    //     // 如果解密失败，返回 false  
    //     if ($decrypted === false) {  
    //         return false;  
    //     }  
      
        // 返回解密后的字符串  
        return $h;  
    }

    //签名类返回签名值
    public function sign($api, $data)
    {
        //h5签名规则

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
