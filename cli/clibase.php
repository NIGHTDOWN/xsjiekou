<?php

/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */
namespace ng169\cli;
use ng169\lib\Log;
use ng169\tool\Cli;
use \ng169\Y;
use \ng169\tool\Curl;
use \ng169\db\daoClass;
define('ROOT', dirname(__FILE__) . '/../');
require_once ROOT . 'source/core/enter.php';

define('FTYPE', 1);
#相对URL路径
error_reporting(E_ALL ^ E_NOTICE);

class BOOK_FROM_TYPE
{
    const  qikan = 0;
    const lanovel = 2;
    const mtoon = 1;
    const aqiyi = 10;
    const qq = 11;
}


class Clibase  extends Cli
{
    public $spiner;
    // 过滤爬虫字符串内容里面的引号，避免数据库注入
    public  $booktype; //书籍类型
    public  $booklang;  //书籍语言
    public  $bookdstdesc; //书籍来源描述
    public  $bookdstdescstr; //书籍来源描述
    public  $domian; //书籍来源描述
    public $dbbook, $dbsec, $dbcontent, $db_id;
    public $logid;
    public $errors = [];
    public $thred_books = [];
    public $thred_books_arr = [];
    public $isthread = false;
    public $in_rmote_db = false;
    public $max_thread = 1500; //最大线程20个
    public function logstart($file)
    {
        $in['filename'] = $file;
        $in['starttime'] = time();
        $in['name'] = $this->bookdstdescstr;
        $in['lang'] =  $this->booklang;
        $in['type'] = $this->booktype;
        $in['flag'] = 0;
        $in['day'] = date('Ymd');
       
        $this->logid = T('spiner')->add($in);
    }
    public function __construct()
    {
       
        parent::__construct(); //初始化帮助信息

    }
    public $th;
    public function getbookdetail($id)
    {
    }
    public function thinit()
    {
        $bookids = $this->getargv(['bookid']);
        if ($bookids['bookid']) {
            $this->getbookdetail($bookids['bookid']);
            die();
        }
        $th = 0;
        $ths = $this->getargv(['t']);

        if (isset($ths['t'])) {
            $th = $ths['t'];
        }

        if ($th) {
            $this->isthread = true;
            if ($th > $this->max_thread) {
                $th =  $this->max_thread;
            }
        }
        $this->th = $th;
        return $th;
    }
    public function thpush($id)
    {
        // if (sizeof($this->thred_books) > 30000) {
        //     d('书籍列表超过6w,请重写遍历函数分割');
        //     return false;
        // }
        if ($this->isthread) {
            //压入数组
            if (!in_array($id, $this->thred_books)) {
                array_push($this->thred_books, $id);
            }
        }
    }
    public function thstart($file, $cachename)
    {
        // foreach ($this->thred_books_arr as $key => $value) {
        //     # code...
        //     $this->threadstrat($file, $cachename, $key);
        // }
        for ($i = 0; $i < $this->th; $i++) {
            # code...
            $this->threadstrat($file, $cachename, $i);
        }
    }
    public function thcache($cachename)
    {
        if (sizeof($this->thred_books) == 0) {
            d('书籍列表空', 1);
        }
        if ($this->isthread) {
            $th = $this->th == 0 ? 1 : $this->th;
            $size = intval(sizeof($this->thred_books) / $th);
            $thred_books_arr = array_chunk($this->thred_books, $size);
        }
        // d(serialize($this),1);
        // Y::$cache->set($cachename . 'arr', json_encode($this->thred_books));
        // Log::txt($this->thred_books_arr);
        // d(json_encode($this->thred_books));
        // $this->thred_books = [];
        // Y::$cache->set($cachename, serialize($this));
        $w['id'] = $this->logid;

        $up['books'] = json_encode($thred_books_arr);
        T('spiner')->update($up, $w);

        // Log::txt(serialize($this));
        // d($this,1);
    }
    //抓取失败的
    public $failcatchold;
    public $failcatchnew;
    public function thcacheobj($cachename)
    {
        Y::$cache->set($cachename, serialize($this));
    }
    //获取上次抓取失败的记录
    public function get_fail_listcache()
    {
        $cachename = 'rmfaillist.' . $this->booktype . $this->booklang . $this->bookdstdesc;
        list($bool, $cache) = Y::$cache->get($cachename);
        // d($cache);
        if ($bool) {
            $this->failcatchold = $cache;
            //去空
            Y::$cache->set($cachename, null);
            $this->do_lastrepet_fail();
            //这里轮询执行拉取失败记录
            return $cache;
        }
        return false;
    }
    //执行上次失败记录重新拉取
    public function do_lastrepet_fail()
    {
        return false; //这里直接跳过，这里经常导致重复插入对应章节
        d('执行上次失败重试开始');

        if ($this->failcatchold) {
            foreach ($this->failcatchold as $index => $book) {
                $bookids = explode('_', $index);
                foreach ($book as $index2 => $sec) {
                    $bool = $this->insetsec($sec, $index2, $this->field, $bookids[1], $bookids[0], $sec[$this->field["secid"]]);
                }
            }
        }

        d('执行上次失败重试结束');
    }
    //保存失败记录
    public function set_fail_listcache()
    {
        $cachename = 'rmfaillist.' . $this->booktype . $this->booklang . $this->bookdstdesc;
        Y::$cache->set($cachename, $this->failcatchnew);
    }
    //记录失败记录
    /**
     * $localbid 本地书籍id
     * ￥localcid  本地章节序号
     * rmbid    远程书籍id
     * rmsecs  远程对应章节信息
     */
    public function save_fail_listcache($localbid, $localcid, $rmbid, $rmsecs)
    {
        $this->failcatchnew[$localbid . '_' . $rmbid][$localcid] = $rmsecs;
    }
    //保存失败的记录到缓存，支持多进程合并
    public function save_fail()
    {
        $cache = $this->get_fail_listcache();
        if ($cache) {
            //合并
            $this->failcatchnew = array_merge($cache, $this->failcatchnew);
        }
        $this->set_fail_listcache();
    }

