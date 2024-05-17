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
use ng169\tool\Ngmatch;

im(TOOL . "simplehtmldom/simple_html_dom.php");
class sexcar91 extends Clibase
{
    //本地node服务信息
    public $lcaesnodeserver = "http://127.0.0.1:3000/decode";
    public  $_booktype = 2; //书籍类型
    public  $_booklang = 5;  //书籍语言
    public  $_bookdstdesc_int = 24; //书籍来源描述
    public  $_bookdstdesc = "色情91--盗版"; //书籍来源描述
    // public  $_domian = "https://qq.com.nxlmtj.top"; //书籍来源描述
    public  $_domian = "https://jymh02.com/"; //书籍来源描述
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
    public function strgetid($str)
    {
        preg_match('/chapter\/(\d+)/', $str, $matches);
        $id = "";
        if (!empty($matches)) {
            $id = $matches[1];
            // echo $id; // 输出: 463
        } else {
            d('没有找到匹配的 ID 值。');
        }
        return $id;
    }
    public function processBookArray($bookArray)
    {
        // 过滤id，保留数字
       $id = Ngmatch::geturllast($bookArray['id']);
        return array(
            'id' => $id,
            'name' => $bookArray['name'],
            'pic' => $bookArray['pic'],
            // 'desc' => $desc
        );
    }


    // 获取远程小说列表，根据实际情况修改fun
    public function getbooklist($page)
    {  
        $post = [];
        $api = "category/page/" . $page;
        $datatmp = $this->apisign($api,  $post);

        $dom =   \str_get_html($datatmp);
        $data = [];
       
        foreach ($dom->find('.common-comic-item') as $p) {
            $book = [];
          
            $book['id'] = $p->find("a")[0]->attr['href'];
            $book['pic'] = $p->find("img")[0]->attr['data-original'];

            $book['name'] = $p->find(".comic__title a")[0]->innertext;
            // $book['desc'] = $p->find(".chapter")[0]->innertext;
            // d($p->find("chapter"));
          
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
                    // $this->thpush($book);
                } else {
                    $this->getbookdetail($book);
                    d($book,1);
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
        $api = "/comic/$id";
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
       
        $html = str_get_html($datas);

        // $hbox = $html->find('.j-chapter-item');

        $sec = $html->find('.j-chapter-link');
       
        if ($sec) {
            $data = [];
            foreach ($sec as $key => $pli) {
                # code...
                $row = array(
                    'title' => trim($pli->innertext),
                    'isfree' => 1,
                    'secid' => Ngmatch::geturllast($pli->attr['href']),
                    'secnum' => '100',
                );
                // preg_match_all('/\d+/', $row['secid'], $matches);

                // if (is_array($matches)) {
                //     $row['secid'] = $matches[0][1];
                // }
                // $row['secid'] = $this->strgetid($row['secid']);
                array_push($data, $row);
            }
        }
        // $data = array_reverse($data); //章节列表需要倒叙
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
        $api = "/comic/$id";
        // $api = "/detail?pid=3&id=$remotebookid";
        $datas = $this->apisign($api, []);
        $html = str_get_html($datas);

        // $pd = $html->find('.banner_detail_form .info')[0];
        $desc = $html->find('.intro-total')[0]->innertext;
        $upstatus = $html->find('.de-chapter__title')[0]->find("span")[0]->innertext;
        // $bpic_detail = $html->find('.box-back')[0]->attr['style'];
        // $sec = $html->find('#chapterlistload')[0];
        $sec = $this->gethttpsec($remotebookid);

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
        ];

        $data = $book;
        if ($sec) {
            $data['desc'] = $desc;
            $data['section'] = sizeof($sec);
            $data['wordnum'] = $data['section'];
            $data['update_status'] = 2;
        }
       
        // if ($pd) {
        //     $data['update_status'] = $pd->find('.tip')[0]->find('.block')[0]->find('span')[0]->innertext;
        // }
        // if ($pd) {
        //     $data['desc'] = trim($pd->innertext);
        // }
        if ($upstatus == "连载：") {
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
        return   Ngmatch::fiximgstr($str);
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
        $api = "/chapter/$remote_sec_id";
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        $data = [];
        $datas = $this->apisign($api, $data);
        $html = str_get_html($datas);

        //  $imgsstr=$html->find('.my-box')[1]->innertext;

        //  $pattern = '/data-echo="([^"]+)"/';

        //  // 使用 preg_match_all 来找到所有匹配的内容
        //  preg_match_all($pattern, $imgsstr, $matches);

        //  // $matches[1] 包含所有匹配的 data-echo 属性的值
        //  $imgs = $matches[1];


        $obj = $html->find('.rd-article-wr')[0];
        // d($obj=="");
        if (!$obj) {
            d("详情获取失败" . $bid);
            return false;
        }
        $imgs = $obj->find('img');
        $data = [];
        if ($imgs) {
            foreach ($imgs as $key => $value) {
                array_push($data, $value->attr['data-original']);
            }
        }
        if ($data) {
            return ($data);
            // return ["key" => $key, "data" => $data];
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
        $url = $api;

        $data = $this->get($url);
        // $data = $this->httpdecode($data);
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
        $t = $this->decrypt($y, $key);
        $data = $this->decrypt($str, $t);
        return $data;
    }
    function sendPostRequest($url, $postData)
    {
        // 初始化cURL会话
        $ch = curl_init($url);
        // 设置cURL选项
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // 返回而不是输出内容
        curl_setopt($ch, CURLOPT_POST, true); // 设置请求方法为POST
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData)); // 设置POST字段的数组
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/x-www-form-urlencoded'
        )); // 设置Content-Type头信息，根据你的服务端需要可能需要调整
        // 执行cURL会话
        $response = curl_exec($ch);
        // 关闭cURL会话

        // 检查是否有错误发生
        if (curl_errno($ch)) {
            curl_close($ch);
            d("解密失败请检查有没有运行node服务，本任务与spbase下的node(aesnode.js)需要搭配使用");;
        } else {
            curl_close($ch);
            // 返回响应数据
            return $response;
        }
    }
    //调用服务器node服务解密---因为php解密函数没写出来；
    function decrypt($encrypted, $key, $iv = null)
    {
        $data = ['str' => $encrypted, 'key' => $key];
        $data = ($data);
        $h = $this->sendPostRequest($this->lcaesnodeserver, $data);
        return $h;
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
