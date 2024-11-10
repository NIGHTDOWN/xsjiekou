<?php

/**统计缺失的章节，并且去掉重复章节 */


namespace ng169\cli\tool;

require_once   dirname(dirname(__FILE__)) . "/clibase.php";


use ng169\Y;
use ng169\tool\File;
use \ng169\tool\Image;
use \ng169\cli\Clibase;
// dsl处理
class dslcartoon extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 0;  //书籍语言
    public  $_bookdstdesc_int = 2; //书籍来源描述
    public  $_bookdstdesc = "把图片生成dsl地址"; //书籍来源描述
    public  $_domian = "https://api.github.com/"; //书籍来源描述
    public  $debug = true;
    public  $path = '';  //计算字数的时候的倍数比列
    // -------------------app 破解获取的相关信息
    // 签名密钥盐
    public $max = "";
    public $git = "github";
    public $min = "";
    // aes iv
    public $bookid = "";
    public $dsldomain = "";
    // aes密钥
    public $db2 = "";
    //用户token
    public $db = "";
    public $do = 0;
    public $appneedinfo = [
        "version" => "1.3.5",
        "language" => "MS",
    ];

    //一些临时数据，无需变动
    public $list = [];

    public $fail = [];
    public $ok = [];
    public $showret = true;
    public $loop = 1;

    public function start()
    {
        //获取列表书籍列表
        //取1000条分词

        $w = ['status' => 1]; //审核状态正常的
        if ($this->bookid) {
            $w['cartoon_id'] = $this->bookid;
        }

        for ($i = 0; $i < 500; $i++) {
            # code...
            $list = T($this->db)->join_table(['t' => $this->db2, 'cart_section_id', 'cart_section_id'])->set_field('cartoon_id,v.cart_section_id,list_order,cart_sec_content,cart_sec_content_dsl')->set_limit([$i, 1000]);
            if ($w) {
                // 指定具体id书籍
                $list = $list->set_where($w);
            }
            if ($this->max) {
                $list = $list->set_where(' v.cart_section_id <=' . $this->max . " ");
            }
            if ($this->min) {
                $list = $list->set_where(' v.cart_section_id >=' . $this->min . " ");
            }
            if ($this->do == 2) {
                $list = $list->set_where($this->db2 . '.isdelete>0 ');
            } else if ($this->do == 1 || $this->do == 0) {
                $list = $list->set_where(' ISNULL(cart_sec_content_dsl) ');
            }

            // $list = $list->set_where(' bpic like "%webp-%" ');
            // $list = $list->set_where(' book_id>2000 ');

            $list = $list->get_all();

            if (sizeof($list) > 0) {
                $this->loop($list);
            } else {
                break;
            }
        }
        d('执行完成');
    }
    public function loop($booklist)
    {
        if (!$booklist) {
            d('数据错误');
            return false;
        }
        foreach ($booklist as $book) {
            $pic = $book['cart_sec_content'];


            if ($this->do == 0) {


                list($dsl, $lost) = $this->getimg($pic, $book['cartoon_id'], $book['list_order']);


                T($this->db2)->update(['cart_sec_content_dsl' => json_encode($dsl), 'isdelete' => $lost], ['cart_section_id' => $book['cart_section_id']]);
            } else if ($this->do == 2) {

                list($dsl, $lost) = $this->fiximg($pic, $book['cartoon_id'], $book['list_order'], $book['cart_sec_content_dsl']);
                T($this->db2)->update(['cart_sec_content_dsl' => json_encode($dsl), 'isdelete' => $lost], ['cart_section_id' => $book['cart_section_id']]);
            } else if ($this->do == 4) {
                // if ($book['cart_sec_content_dsl']) {
                //     return false;
                // }

                if (!$this->dsldomain) {
                    d('缺少必要参数dsl域名', 1);
                }
                $dsl = json_decode($book['cart_sec_content_dsl'], 1);
                if (is_array($dsl)) {
                    if ($dsl['dsl']) {
                        //不执行
                        continue;
                    }
                }

                $up = ['pic' => $this->getdsllist($pic, $book['cartoon_id'], $book['list_order']), 'dsl' => $this->dsldomain];

                T($this->db2)->update(['cart_sec_content_dsl' =>  json_encode($up)], ['cart_section_id' => $book['cart_section_id']]);
            } else if ($this->do == 5) {
                // if ($book['cart_sec_content_dsl']) {
                //     return false;
                // }

                if (!$this->dsldomain) {
                    d('缺少必要参数dsl域名', 1);
                }
                $dsl = json_decode($book['cart_sec_content_dsl'], 1);
                if (is_array($dsl)) {
                    if (!$dsl['dsl']) {
                        //不执行
                        continue;
                    }
                }

                $up = ['cart_sec_content' => $this->getdsllist3($pic, $book['cartoon_id'], $book['list_order'])];

                T($this->db2)->update(['cart_sec_content' =>  json_encode($up)], ['cart_section_id' => $book['cart_section_id']]);
            }
        }


        //执行拉取到本地操作
    }
    public function apisign($api, $parem)
    {
        $this->setproxy();
        $this->head($this->appneedinfo);
        $data = $this->post($api, $parem);
        return $data;
    }
    //抓图片
    public function getimg($imgs, $id, $listorder)
    {
        $p = $this->path;
        $imgs = json_decode($imgs, 1);
        $imgs = $imgs['cart_sec_content'];
        $ret = [];
        if (!$imgs) return [false, 0];
        // $filename = $this->_booktype . '_' . $id . '.png';
        $filenames = '/' . $this->_booklang . "/$id/$listorder/";

        $size = sizeof($imgs);
//保存的图片要加随机数；避免直接猜出
        foreach ($imgs as $k => $img) {
            $index = $k + 1;
            $filename = $filenames . $index ."_".rand(1,999). '.webp';


            $img = $img['url'];
            // $img = 'http://pic.cc/' . "$id/$listorder/$index" . '.png';

            $file = Image::imgtolocalwebp($img, null, $filename, $p);

            if ($file) {
                $mock = 'dsl://' . $file;
                $ret[$k]['url'] = $mock;
                $size--;
            } else {

                d("$id/$listorder/$k/失败");
            }
        }


        return [$ret, $size];
    }
    public function fiximg($imgs, $id, $listorder, $dsl)
    {
        $p = $this->path;
        $imgs = json_decode($imgs, 1);
        $imgs = $imgs['cart_sec_content'];
        $ret = [];
        if (!$imgs) return [false, 0];
        // $filename = $this->_booktype . '_' . $id . '.png';
        $filenames = '/' . $this->_booklang . "/$id/$listorder/";

        $size = sizeof($imgs);

        foreach ($imgs as $k => $img) {
            $filename = $filenames . ($k + 1) . '.png';



            $file = Image::imgtolocal($img['url'], null, $filename, $p);

            if ($file) {
                $mock = 'dsl://' . $file;
                $ret[$k]['url'] = $mock;
                $size--;
            } else {

                d("$id/$listorder/$k/失败");
            }
        }

        // if ($file) {
        //     return $mock;
        // }

        // return null;
        return [$ret, $size];
    }
    // 获取远程小说列表，根据实际情况修改fun
    public function getdsllist($imgs, $id, $listorder)
    {

        $imgs = json_decode($imgs, 1);
        $imgs = $imgs['cart_sec_content'];
        $ret = [];
        if (!$imgs) return [false, 0];
        if ($this->git == 'github') {
            $filenames =  "$id/master/$listorder/";
        } else {
            $filenames =  "a_$id/raw/master/$listorder/";
        }


        // https://raw.githubusercontent.com/lookstory/51034/master/1/5.png
        // https://gitee.com/lookstory/a_50019/raw/master/81/10.png
        $size = sizeof($imgs);

        foreach ($imgs as $k => $img) {
            $filename = $filenames . ($k + 1) . '.png';
            $mock = 'dsl://' . $filename;
            $ret[$k]['url'] = $mock;
            $size--;
        }
        return $ret;
    }
    public function getdsllist3($imgs, $id, $listorder)
    {

        $imgs = json_decode($imgs, 1);
        $imgs = $imgs['cart_sec_content'];
        $ret = [];
        if (!$imgs) return [false, 0];
        if ($this->git == 'github') {
            $filenames =  "$id/master/$listorder/";
        } else {
            $filenames =  "a_$id/raw/master/$listorder/";
        }


        // https://raw.githubusercontent.com/lookstory/51034/master/1/5.png
        // https://gitee.com/lookstory/a_50019/raw/master/81/10.png
        $size = sizeof($imgs);

        foreach ($imgs as $k => $img) {
            $filename = $filenames . ($k + 1) . '.png';
            $mock = $this->dsldomain . '/' . $filename;
            $ret[$k]['url'] = $mock;
            $size--;
        }
        return $ret;
    }


    // 一些非不要类---------------------------------

    //初始化进程
    public function __construct()
    {
        parent::__construct(); //初始化帮助信息
        $gt = $this->getargv(['lang', 'do', 'bookid', 'path', 'max', 'min', 'dsl', 'git', 't']);


        $this->setdomain($this->_domian);
        if (isset($gt['lang'])) {
            $this->_booklang = $gt['lang'];
        }
        if (isset($gt['git'])) {
            $this->git = $gt['git'];
        }
        if (isset($gt['bookid'])) {
            $this->bookid = $gt['bookid'];
        }
        if (isset($gt['max'])) {
            $this->max = $gt['max'];
        }
        if (isset($gt['min'])) {
            $this->min = $gt['min'];
        }
        if (isset($gt['dsl'])) {
            $this->dsldomain = $gt['dsl'];
        }
        $this->db = 'cartoon_section';
        $this->db2 = 'cart_sec_content';
        if ($this->_booklang == 0) {
        } else {
            $this->db = $this->db . '_' . $this->_booklang;
            $this->db2 = $this->db2 . '_' . $this->_booklang;
        }

        $this->path = '/soft/cp/cartoon_section/';
        if ($this->_booktype == 1) {
        }
        if (isset($gt['path'])) {
            $this->path = $gt['path'];
        }

        if (isset($gt['do'])) {
            $this->do = $gt['do'];
            // die();
        }
        if (isset($gt['t'])) {
            // 开启多线程执行
            $arg = $gt;
            unset($arg['t']);
            $num = $gt['t'];
            $q = ' ';
            foreach ($arg as $v => $n) {
                $q .= "$v=$n ";
            }
            //拆分线程
            $w = ['status' => 1]; //审核状态正常的
            if ($this->bookid) {
                $w['book_id'] = $this->bookid;
            }
            $count = T($this->db)->set_where($w)->get_count();
            // d($count);
            # code...

            for ($i = 0; $i < $num; $i++) {
                # code...
                $min = intval($count / $num * $i);
                $max = intval($count / $num * ($i + 1));
                $in = " max=$max  min=$min ";
                $comm = "php " . __FILE__ . $q . $in;
                // d($comm);
                $this->execInBackground($comm);
            }
            die();
        }

        if ($this->do == 3) {
            $this->initgithub();
            die();
        }
    }



    public function help()
    {
        d('1、检查把卡通详情图片抓取到本地生成dsl链接,参数lang 指定书籍类型，git 仓库类型github，gitee,bookid指定书籍id，max section_id最大，min section_id最小,path指定保存位置,dsl 更新dsl域名，更新链接是必须传，do 类型1抓取完成上传到图床之后更新服务器短dsl链接，类型2：修复没完全更新到本地的图片链接,3 初始化github每本漫画仓库,4 更新dsl链接,5直接更新cart_sec_content字段图片；域名,t开多少个线程执行拉取操作：例子php dsl.php lang=0 min=1000 max=5000 ');
    }
    //重新排序书籍
    public function initgithub()
    {
        if ($this->bookid) {
            $w = ['cartoon_id' => $this->bookid];
        }
        $w['lang'] = $this->_booklang;
        for ($i = 0; $i < 500; $i++) {
            # code...
            $list = T('cartoon')->set_field('cartoon_id as book_id')->set_limit([$i, 1000]);
            if ($w) {
                // 指定具体id书籍
                $list = $list->set_where($w);
            }

            $list = $list->get_all();

            if (sizeof($list) > 0) {
                foreach ($list as $book) {
                    if ($this->git == 'github') {
                        $this->creategithub($book);
                    } else {

                        $this->creategitee($book);
                    }
                }
            } else {
                break;
            }
        }
    }
    //创建github仓库
    public function creategithub($book)
    {
        //生成github token 
        // 要想通过api来操作你的github，必须要先在github的网址（https://github.com/settings/tokens）上生成一个访问令牌
        $this->appneedinfo = [
            'Authorization' => 'token 82e4abce9e0c2158f0969282dff498349eb3dacd'
        ];
        $post = [
            'name' => $book['book_id'],
        ];
        $data = $this->apisign('user/repos', json_encode($post));

        d($data);
    }
    //创建github仓库
    public function creategitee($book)
    {
        //生成github token 
        // 要想通过api来操作你的github，必须要先在gitee的网址（https://gitee.com/personal_access_tokens）上生成一个访问令牌
        $this->appneedinfo = [
            "Content-Type: application/json;charset=UTF-8",
            // "User-Agent: Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/70.0.3538.25 Safari/537.36 Core/1.70.3861.400 QQBrowser/10.7.4313.400",
        ];
        $this->domian = "https://gitee.com/api/v5/";
        $post = [
            'access_token' => "d91d04445b874f7bf25c16fea50e3392",
            'name' => "a_" . $book['book_id'],
            'has_issues' => "true",
            'has_wiki' => "true",
            'can_comment' => "true",
        ];

        $data = $this->apisign('user/repos', json_encode($post));

        d($data);
    }
    public function datacode($array)
    {
        $ret = '';
        foreach ($array as $k => $v) {
            $k = urlencode($k);
            $v = urlencode($v);
            $ret .=  "$k=$v&";
        }
        $ret = trim($ret, '&');
        $ret = ($ret);
        return $ret;
    }
}
$ob = new dslcartoon();


$ob->start();