    //获取多线程远程列表缓存
    public function get_th_listcache()
    {
        $this->get_fail_listcache();
        $cachename = 'rmlist.' . $this->booktype . $this->booklang . $this->bookdstdesc;
        list($bool, $cache) = Y::$cache->get($cachename);

        if ($bool) {
            $this->thred_books = $cache;
            if ($this->isthread) {
                //多线程线程
                return $cache;
            }
            return false;
        }
        return false;
    }
    //设置多线程远程列表缓存
    public function set_th_listcache()
    {
        $cachename = 'rmlist.' . $this->booktype . $this->booklang . $this->bookdstdesc;
        Y::$cache->set($cachename, $this->thred_books, G_DAY * 2);
    }
    // 更新章节数量，更新章节详情，远程数据数量统计
    public function logend($num, $info, $rmnum)
    {
        if (!$this->logid) return false;
        if ($this->isthread) {
            $old = T('spiner')->get_one(['id' => $this->logid]);
            if (!$old) return false;
            $w['id'] = $this->logid;
            $up['flag'] = 1;
            $up['endtime'] = time();
            $up['upnum'] = $num + $old['upnum'];
            $up['innum'] = $this->incount + $old['innum'];
            if (sizeof($this->thred_books)) {
                $up['rmbooknum'] = sizeof($this->thred_books);
            }

            if ($old['upinfo']) {
                $up['upinfo'] = $old['upinfo'] . "|" . time() . "|th" . $rmnum . "|" . json_encode($info);
            } else {
                $up['upinfo'] = $this->dbbook . "|" . $this->dbsec . "|" . $this->dbcontent . "|main" . $rmnum . "|" . json_encode($info);
            }

            T('spiner')->update($up, $w);
            $this->save_fail();
        } else {
            $w['id'] = $this->logid;
            $up['flag'] = 1;
            $up['endtime'] = time();
            $up['upnum'] = $num;
            $up['innum'] = $this->incount;
            $up['rmbooknum'] = $rmnum;
            $up['upinfo'] = $this->dbbook . "|" . $this->dbsec . "|" . $this->dbcontent . json_encode($info);
            T('spiner')->update($up, $w);
            $this->set_fail_listcache();
        }
    }
    public function threadstrat($file, $cachename, $key)
    {
        $shell = 'php  ' . dirname(__FILE__) . '/clibasethred.php';
        $arg = ' thread=1 obj=' . $cachename . ' id=' . $key . ' file=' . $file;
        $shell .= $arg;
        // if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {

        //     $shell = '  start  cmd /k  ' . $shell;
        // } else {

        //     $shell = 'nohup ' . $shell .  ' > /dev/null 2>&1 &';
        // }
        d($shell);
        $this->execInBackground($shell);
    }
    public function execInBackground($cmd)
    {

        if (substr(php_uname(), 0, 7) == "Windows") {
            // 不可见窗口
            // pclose(popen("start /B " . $cmd, "r"));
            // 可见窗口
            pclose(popen("start " . $cmd, "r"));
        } else {

            exec($cmd . " > /dev/null &");
        }
    }
    public function threadcall()
    {
        //接受参数 对象缓存--obj，对象列表序号--id

        $data = $this->getargv(['id', 'obj', 'file', 'rmbookid']);

        list($bool, $cache) = Y::$cache->get($data['obj']);
        require_once $data['file'];
        $obj = unserialize($cache);

        if (isset($data['rmbookid'])) {
            $obj->getbookdetail($data['rmbookid']);
            return true;
        }
        // list($bool, $arr) = Y::$cache->get($data['obj'] . 'arr');
        $arrs = T('spiner')->get_one(['id' => $obj->logid]);
        $arr = json_decode($arrs['books'], 1);

        foreach ($arr[$data['id']] as $id) {
            $obj->getbookdetail($id);
        }
        //日志记录
        // Log::txt($this);
        $obj->logend($obj->upcount, $obj->upinfo, $data['id']);
        //  $this->getbookdetail($book[$remote_bookarr_id]);
        // d(($cahce), 1);
    }
    public function logout($info)
    {
        if (!$this->logid) return false;
        $w['id'] = $this->logid;
        $up['flag'] = 2;
        $up['endtime'] = time();
        if (!$this->isthread) {
            $up['innum'] = $this->incount??0;
            $up['upnum'] = $this->upcount??0;
        }
        // $up['upnum'] = $num;s
        $up['endreson'] = $info;
        T('spiner')->update($up, $w);
    }
    public function help()
    {
        d("参数bookid 指定要抓的远程书籍id\n参数t 多少个子线程，最大不超过20个线程\n如 t=15开15个线程", 1);
    }
    //获取缺失章节序号
    protected function getlostMemberInArray($array)
    {
        if (!is_array($array)) {
            return [];
        }
        if (sizeof($array) <= 0) {
            return [];
        }
        //创建一个数组
        $new_arr = range(1, max($array));
        //使用array_diff查找缺少的元素
        return array_diff($new_arr, $array);
    }
    //$value远程值，$eq远程值与asset相等,表示完结1，否则返回2
    public function getbookisend($value, $eq)
    {
        if ($value == $eq) {
            return 1;
        }
        return 2;
    }
    //$value远程值，$eq远程值与asset相等,表示收费1，否则返回免费0
    public function getsecisfree($value, $eq)
    {
        if ($value == $eq) {
            return 1;
        }
        return 0;
    }
    //获取重复章节
    protected function getRepeatMemberInArray($array)
    {
        if (!is_array($array)) {
            return [];
        }
        if (sizeof($array) <= 0) {
            return [];
        }
        // 获取去掉重复数据的数组 
        $unique_arr = array_unique($array);
        // 获取重复数据的数组 
        $repeat_arr = array_diff_assoc($array, $unique_arr);
        return $repeat_arr;
    }
    public  function xhth($str)
    {
        $str = str_replace(["\'", "'"], '’', $str);
        return $str;
    }
    //初始化curl
    public function init()
    {
        if (!$this->spiner) {

            $this->spiner = new \ng169\tool\Curl();
        }
    }
    //检查章节是否重复
    public function checkrep($bookid, $listorder)
    {
        $w = [$this->db_id      => $bookid, "list_order"   => $listorder, "status"       => 1, 'isdelete' => 0];
        if ($this->in_rmote_db) {
            $in = T($this->dbsec)->set_field('list_order')->set_where($w)->get_sql();
            $in =  $this->sql($in, 1);
        } else {
            $in = T($this->dbsec)->set_field('list_order')->set_where($w)->get_one();
        }

        $bool = false;
        $qs = [];
        $cf = [];
        if ($in) {
            //如果存在，说明章节又漏，记录日志,并且尝试修复

            list($qs, $cf) = $this->getseclist_lostAndRepeat($bookid);

            $bool = true;
        }
        return [$bool, $qs, $cf];
    }
    public function getseclist_lostAndRepeat($bookid)
    {

        $w = [$this->db_id      => $bookid,  "status"       => 1, 'isdelete' => 0];


        if ($this->in_rmote_db) {
            $list = T($this->dbsec)->set_field('list_order,' . $this->db_id)->order_by('list_order asc')->set_where($w)->get_sql();
            $list =  $this->sql($list, 1);
        } else {
            $list = T($this->dbsec)->set_field('list_order,' . $this->db_id)->order_by('list_order asc')->set_where($w)->get_all();
        }
        $list = array_column($list, 'list_order');

        $qs = $this->getlostMemberInArray($list);
        $cf = $this->getRepeatMemberInArray($list);
        return [$qs, $cf];
    }
    // 下架重复的章节,小说id，连载状态，重复序号
    public function deleteRepeatSec($bookid, $updatestatus, $secids)
    {
        if (sizeof($secids) <= 0) {
            return false;
        }
        $w = [$this->db_id      => $bookid,  "status"       => 1, 'isdelete' => 0];
        if ($this->booktype == 1) {
            $field = 'section_id';
        } else {
            $field = 'cart_section_id';
        }

        // if ($this->in_rmote_db) {
        //     $list = T($this->dbsec)->set_field($field)->set_where($w)->wherein('list_order', $secids)->order_by($this->db_id . ' desc')->group_by('list_order')->get_sql();
        //     $list =  $this->sql($list, 1);
        // } else {
        $list = T($this->dbsec)->set_field($field)->set_where($w)->wherein('list_order', $secids)->order_by($this->db_id . ' desc')->group_by('list_order')->get_all();
        // }
        // d($list);
        $list = array_column($list, $field);
        if (sizeof($list) <= 0) {
            return false;
        }
        if (sizeof($list) > sizeof($secids)) {
            $msg = $bookid . '取出的去重数量不对' . implode(',', $list);
            d($msg);
            $this->logout($msg);
            // return false;
        }
        T($this->dbsec)->set_field($field)->set_where($this->db_id . " =" . $bookid . " and status  =1 and isdelete = 0 ")->wherein($field, $list)->update(['status' => 0]);
        if ($updatestatus == 1) {
            T($this->dbbook)->update(['update_status' => 2], [$this->db_id      => $bookid,]);
        }
        M('book', 'im')->clearcache($this->booktype,  $bookid);
    }
    // 设置请求的数据头参数
    /**$head
     * 内容  为“name:val”,不是“name”=>“val”的数组
     */
    public function head($head)
    {
        $this->init();
        $this->spiner->head($head);
    }
    //获取小说列表，循环列表每一本书
    // abstract public function getbooklist($page);
    // //获取小说介绍
    // abstract public  function getbookdetail($remotebookid);
    // //获取小说章节
    // abstract public  function getseclist($id, $dbid);
    // //获取章节内容
    // abstract public  function getcontent($remote_book_id, $remote_sec_id, $remote_sec_num);
    // //解锁章节
    // abstract public  function unlock($remote_book_id, $remote_sec_id, $remote_sec_num);
    //解密
    public function aes_cbc_nopadding($data = '', $privateKey, $iv)
    {

        return openssl_decrypt(base64_decode($data), "AES-128-CBC", $privateKey, OPENSSL_NO_PADDING, $iv);
    }
    //签名
    // abstract public  function sign($api, $data);
    // //注册
    // abstract public  function reg();
    //设置接口域名
    public  function setdomain($domian)
    {
        $this->domian = $domian;
    }
    public  function setinfo($booktype, $booklang, $bookdstdesc, $bookdstdescstr)
    {
        $this->booktype = $booktype;
        $this->booklang = $booklang;
        $this->bookdstdesc = $bookdstdesc;
        $this->bookdstdescstr = $bookdstdescstr;
    }
    //请求走代理
    public function setproxy($ip = '', $port = '')
    {
        $this->init();
        $this->spiner->setproxy($ip, $port);
    }
    // 请求
    public function post($api, $data, $time = 10)
    {
        $this->init();

        if ($this->ip && $this->port) {
          
            $this->setproxy($this->ip, $this->port);
        }
        $url = $this->domian . $api;
     if($this->ip && $this->port){
        $data = $this->spiner->post($url, ($data), $this->ip.":". $this->port, $time);
     }else{
        $data = $this->spiner->post($url, ($data), null, $time);
     }
        

        return $data;
    }
  
