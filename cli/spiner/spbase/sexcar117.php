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
class sexcar117 extends Clibase
{
    //本地node服务信息
    public  $_booktype = 2; //书籍类型
    public  $_booklang = 5;  //书籍语言
    public  $_bookdstdesc_int = 27; //书籍来源描述
    public  $_bookdstdesc = "色情sexcar117男--短篇色情"; //书籍来源描述
    // public  $_domian = "https://qq.com.nxlmtj.top"; //书籍来源描述
    public  $_domian = "https://www.177pica.com/"; //书籍来源描述
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
   
    public function processBookArray($bookArray)
    {
        // 过滤id，保留数字
       $id = Ngmatch::geturllast($bookArray['id'],3);
       $pic = Ngmatch::geturlimgone($bookArray['pic']);
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
        $api = "html/category/cg/cg-cn/page/" . $page;
        $datatmp = $this->apisign($api,  $post);
        $dom =   \str_get_html($datatmp);
        $data = [];
        foreach ($dom->find('.picture-box') as $p) {
            $book = [];
            $book['id'] = $p->find("a")[0]->attr['href'];
            $book['pic'] = $p->find("img")[0]->attr['src'];
            $book['name'] = $p->find(".grid-title")[0]->find('a')[0]->innertext;
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
        $api = "/html/$id";
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
         $hbox = $html->find('.page-links')[0];
        $sec = $hbox->find('a');
        if ($sec) {
            $data = [];
            foreach ($sec as $key => $pli) {
                # code...
                $id=$key+1;
                $row = array(
                    'title' =>"第".($id)."章",
                    'isfree' => 1,
                    'secid' => $id,
                    'secnum' => '100',
                );
                // preg_match_all('/\d+/', $row['secid'], $matches);
            if(($row['secid'])>0 ){
                //过滤掉加载更多的按钮
                array_push($data, $row);
            }
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
        $api = "/html/$id";
        // $api = "/detail?pid=3&id=$remotebookid";
        $datas = $this->apisign($api, []);
        $html = str_get_html($datas);
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
            $data['desc'] = $book['name'];
            $data['section'] = sizeof($sec);
            $data['wordnum'] = $data['section'];
            $data['update_status'] = 2;
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
        $api = "/html/$remote_book_id/$remote_sec_id";
        $bid = $remote_book_id;
        $sid = $remote_sec_id;
        $data = [];
        $datas = $this->apisign($api, $data);
         $html = str_get_html($datas);
        $obj = $html->find('.entry-content')[0];
        if (!$obj) {
            d("详情获取失败" . $bid);
            return false;
        }
        $imgs = $obj->find('img');
        $data = [];
        if ($imgs) {
            foreach ($imgs as $key => $value) {
                $src=$value->attr['data-lazy-src'];
                    array_push($data, $src);
            }
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
        $url = $api;
       
        $data = $this->get($url);
        return $data;
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
