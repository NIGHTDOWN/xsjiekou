<?php

/**
 * 爱奇艺漫画
 * 列子 ：php opsock 192.168.1.1 8080
 */

namespace ng169\cli\spiner\spbase;

require_once   dirname(dirname(dirname(__FILE__))) . "/clibase.php";



use ng169\Y;
use ng169\cli\Clibase;

im(TOOL . "simplehtmldom/simple_html_dom.php");
class sexcar extends Clibase
{
    public  $_booktype = 2; //书籍类型
    public  $_booklang = 5;  //书籍语言
    public  $_bookdstdesc_int = 23; //书籍来源描述
    public  $_bookdstdesc = "色情--盗版"; //书籍来源描述
    public  $_domian = "https://qq.com.nxlrvi.top"; //书籍来源描述
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
            d( '没有找到匹配的 ID 值。');
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

// public function getbooklistall($pagemax){
//     $cachecindex="seccar";
//    $cc= Y::$cache->get( $cachecindex);
//    if($cc[0]){
//     $this->thred_book=$cc[1];
//    }else{
//     $this->thred_book=[];
//     for ($i=0; $i <= $pagemax; $i++) {
//         $size = $this->_getbooklist($i);
//         if (!$size) {
//             //分页已经没东西了，直接退出
//             break;
//         }
//     }
//     Y::$cache->set( $cachecindex,$this->thred_book,G_DAY);
//    }
   
//     return $this->thred_book;
// }
// public function _getbooklist($page)
// {
//     $post = [];
//     $api = "/category/page/$page.html";
//     $datatmp = $this->apisign($api,  $post);
//     $dom =   \str_get_html($datatmp);
//     $data = [];
//     foreach ($dom->find('div.mh-item') as $p) {
//         $book = [];
//         $book['id'] = $p->find("a")[0]->attr['href'];
//         $book['pic'] = $p->find(".mh-cover")[0]->attr['style'];
//         $book['name'] = $p->find("a")[0]->attr['title'];
//         $book['desc'] = $p->find(".chapter")[0]->innertext;
//         // d($p->find("chapter"));
//         $book = $this->processBookArray($book);
//         array_push($data, $book);
//     }
//     //返回数据里面数据id字段
//     $remote_bookarr_id = "id";
//     if (is_array($data) && sizeof($data) > 0) {
//         d("远程拉取小说数量" . sizeof($data));
//         foreach ($data  as $k=>$book) {
//             // $this->thpush($book[$remote_bookarr_id]);
//             array_push($this->thred_book,$book);
//         }
//         return sizeof($data);
//     }
//     return 0;
// }
    // 获取远程小说列表，根据实际情况修改fun
    public function getbooklist($page)
    {
        $post = [];
        $api = "/list?pid=3&page=".$page;
        $datatmp = $this->apisign($api,  $post);
        d($datatmp,1);
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
            d($book,1);
            array_push($data, $book);
        }
        //返回数据里面数据id字段
        $remote_bookarr_id = "id";

        if (is_array($data) && sizeof($data) > 0) {
            d("远程拉取小说数量" . sizeof($data));
            foreach ($data  as $k=>$book) {

                if ($this->isthread) {
                   Y::$cache->set("spck_".$book[$remote_bookarr_id],$book,G_DAY*2);     
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
        
        if(!is_array($book)){
           
           $ck= Y::$cache->get("spck_".$book);  
          
           if(is_array($ck[1])){
            $book=$ck[1];
           }else{
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
            "writer_name"=>"writer_name",
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
            // $tb = $this->dbbook;
            // $in['lang'] =  $this->booklang;
            // $in['ftype'] = $this->bookdstdesc;
            // $list = T($tb)->set_field($this->db_id)->set_where($in)->set_where($this->db_id . ">61820")->get_all(null, 1);
        //    $list=$this->getbooklistall(1000);
        
           
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
        $data=$this->httpdecode($data);
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
    public function  httpdecode($hstr){
        
        $y="";
        $str="";
        if(!$hstr){
            d("没拉倒数据");
            // return false;
        }
        $pattern = '/let\s+y\s*=\s*\'(.*?)\'/';

        preg_match($pattern, $hstr, $matches);
        if (!empty($matches)) {
            $y = $matches[1];
            // echo $id; // 输出: 463
        } else {
            d( '没有找到匹配的 y 值。');
        }
        $patterns = '/let\s+str\s*=\s*\'(.*?)\'/';
        preg_match($patterns, $hstr, $matches);
        if (!empty($matches)) {
            $str = $matches[1];
            // echo $id; // 输出: 463
        } else {
            d( '没有找到匹配的 y 值。');
        }
        $y = 'U2FsdGVkX18NkRdBfXeY75Z1nPw+mxxMtkxrarUPifv8YPzYLijrGBPGPRINiYPH6/YFweN9ZE7yluyp7UCBqg==';
        $str = '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';
    
       
        


        $key = 'window.atob'; // 必须是32字节的二进制数据  
        // $keyBase64 = 'd2luZG93LmF0b2I='; // 替换为实际的Base64编码AES密钥  
  
        // // 解码Base64密钥  
        // $key = base64_decode($keyBase64); 
        $y = 'U2FsdGVkX18NkRdBfXeY75Z1nPw+mxxMtkxrarUPifv8YPzYLijrGBPGPRINiYPH6/YFweN9ZE7yluyp7UCBqg==';
        //    $t = openssl_decrypt(base64_decode($y), 'aes-128-cbc', $key, OPENSSL_NO_PADDING);  
        //     $t=base64_encode($t);
        $t=$this->decrypt($str,'148acb5f7b5060f5fc1c31ae888ea60e');
        d($t);
        
        d(1,1);
    }
        function decrypt($data, $key)
        {
            //支持php5
           
            //支持php8
            //$decrypted = openssl_decrypt($data, 'AES-128-CBC', $key,2, $iv);
            $options = OPENSSL_NO_PADDING;
            $decrypted = openssl_decrypt(base64_decode($data), 'AES-128-CBC', $key,$options);
           
            d($key);
            d($decrypted);
            // $json_str = rtrim($decrypted, "\0");
            return ($decrypted);
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