    public function get($api, $data = null, $time = 10)
    {
        $this->init();
       
        if ($this->ip && $this->port) {
            $this->setproxy($this->ip, $this->port);
        }
        $url = $this->domian . $api;
        
        if ($this->iscli()) {
        d($url);
    }
        $data = $this->spiner->get($url, $time);
        return $data;
    }
    public $proxystr = null;
    public $ip = null;
    public $port = null;
    public $proxylist = [];
    /**
     * int $type 代理模式1每次使用一个ip代理，2代理ip随机切换
     */
    public function autoproxy($type = 1)
    {
        //这里要用缓存
        $proxystrindex = 'proxystrindex';
        $data = T('option')->set_where(['option_name' => 'open_proxy'])->get_one();
        if (!$data['option_value'] && !G_CLI_DEBUG) {
            p('不使用代理');
            Y::$cache->set($proxystrindex, null, 0);
            return;
        }

        if (G_CLI_DEBUG) {
            $proxy = explode(':', G_CLI_DEBUG);
            $this->ip = $proxy[0];
            $this->port = $proxy[1];
            p($this->ip . ':' . $this->port);
            return;
        }
        d(2);
        list($bool, $data) = Y::$cache->get($proxystrindex);
        if ($bool) {
            $this->proxystr = $data;
        } else {
            if (!$this->proxystr) {
                $data = T('option')->set_where(['option_name' => 'ok_proxy'])->get_one();
                // $jdata = json_decode($data['option_value'], true);
                $this->proxystr = explode(',', $data['option_value']);
                $this->proxystr = array_filter($this->proxystr);
                Y::$cache->set($proxystrindex, $this->proxystr, 1800);
            }
        }
        if (!sizeof($this->proxystr)) {
            return false;
        }

        if ($type == 1) {
            if ($this->ip) return;
            $index = rand(0, sizeof($this->proxystr) - 1);
            $proxy = $this->proxystr[$index];
            p($proxy);
            $proxy = explode(' ', $proxy);
            $this->ip = $proxy[0];
            $this->port = $proxy[1];
        }
        if ($type == 2) {
            $index = rand(0, sizeof($this->proxystr));
            $proxy = $this->proxystr[$index];
            $proxy = explode(' ', $proxy);
            $this->ip = $proxy[0];
            $this->port = $proxy[1];
        }
    }

