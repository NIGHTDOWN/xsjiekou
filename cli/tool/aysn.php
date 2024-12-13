<?php

/**统计缺失的章节，并且去掉重复章节 */


namespace ng169\cli\tool;

require_once   dirname(dirname(__FILE__)) . "/clibase.php";


use ng169\Y;

class aysn extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 6;  //书籍语言
    public  $_bookdstdesc_int = 2; //书籍来源描述
    public  $_bookdstdesc = "同步"; //书籍来源描述
    public  $_domian = "http://api.lookstory.xyz/api/"; //书籍来源描述
    // public  $_domian = "http://xs2.com/api"; //书籍来源描述
    public  $debug = true;
    public  $wordrate = 3;  //计算字数的时候的倍数比列
    // -------------------app 破解获取的相关信息


    //一些临时数据，无需变动
    public $cachename = 'cacheignorb';
    public  $ignore = [];
    public function start()
    {
        //读取本地所有书籍跟远程对比
        //远程已经完结的记录下次不再读取
        $this->loadig();
        $size = 500;
        $page = 0;
        $pagemx = 5;
        for ($page; $page < $pagemx; $page++) {
            # code...
            $book = T('book')->set_limit([$page, $size])->get_all();

            $this->getbooklist($book);
        }
        $this->saveig();
        $this->logo();
    }
    //获取已经忽略的书籍缓存
    public function loadig()
    {
        $this->cachename = $this->cachename . $this->_booktype;
        list($bool, $data) = Y::$cache->get($this->cachename);
        if ($data) {
            $this->ignore = $data;
        }
    }

    public function saveig()
    {
        Y::$cache->set($this->cachename, $this->ignore);
    }
    private $msgdata = '';
    public function log($info)
    {
        $this->msgdata .= $info;
        d($info);
    }
    public function logo()
    {
        $this->logout($this->msgdata);
    }
    // 获取远程小说列表，根据实际情况修改fun
    public function getbooklist($data)
    {

        $type = $this->_booktype;

        foreach ($data  as $book) {
            $this->_booklang = $book['lang'];
            $dbsec =  M('book', 'im')->gettpsec($type, $this->_booklang);
            if ($type == 1) {
                $id = 'book_id';
            } else {
                $id = 'cartoon_id';
            }

            $secnum = T($dbsec)->set_where([$id => $book[$id], "status" => 1, 'isdelete' => 0])->get_count();
            $this->getrminfo($book[$id], $type, $book['other_name'], $book['lang'], $secnum, $book);
        }
        return sizeof($data);
    }
    public function getrminfo($bid, $type, $book, $lang, $num, $bookobj)
    {
        //返回4个状态，
        // 1存在章节需要
        // 2存在，远程已经完结
        // 3存在章节不需要更新或者远程数据大于本都
        // 4不存在
        if (in_array($bid, $this->ignore)) {
            d('本书已完结');
            return false;
        }
        $data = $this->apisign('/aysnb/check', [
            'type' => $type,
            'secnum' => $num,
            'book' => $book,
            'lang' => $lang,
        ]);


        list($bool, $dta) = $this->getdata($data, ['code', 'result'], 1);
        // d($data, 1);
        if ($bool) {
        } else {
            d('参数不全');
            d($data);
            return 1;
        }
        if ($dta['code'] == 2 && $dta['bookid']) {
            array_push($this->ignore, $bid);
            return false;
        }
        if ($dta['code'] == 3 && $dta['bookid']) {
            return false;
        }
        if ($type == 1) {
            $ids = 'book_id';
        } else {
            $ids = 'cartoon_id';
        }
        if ($dta['code'] == 4) {
            //插入新书
            $id = $this->inrmbook($type, $lang, $book, $bookobj['bpic'], $bookobj['desc'], $bookobj['wordnum'], $num, $bookobj['update_status']);
            $this->log("\n远程插书," . $bookobj[$ids] . "远程id" . $lang . '-' . $id . "|章节数量" . $dta['upnum']);
            if ($id) {
                $start = 0;
                //插入章节
                $this->inrmsec($type, $lang, $bookobj[$ids], $id, $start);
            }
            return true;
        }
        if ($dta['code'] == 1 && $dta['bookid']) {
            $this->log("\n远程更新," . $bookobj[$ids] . "远程id" . $lang . '-' . $dta['bookid'] . "|章节数量" . $dta['upnum']);
            $start = $dta['start'] + 1;
            //插入章节
            $this->inrmsec($type, $lang, $bookobj[$ids],  $dta['bookid'], $start);
            //插入章节
            return true;
        }
    }
    //远程插入书籍
    // 'wnum', 'type', 'lang', 'secnum', 'bpic', 'update_status', 'other_name', 'desc'
    public function inrmbook($type, $lang, $book, $bpic, $desc, $wnum, $num, $update_status)
    {
        $data = $this->apisign('/aysnb/inbook', [
            'type' => $type,
            'secnum' => $num,
            'other_name' => $book,
            'lang' => $lang,
            'wnum' => $wnum,
            'bpic' => $bpic,
            'update_status' => $update_status,
            'desc' => $desc,

        ]);

        list($bool, $dta) = $this->getdata($data, ['code', 'result'], 1);

        if ($bool) {
            // if()
        } else {
            d('书籍插入失败');
        }
        return $dta;
    }
    //远程插入书籍章节
    public function inrmsec($type, $lang, $id, $rmid, $start)
    {
        $dbsec =  M('book', 'im')->gettpsec($type, $lang);
        $dbsecc = M('book', 'im')->gettpseccontent($type, $lang);
        if ($type == 1) {
            $ids = 'section_id';
            $bids = 'book_id';
            $s = 'sec_content';
        } else {
            $ids = 'cart_section_id';
            $bids = 'cartoon_id';
            $s = 'cart_sec_content';
        }
        $secs = T($dbsec)->join_table(['t' => $dbsecc, $ids, $ids])->set_where(' list_order>=' . $start)->set_global_where(["status"       => 1, 'isdelete' => 0, $bids => $id])->order_by(['s' => 'up', 'f' => 'list_order'])->get_all();
        if (is_array($secs) && sizeof($secs) > 0) {
            foreach ($secs as $key => $sec) {
                $p = ['title' => $sec['title'], 'bookid' => $rmid, 'secnum' => $sec['secnum'], 'list_order' => $sec['list_order'], 'isfree' => $sec['isfree'], 'content' => $sec[$s], 'type' => $type, 'lang' => $lang];
                $data = $this->apisign('/aysnb/insec',  $p);

                list($bool, $dta) = $this->getdata($data, ['code', 'result'], 1);

                if ($bool) {
                } else {
                    d($data);
                    d($rmid . '|' . $sec['list_order'] . '章节插入失败');
                }
            }
        }
    }


    //***********************************工具性************************************** */
    //http请求入口，根据实际情况，把一些固定值写进去
    public function apisign($api, $parem)
    {
        // $token = $this->token;
        // $p = [
        //     "time" => time(),
        //     "rnumber" => Rand(1000, 9999),
        //     "token" => $token, //可变
        // ];
        // $parem = array_merge($parem, $p, $this->appneedinfo);
        // $parem["sign"] = $this->sign($api, $parem);
        $this->setproxy();
// 
        $data = $this->post($api, $parem);

        return $data;
    }
    //签名类返回签名值
    public function sign($api, $data)
    {
        ksort($data);
        $signstr = $api;
        foreach ($data as $key => $value) {
            # code...
            $signstr .= $key . "=" . $value;
        }
        $signstr =  $signstr . $this->code;
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
    public function getdata($data, $field = ["status", "data"], $value = 0)
    {
        return  $this->check($data, $field, $value);
    }

    // 一些非不要类---------------------------------

    //初始化进程
    public function __construct()
    {
        parent::__construct(); //初始化帮助信息
        // $gt = $this->getargv(['type::', 'lang::']);

        $this->setdomain($this->_domian);
        // $this->_booktype = $gt['type'];
        // $this->_booklang = $gt['lang'];
        $this->setinfo($this->_booktype, $this->_booklang, $this->_bookdstdesc_int, $this->_bookdstdesc);
        $this->loaddb($this->booktype, $this->booklang);
        $this->logstart(__FILE__);
    }
    //调试类



    public function help()
    {
        d('本地同步到远程-修改参数,支持参数书籍类型type，语言lang');
    }
}
$ob = new aysn();
// $ob->reg();

$ob->start();
