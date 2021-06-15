<?php

/**统计缺失的章节，并且去掉重复章节 */




require_once   dirname(dirname(__FILE__)) . "/clibase.php";


use ng169\Y;

class fixnovel extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 6;  //书籍语言
    public  $_bookdstdesc_int = 2; //书籍来源描述
    public  $_bookdstdesc = "修复重复"; //书籍来源描述
    public  $_domian = "https://www.lanovel.club"; //书籍来源描述
    public  $debug = true;
    public  $wordrate = 3;  //计算字数的时候的倍数比列
    // -------------------app 破解获取的相关信息
    // 签名密钥盐
    public $code = "c1774fb28759d14916a641f04df67bca";
    // aes iv
    public $aesiv = "";
    // aes密钥
    public $aeskey = "";
    //用户token
    public $token = "";
    public $appneedinfo = [
        "version" => "1.3.5",
        "language" => "MS",
    ];

    //一些临时数据，无需变动
    public $upinfo = [];
    public $upcount = 0;
    public $tokens = [];
    public $rmbookid = [];

    public function start()
    {
        $page = 100;
        $i = 0;
        for ($i; $i <= $page; $i++) {
            $data = $this->getbooklist($i);
            if (($data) <= 0) {
                break;
            }
        }
        $this->logend($this->upcount, $this->upinfo, sizeof($this->rmbookid));
        d("任务结束");
    }
    // 获取远程小说列表，根据实际情况修改fun
    public function getbooklist($page)
    {
        $size = 100;
        $gt = $this->getargv(['type', 'lang', 'bookid', 'tool']);
        if (isset($gt['lang'])) {

            $data = T($this->dbbook)->set_field($this->db_id . ',update_status,lang')
                ->set_where(['lang' => $gt['lang']])
                ->set_limit([$page, $size])->get_all();
        } else {
            $data = T($this->dbbook)->set_field($this->db_id . ',update_status,lang')
                // ->set_where(['ftype' => $this->bookdstdesc])
                ->set_limit([$page, $size])->get_all();
        }
        foreach ($data  as $book) {
            $this->_booklang = $book['lang'];
            $this->setinfo($this->_booktype, $this->_booklang, $this->_bookdstdesc_int, $this->_bookdstdesc);
            $this->loaddb($this->booktype, $this->booklang);
            list($qs, $cf) = $this->getseclist_lostAndRepeat($book[$this->db_id]);
            if (sizeof($qs)) {
                // d('丢失章节' . $book[$this->db_id] . '|' . implode(',', $qs));
                $this->debuginfo('丢失章节' . $book[$this->db_id] . '|' . implode(',', $qs));
            }
            if (sizeof($cf)) {
                // d('重复章节' . $book[$this->db_id] . '|' . implode(',', $cf));
                $this->debuginfo('重复章节' . $book[$this->db_id] . '|' . implode(',', $cf));
                $this->deleteRepeatSec($book[$this->db_id], $book['update_status'], $cf);
            }
        }
        return sizeof($data);
    }




    //***********************************工具性************************************** */
    //http请求入口，根据实际情况，把一些固定值写进去
    public function apisign($api, $parem)
    {

        // $this->setproxy();
        $str = ["ProjectId" => 0, 'Text' => $parem];

        $p = [
            "Product" => 'tmt',
            "Action" => 'LanguageDetect',
            "Version" => '2018-03-21',
            "Region" => 'ap-guangzhou',
            "Token" => '',
            "Area" => '',
            "SecretId" => 'AKIDyjGYbPWJPFDwQekKmyub2DF5g2A44BNL',
            "SecretKey" => 'rUt3ye5HG0WnrgGbhZEeqoX1GH3btvhC',
            "Language" => 'zh-CN', //可变
            'JsonData' => json_encode($str)
        ];
        // $parem = array_merge($parem, $p);
        // $parem["sign"] = $this->sign($api, $parem);
        $data = $this->post($api, $p);
        return $data;
    }


    //接口值判断类，$field[0]判断索引，$field[1]需要返回的摄影,$field[0] ==$value 返回treu
    public function getdata($data, $field = ["status", "data"], $value = 0)
    {
        return  $this->check($data, $field, $value);
    }

    // 一些非不要类---------------------------------

    //初始化进程
    public function __construct()
    {
        parent::__construct(); //初始化帮助信息
        $gt = $this->getargv(['type', 'lang', 'bookid', 'tool', 'max']);
        if ($gt['tool'] == 'fix') {
            $this->fix($gt['bookid']);
            die();
        }
        if ($gt['tool'] == 'sb') {
            $this->autosbcity();
            die();
        }
        if ($gt['tool'] == 'sbfix') {
            $this->sbfixcity();
            die();
        }
        if ($gt['tool'] == 'fixsecnum') {
            if (isset($gt['max'])) {
                $this->max = $gt['max'];
            }
            $this->autofixsec();
            die();
        }
        if ($gt['tool'] == 'mtoon') {

            $this->mtoon();
            die();
        }
        // $this->setdomain($this->_domian);
        $this->_booktype = $gt['type'];
        $this->_booklang = $gt['lang'];
        $this->setinfo($this->_booktype, $this->_booklang, $this->_bookdstdesc_int, $this->_bookdstdesc);
        $this->loaddb($this->booktype, $this->booklang);
        $this->logstart(__FILE__);
    }



    public function help()
    {
        d('1、去掉所有重复章节，,支持参数书籍类型type，语言lang| 命令fix.php type=1 lang=1');
        d('2、去掉所有重复章节，,支持参数书籍类型tool，书籍bookid,修改审核状态cgstatus 0不做，1全部审核通过，排序类型desctype，0按照标题数字排序，1按照插入顺序排序| 命令fix.php tool=fix bookid=1007');
        d('3、识别书籍语种,支持参数书籍类型tool，书籍bookid,| 命令fix.php tool=sb bookid=1007');
        d('3、识别书籍语种修复，书籍类型tool，书籍bookid,原国家flang-移动到新国家tlang| 命令fix.php tool=sbfix bookid=1007 flang=1 tlang=2');
        d('5、修复章节数量不准，章节字数为0,支持参数书籍类型tool，书籍bookid,章节字数max| 命令fix.php tool=fixsecnum bookid=1007 max=3000');
        d('6、修复mtoon,去除desc里面版权声明，去除图片上面的标签| 命令fix.php tool=mtoon bookid=1007');
    }
    //重新排序书籍
    public function fix($bookid)
    {
        //获取章节数量
        //调整上架状态，收费状态
        //调整章节列表收费状态
        $w = ['book_id' => $bookid];
        if (!$bookid) return false;
        $ins = T('book')->set_field('lang')->get_one($w); //泰国书
        $dbsec =  M('book', 'im')->gettpsec(1, $ins['lang']);
        $gt = $this->getargv(['desctype', 'cgstatus']);

        // echo $bookid.'更新成功';set_where
        if ($gt['cgstatus']) {
            //是否全是不审核
            // 是
            T($dbsec)->update(['status' => 1], $w);
        }
        $w['status'] = 1;
        $list = T($dbsec)->set_field('section_id,list_order,title')->set_where($w)->order_by('list_order,section_id ASC')->get_all();
        $patterns = "/\d+/"; //第一种
        //$patterns = "/\d/";   //第二种
        // $strs="left:0px;top:202px;width:90px;height:30px";

        foreach ($list as $k => $a) {
            if ($gt['desctype']) {
                T($dbsec)->update(['list_order' => $k + 1], ['section_id' => $a['section_id']]);
            } else {
                preg_match_all($patterns, $a['title'], $arr);

                $index = $arr[0][0];
                if ($index) {
                    if ($index != $a['list_order']) {
                        T($dbsec)->update(['list_order' => $index], ['section_id' => $a['section_id']]);
                    }
                }
            }
            // d(intval($a['title']));
        }
        echo "\n修复" . $bookid;
    }
    /**检查书籍国家吗，并且尝试修复对应的国家 */
    public function autofixsec()
    {
        $size = 500;
        $page = 0;
        $pagemx = 5;
        // $this->loadcity();
        // $this->loadsc();
        if ($this->gettype() == 1) {
            $id = "book_id";
            $tb = "book";
        } else {
            $id = "cartoon_id";
            $tb = "cartoon";
        }


        $gt = $this->getargv(['bookid']);
        if (isset($gt['bookid'])) {
            $book = T($tb)->set_where([$id => $gt['bookid']])->get_one(null, 1);

            $this->ckcity($book);
            die();
        }

        for ($page; $page < $pagemx; $page++) {
            # code...
            $book = T('book')->set_limit([$page, $size])->get_all();
            foreach ($book as $bok) {
                if (!in_array($bok[$id], $this->scuess))
                    $this->ckbook($bok);
            }
        }
        //失败的尝试重新查询
        // $this->trfail();
        // $this->savesc();
    }
    private $max = 3000;
    public function ckbook($book)
    {
        //检查章节数量对不对
        //检查书籍字数数量对不对
        //检查书籍章节字数数量对不对
        $sid = 'section_id';
        $dbsec =  M('book', 'im')->gettpsec(1, $book['lang']);
        $dbsecs =  M('book', 'im')->gettpseccontent(1, $book['lang']);
        $id = 'book_id';

        $secnum0 = T($dbsec)->set_where([$id => $book[$id], "status" => 1, 'isdelete' => 0, 'secnum' => 0])->get_all();
        if (is_array($secnum0) && sizeof($secnum0) > 0) {
            d("\n" . '修复章节字数0的书id' . $book[$id] . '修复章节数量' . sizeof($secnum0));
            foreach ($secnum0 as $key => $sec) {
                # code...
                $w = [$sid => $sec[$sid]];
                $secs = T($dbsecs)->set_where($w)->get_one();
                if (!$secs) {
                    d("\n" . '章节内容记录不存在，书id' . $book[$id] . '章节id' . $sec[$sid]);
                    T($dbsec)->update(['status' => 0], $w);
                } else {
                    $leng = mb_strlen($secs['sec_content']);
                    if ($leng == 0) {
                        d("\n" . '章节内容不存在，书id' . $book[$id] . '章节id' . $sec[$sid]);
                        T($dbsec)->update(['status' => 0], $w);
                    } else {
                        if ($leng > $this->max) {
                            $leng = $this->max;
                        }
                        T($dbsec)->update(['secnum' => $leng], $w);
                    }
                }
            }
        }

        $secnum = T($dbsec)->set_where([$id => $book[$id], "status" => 1, 'isdelete' => 0])->get_count();
        $section = 0;
        if ($secnum != $book['section'] || $book['section'] == 0) {
            $section = $secnum;
        }
        if ($section) {
            $num = T($dbsec)->set_where([$id => $book[$id], "status" => 1, 'isdelete' => 0])->set_field('sum(secnum) as zs')->get_one();
            $num = $num['zs'];
            d("\n" . '修复章节数量的书id' . $book[$id] . '章节数量' . ($secnum) . '总字数' . $num);
            $u = ['section' => $section, 'wordnum' => $num];
            T('book')->update($u, [$id => $book[$id]]);
        }
    }
    public function autosbcity()
    {
        $size = 500;
        $page = 0;
        $pagemx = 5;
        $this->loadcity();
        if ($this->gettype() == 1) {
            $id = "book_id";
            $tb = "book";
        } else {
            $id = "cartoon_id";
            $tb = "cartoon";
        }
        // $this->loadsc();
        $gt = $this->getargv(['bookid']);
        if (isset($gt['bookid'])) {
            $book = T($tb)->set_where([$id => $gt['bookid']])->get_one();

            $this->ckcity($book);
            die();
        }

        for ($page; $page < $pagemx; $page++) {
            # code...
            $book = T($tb)->set_limit([$page, $size])->set_where(['is_virtual' => '0'])->get_all();
            foreach ($book as $bok) {

                $this->ckcity($bok);
            }
        }
        //失败的尝试重新查询
        $this->trfail();
        // $this->savesc();
    }
    public function mtoon()
    {
        $size = 500;
        $page = 0;
        $pagemx = 5;
        if ($this->gettype() == 1) {
            $id = "book_id";
            $tb = "book";
        } else {
            $id = "cartoon_id";
            $tb = "cartoon";
        }
        // $this->loadsc();
        $gt = $this->getargv(['bookid']);
        if (isset($gt['bookid'])) {
            $book = T($tb)->set_where([$id => $gt['bookid']])->get_one();

            $this->fixtoon($book);
            die();
        }

        for ($page; $page < $pagemx; $page++) {
            # code...
            $book = T($tb)->set_limit([$page, $size])->get_all();
            foreach ($book as $bok) {

                $this->fixtoon($bok);
            }
        }
        //失败的尝试重新查询
        // $this->trfail();
        // $this->savesc();
    }
    public function fixtoon($data)
    {

        $desc = $data['desc'];
        $bpic = $data['bpic'];
        preg_match('/\s.*MangaToon.*/', $desc, $booldesc);
        preg_match('/\.[\w]{3,4}(-[\w]{1,})/', $bpic,  $boolbpic);
        //判断desc
        //判断img

        if ($booldesc[0]) {
            d("desc\n" . $booldesc[0]);
            $bool = $this->insure();
           
            if ($bool) {
                $desc1 = str_replace($booldesc[0], '', $desc);
                d($desc1,1);
            }
        }
        if ($boolbpic[1]) {
            d("pic\n" . $boolbpic[1]);
            $bool = $this->insure();
            if ($bool) {
                $bpic1 = str_replace($booldesc[1], '', $bpic);
            }
        }
        if ($desc1 || $bpic1) {
            d($desc1);
            d($bpic1);
        }
    }
    public function insure()
    {
        $get = $this->getin("确认修复么？Y或者N");
        $bool = strtolower(trim($get));
        d($bool);
        return $bool == 'y' ? true : false;
    }
    public function sbfixcity()
    {
        $size = 500;
        $page = 0;
        $pagemx = 5;
        // $this->loadcity();
        // $this->loadsc();
        if ($this->gettype() == 1) {
            $id = "book_id";
            $tb = "book";
        } else {
            $id = "cartoon_id";
            $tb = "cartoon";
        }
        $gt = $this->getargv(['bookid']);
        if (isset($gt['bookid'])) {
            $book = T($tb)->set_where([$id  => $gt['bookid']])->get_one();

            $this->fixcity($book);
            die();
        }

        for ($page; $page < $pagemx; $page++) {
            # code...
            $book = T($tb)->set_limit([$page, $size])->set_where(['is_virtual' => '2'])->get_all();
            foreach ($book as $bok) {

                $this->fixcity($bok);
            }
        }
        //失败的尝试重新查询
        // $this->trfail();
        // $this->savesc();
    }
    public function fixcity($book)
    {
        //尝试修复
        $get = $this->getargv(['bookid', 'tlang', 'flang']);

        $tlang = isset($get['tlang']) ? $get['tlang'] : $book['virtual_coin'];
        $flang = isset($get['flang']) ? $get['flang'] : $book['lang'];
        if (isset($get['bookid'])) {
            //强制移动
            $this->mvsec($book, $flang, $tlang);
            return;
        } else {
            if (isset($get['tlang']) || isset($get['flang'])) {
                //匹配对应
                // d($book['virtual_coin']);

                if ($get['tlang'] == $book['virtual_coin'] || $get['flang'] == $book['lang']) {

                    $this->mvsec($book, $flang, $tlang);
                }
            } else {
                $this->mvsec($book, $flang, $tlang);
            }
        }
    }
    public function gettype()
    {
        $get = $this->getargv(['type']);
        if (isset($get['type'])) {
            return $get['type'];
        }
        return 1;
    }
    public function mvsec($book, $flang, $tlang)
    {
        $type = $this->gettype();
        if ($type == 1) {
            $id = "book_id";
            $sid = "section_id";
            $ssc = "sec_content";
            $tb = "book";
        } else {
            $id = "cartoon_id";
            $sid = "cart_section_id";
            $ssc = "cart_sec_content";
            $tb = "cartoon";
        }


        $secf =   M('book', 'im')->gettpsec($type, $flang);
        $dbseccf = M('book', 'im')->gettpseccontent($type, $flang);
        $sect =   M('book', 'im')->gettpsec($type, $tlang);
        $dbsecct = M('book', 'im')->gettpseccontent($type, $tlang);
        //两边对比取交集
        $secfs = T($secf)->set_where([$id => $book[$id], 'status' => 1, 'isdelete' => 0])->order_by('list_order asc')->get_all();

        if (sizeof($secfs) <= 0) {
            //无章节内容直接退出
            T($tb)->update(['lang' => $tlang, 'is_virtual' => 3, 'virtual_coin' => $flang . $tlang], [$id => $book[$id]]);
            d($book[$id] . '章节' . $book['section'] . '移动' . 0 . '移动成功');
            return;
        }
        $secs = array_column($secfs, null, 'list_order');
        $sec = [];
        $lists = [];
        $sects = T($sect)->set_where([$id => $book[$id], 'status' => 1, 'isdelete' => 0])->set_field('list_order')->order_by('list_order asc')->get_all();

        $listsf = array_column($secfs, 'list_order');
        $listst = array_column($sects, 'list_order');


        $listss = array_diff($listsf, $listst);
        if (sizeof($listss) == sizeof($secs)) {
            $sec =  $secs;
            $lists = array_column($sec, $sid);
        } else {
            foreach ($listss as $v) {
                array_push($sec, $secs[$v]);
                array_push($lists, $secs[$v][$sid]);
            }
        }

        if (sizeof($lists) > 0) {
            if ($lists > 1000) {
                $tmp = array_chunk($lists, 500);
                $secc = [];
                foreach ($tmp as $key => $value2) {
                    $secctmp = T($dbseccf)->wherein($sid, $value2)->get_all();
                    $secc = array_merge($secc, $secctmp);
                }
            } else {
                $secc = T($dbseccf)->wherein($sid, $lists)->get_all();
            }

            //大于1000章的要分段，不然数据库溢出，终端

            $secc = array_column($secc, $ssc, $sid);

            foreach ($sec as $in) {
                $id = $in[$sid];
                unset($in[$sid]);
                $sid = T($sect)->add($in);
                T($dbsecct)->add([$sid => $sid, $ssc => $secc[$id]]);
            }


            T($tb)->update(['lang' => $tlang, 'is_virtual' => 3, 'virtual_coin' => $flang . $tlang], [$id => $book[$id]]);
            d($book[$id] . '章节' . $book['section'] . '移动' . sizeof($lists) . '移动成功');
        } else {
            //无章节内容直接退出
            T($tb)->update(['lang' => $tlang, 'is_virtual' => 3, 'virtual_coin' => $flang . $tlang], [$id => $book[$id]]);
            d($book[$id] . '章节' . $book['section'] . '移动' . 0 . '移动成功');
            return;
        }
    }
    public function loadcity()
    {

        $citys = T('city')->get_all();
        $this->citys = array_column($citys,  'cityid', 'cityname');
        $this->citys1 = array_column($citys,  'cityname', 'cityid');
    }
    public function ckcity($book)
    {
        if ($this->gettype() == 1) {
            $ids = "book_id";
            $tb = "book";
        } else {
            $ids = "cartoon_id";
            $tb = "cartoon";
        }
        $id = $book[$ids];
        $desc = $book['desc'];
        $title = $book['other_name'];
        $lang = $book['lang'];
        $str = $desc ? $desc : $title;
        // $str = substr($str, 0, 200);
        $str = mb_substr($str, 0, 100, 'utf-8');
        $cityid = $this->reqqqsb($str);
        $dbcity = $lang;
        if ($cityid == '-1') {
            $this->debuginfo('书籍id=' . $id . '请求失败' . $this->citys1[$lang] . "\n");
            array_push($this->fail, $book);
            T($tb)->update(['is_virtual' => '0'], [$ids => $id]);
            return;
        }
        if ($cityid != $lang) {
            array_push($this->scuess,  $id);
            T($tb)->update(['is_virtual' => '2', 'virtual_coin' => $cityid], [$ids => $id]);
            $this->debuginfo('书籍id=' . $id . '国家不匹配' . $this->citys1[$lang] . '识别' . $this->citys1[$cityid] . "\n");
        } else {
            T($tb)->update(['is_virtual' => '1'],  [$ids => $id]);
        }
    }
    private $citys;
    private $citys1;
    private $fail = [];
    private $scuess = [];
    //请求失败的尝试重新查询
    public function trfail()
    {
        if (sizeof($this->fail) > 0) {
            foreach ($this->fail as $book) {
                $this->ckcity($book);
            }
        }
    }
    public function  savesc()
    {
        Y::$cache->set($this->cachename, $this->scuess);
    }
    public function     loadsc()
    {
        $this->cachename = 'sb_qq_';
        list($bool, $data) = Y::$cache->get($this->cachename);

        if ($bool) {

            $this->scuess = $data;
        }
    }
    public function reqqqsb($str)
    {
        $data1 = $this->apisign('https://api.cloud.tencent.com/requesttc3', $str);
        $data2 = json_decode($data1, 1);

        $data = $data2['body']['Response']['Lang'];
        $ret = -1;
        if (!$data) {
            d('查询失败' . json_encode($data2['body']));
        } else {

            if (isset($this->citys[$data])) {
                $ret = $this->citys[$data];
            } else {

                d('查询到系统不匹配' . $data);
            }
        }
        return $ret;
    }
}
$ob = new fixnovel();


$ob->start();