    // 返回的数据
    // abstract public function getdata($data);
    public function check($data, $field, $value)
    {
       
        $data = json_decode($data, 1);
        $status = false;
        $ret = '';
        if ($data[$field[0]] == $value) {
            $status = true;
            //
            $need = explode('.', $field[1]);
            if (sizeof($need) == 1) {
                $ret = $data[$field[1]];
            } elseif (sizeof($need) == 2) {
                $ret = $data[$need[0]][$need[1]];
            } elseif (sizeof($need) == 3) {
                $ret = $data[$need[0]][$need[1]][$need[2]];
            }
        }
        return [$status, $ret];
    }
    public function loaddb($type, $lang)
    {
        if ($type == 1) {
            $this->dbbook = 'book';
            $this->db_id = 'book_id';
        } else {
            $this->dbbook = 'cartoon';
            $this->db_id = 'cartoon_id';
        }
        $this->dbsec =  M('book', 'im')->gettpsec($type, $lang);
        $this->dbcontent =  M('book', 'im')->gettpseccontent($type, $lang);
    }
    public function logerror($info)
    {
        if (!in_array($info, $this->errors, true)) {
            array_push($this->errors, $info . "\n");
            $this->logout(implode(",", $this->errors));
        }
    }
    public function logerror2($type, $info)
    {
        if (!in_array($info, $this->errors, true)) {
            array_push($this->errors, $type);
            array_push($this->errors, $info . "\n");
            $this->logout(implode(",", $this->errors));
        } else {
            // array_push($this->errors, $type . "\n");
            // $this->logout(implode(",", $this->errors));
            $this->logerror($type);
        }
    }
    // public function __construct()
    // {
    //     parent::__construct();
    // }
    //*************************************************************************** */
    //调试类
    public function debuginfo($type, $info = null)
    {
        if (!$info) {
            $this->logerror($type);
            if ($this->debug) {
                d($type, null, null, 1);
            }
        } else {
            $this->logerror2($type, $info);
            if ($this->debug) {
                d($type . $info, null, null, 1);
            }
        }

        // if ($this->debug) {
        //     d($info, null, null, 1);
        // }
    }

    //计算章节字数
    public function calcsecnum($content)
    {
        $num = intval(strlen($content) / $this->wordrate);
        return $num;
    }
    public $endlist = null;
    //获取完结小说id集合
    public function getend()
    {
        if ($this->endlist) {
            return $this->endlist;
        } else {
            $w = [
                'ftype' => $this->bookdstdesc,
                'lang' => $this->booklang,
                'update_status' => '1',
            ];
            if ($this->booktype == 1) {
                $db = 'book';
                $id = 'book_id';
            } else {
                $db = 'cartoon';
                $id = 'cartoon_id';
            }
            $list = T($db)->set_field($id . ',fid')->set_where($w)->get_all();
            $this->endlist = array_column($list, 'fid');
            return $this->endlist;
        }
    }
    //判断是否完结
    public function isend($rmbookid)
    {
        $endlist = $this->getend();
        if (in_array($rmbookid, $endlist)) {
            return true;
        } else {
            return false;
        }
    }

    //插入或者更新书详情
    public function insertdetail($remotedata, $refield)
    {
        $thisbook = [];
        $data = $remotedata;
        $id = $remotedata[$refield["fid"]];
        $data["other_name"] = $this->xhth($data[$refield["bookname"]]);
        $data["book_desc"] = $this->xhth($data[$refield["desc"]]);
        //这里加缓存，避免多次数据库操作
        if (!$id) {
            d('书籍不存在');
            return false;
        }
        $fcache_index = "spiner_" . $id . "_" . $this->booklang . "_" . $this->booktype . $this->bookdstdesc;

        $cache = Y::$cache->get($fcache_index);
        if ($cache[0]) {
            $dbbook = $cache[1];
        } else {
            if ($this->in_rmote_db) {
                $dbbook = T($this->dbbook)->set_where(["fid" => $id, "ftype" => $this->bookdstdesc, "lang" => $this->booklang])->get_one();
                $dbbook = $this->sql($dbbook, 1);
            } else {
                $dbbook = T($this->dbbook)->get_one(["fid" => $id, "ftype" => $this->bookdstdesc, "lang" => $this->booklang]);
            }


            if ($dbbook) {
                //缓存一天
                if ($dbbook["update_status"] == 1) {
                    //永久缓存
                    Y::$cache->set($fcache_index, $dbbook, 0);
                } else {
                    //否则缓存一天
                    Y::$cache->set($fcache_index, $dbbook, G_DAY);
                }
            }
        }

        if (!$dbbook) {

            if ($this->in_rmote_db) {
                $dbbook = T($this->dbbook)->set_where(["other_name" => $data["other_name"], "lang" => $this->booklang])->get_sql();
                $dbbook = $this->sql($dbbook, 1);
            } else {
                $dbbook = T($this->dbbook)->get_one(["other_name" => $data["other_name"], "lang" => $this->booklang]);
            }
        }
        // d($this->in_rmote_db, 1);
        // d($dbbook, 1);
        $up = [];
        $dbid = 0;
        if ($dbbook) {
            if ($dbbook["update_status"] == 1) {
                d("完结跳出,远程id" . $id . "数据库id" . $dbbook[$this->db_id] . "\n");
                return false;
            } else {
                //更新书籍章节以及更新信息

                if ($data["update_section"]) {
                    $up["section"] = $data[$refield["section"]];
                    $up["update_time"] = time();
                    // $up["update_status"] = $this->getbookisend($data[$refield["update_status"]], 1); //状态2为完结 ，1为连载
                }
            }
            $dbid = $dbbook[$this->db_id];
        } else {
            //添加新小说
            $category_id = $this->get_category_id(@$data[$refield["category_id"]]);
            $cate_id = $this->get_cate_id($category_id, @$data[$refield["cate_id"]]);
            $lable =   $this->get_lable($cate_id, @$data[$refield["lable"]]);
            $add = [
                "fid" => $id,
                "ftype" => $this->bookdstdesc,
                "writer_name" => $data[$refield["writer_name"]],
                // "book_name" => $data["other_name"],
                "status" => 2, //下架
                "wordnum"   => $data[$refield["wordnum"]],
                "section"   => $data[$refield["section"]],
                "bpic" => $data[$refield["bpic"]],
                "isfree" => 1, //没该字段，所有数据都是收费章节
                "desc"  => $data["book_desc"],
                "money" => $this->booktype == 1 ? 0.6 : 60, //小说就是0.6，漫画就是60
                "lang" => $this->booklang,
                "create_time" => time(),
                "update_time" => time(),
                "update_status" => '2',
                // "update_status" => $this->getbookisend($data[$refield["update_status"]], 1), //状态2为完结 ，1为连载
                "other_name" => $data["other_name"],
                'category_id' => $category_id,
                'cate_id' =>    $cate_id,
                'lable' =>      $lable,
                'cate_name' =>       @$data[$refield["lable"]],
            ];

            if ($this->in_rmote_db) {

                $dbid = $this->rmsqladd($this->dbbook, $add);
            } else {
               
                $dbid = T($this->dbbook)->add($add);
            }

            $dbbook = $add;
            d("添加新书远程id" . $id . "数据库ID" . $dbid . "\n");
        }
        $this->lastbid = $dbid;
        $this->last = 0;
        $thisbook['bookid'] = $dbid;
        $thisbook['upnum'] = 0;
        $thisbook['type'] = $this->booktype;
        $thisbook['lang'] = $this->booklang;

        //更新章节，并且获取更新的章节数量
        //更新数量，远程数量，实际更新数量
        list($upmu, $zjnum, $innum) = $this->getseclist($id, $dbid);
        
        if ($upmu) {
            $thisbook['upnum'] = $innum;
            $thisbook['needupnum'] = $upmu;
            $thisbook['day'] = date('Ymd');
            $thisbook['uptime'] = time();
            T('book_up_log')->add($thisbook);

            //清空缓存
            //添加更新记录表
        }
        if ($upmu || $dbbook['update_status'] != 1) {
            //更新章节更新时间
            //更新章节数量
            $up["section"] = $zjnum;
            $up["update_time"] = time();
            if ($upmu) {
                //如果有需要更新的章节才压入
                $this->upinfo[$dbid] = $upmu;
            }

            $this->upcount = $this->upcount + $upmu;
            $this->incount = $this->incount + $innum;
            //如果更新状态跟数量相同；就把连载状态根据实际情况该便

            if ($innum == $upmu) {
                $up["update_status"] = $this->getbookisend($data[$refield["update_status"]], $this->update_status_end_val); //状态2为完结 ，1为连载
            }
            //完结状态需要在最后更新，避免拉取不完整，下次缺不在拉取
            // T($this->dbbook)->update($up, [$this->db_id =>  $dbid]);
            if ($this->in_rmote_db) {

                $dbid = $this->rmsqlupdate($this->dbbook, $up, [$this->db_id =>  $dbid]);
            } else {
                // $dbid = T($this->dbbook)->add($add);
                T($this->dbbook)->update($up, [$this->db_id =>  $dbid]);
            }
            //清除这边书的缓存
            M('book', 'im')->clearcache($this->booktype, $dbid);
        }
    }
    public $update_status_end_val = 1;
    public $is_un_free_val = 1;
    public $incount;
    //获取男女类别
    public function get_category_id($data)
    {
        if (!$data) return 0;
    }
    //获取标签
    public function get_lable($cateid, $data)
    {
        if (!$data) return 0;
    }
    //获取分类
    public function get_cate_id($cateid, $data)
    {
        if (!$data) return 0;
    }

    //子类复写
    public function getseclist($id, $dbid)
    {
    }
    //子类复写
    public function getcontent($remote_book_id, $remote_sec_id, $remote_sec_num)
    {
    }
    public $field;
    //章节同步,$remotedatas远程章节列表，字段映射表
    public function section_asyn($id, $dbid, $remotedatas, $field)
    {
        if (!$field) {
            $field = $this->field;
        }
        $data = $remotedatas;

        if ($this->in_rmote_db) {
            $secnum = $this->sql(T($this->dbsec)->set_where([$this->db_id => $dbid, "status" => 1, 'isdelete' => 0])->get_sql(), 3);
        } else {
            $secnum = T($this->dbsec)->set_where([$this->db_id => $dbid, "status" => 1, 'isdelete' => 0])->get_count();
        }

        $innum = 0;
        $rnum = sizeof($data);
        if ($rnum > $secnum) {
            //更新最新的章节
            $upnum = $rnum - $secnum;
            d("【{$id} |db {$dbid}】远程更新" . sizeof($data) . "更新章节数量" . $upnum);

            for ($j = 0; $j <= $upnum; $j++) {
                $newsecid = $secnum;
                $remotedata = $data[$newsecid];
                if (!$remotedata) {
                    break;
                }
                $listorder = $newsecid + 1;
                //取到了内容就更新

                list($check, $qs, $cf) = $this->checkrep($dbid, $listorder);

                if ($check) {
                    if (sizeof($cf) > 0) {
                        $this->debuginfo('章节重复' . $dbid . '||'  . implode(',', $cf));
                    }
                    if (sizeof($qs) > 0) {
                        $this->debuginfo('缺失章节修复' . $dbid . '||'  . implode(',', $qs));
                        foreach ($qs as $listord) {
                            $index = $listord - 1;
                            $rmdata = $data[$index];
                            //修复缺失
                            $bool = $this->insetsec($rmdata, $listord, $field, $id, $dbid, $rmdata[$field["secid"]]);
                            if ($bool) {
                                $innum++;
                            } else {
                                $this->save_fail_listcache($dbid, $listord, $id, $rmdata);
                            }
                        }
                    }
                    $secnum++;
                    break; //跳过当前序号内容插入
                } else {
                   
                    $bool = $this->insetsec($remotedata, $listorder, $field, $id, $dbid, $remotedata[$field["secid"]]);
                    if ($bool) {
                        $innum++;
                    } else {
                        $this->save_fail_listcache($dbid, $listorder, $id, $remotedata);
                    }
                }
                $secnum++;
            }
            return [$upnum, $rnum, $innum];
        } else {
            d(" {$id}|{$dbid} 章节已经是最新\n");
            return [0, $rnum, 0];
        }
    }

    //远程章节入库
    public function insetsec($remotedata, $listorder, $field, $id, $dbid, $rmsecid)
    {

        $seccontrent = $this->getcontent($id, $rmsecid, $listorder);

        if (!$seccontrent) {
            //内容获取失败
            return false;
        }

        $secadd = [
            // "section_id"   => "",
            "title"        => $this->xhth($remotedata[$field["title"]]),
            "list_order"   => $listorder,
            $this->db_id      => $dbid,
            "create_time"  => date("Y-m-d H:i:s"),
            "update_time"  =>  date("Y-m-d H:i:s"),
            "check_time"  =>  date("Y-m-d H:i:s"),
            "crontab_time"  =>  date("Y-m-d H:i:s"),
            "status"       => 1,
            'user_id'=>0,
            'check_name'=>'spiner',
            "isfree"       => $this->getsecisfree($remotedata[$field["isfree"]], $this->is_un_free_val),
        ];
        if ($this->booktype == 1) {
            $secadd["sec_word"] = $listorder - 1;

            if ($remotedata[$field["secnum"]]) {
                //字段包含字数就直接入库
                $secadd["secnum"] = $remotedata[$field["secnum"]];
            } else {

                $secadd["secnum"] = $this->calcsecnum($seccontrent); //字数远程没数据，需要计算,因为字数太大，单章收费太高；所以除以19算出新字数在入库;
            }
        } else {
            $secadd["charge_coin"] = 60;
        }
        //如果是最新一张变成免费了；需要调整，上一章是否免费，如果同一本书上一张是收费。接下来一定是收费
        if ($listorder > 35 && $secadd["isfree"] == 0 && $this->last == 1 &&  $this->lastbid == $dbid) {
            //获取一些出现后面都是免费，所以要调整成收费
            $secadd["isfree"] = 1;
        }
        //避免同一序号章节重复插入
        $this->last = $secadd["isfree"];
        if ($this->in_rmote_db) {
            $secid = $this->rmsqladd($this->dbsec, $secadd);
        } else {
            $secid = T($this->dbsec)->add($secadd);
        }

        if ($secid) {
            //章节添加成功
            if ($this->booktype == 1) {
                $a2 = ["sec_content" => $this->xhth($seccontrent), "section_id" => $secid];
                // T($this->dbcontent)->add($a2);
            } else {
                $a2 = ["cart_sec_content" => json_encode($seccontrent), "cart_section_id" => $secid];
                // T($this->dbcontent)->add($a2);
            }
            if ($this->in_rmote_db) {
                $this->rmsqladd($this->dbcontent, $a2);
            } else {
                T($this->dbcontent)->add($a2);
            }
        }
        return true;
    }
    private function sql($sql, $type)
    {

        $data = $this->post2('/api/index/sql', ['sql' => $sql, 'type' => $type]);
        d($sql);
        d($data);
        return $data;
    }
    private function rmsqladd($table, $inarray)
    {
        return 1;
    }
    private function rmsqlupdate($table, $inarray, $where)
    {
        return 1;
    }
    public function post2($api, $data)
    {
        $spiner = new \ng169\tool\Curl();
        $url = 'http://xsapi.ng169.com' . $api;
        $data = $spiner->post($url, $data);
        return $data;
    }
}
