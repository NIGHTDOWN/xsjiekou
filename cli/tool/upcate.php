<?php
namespace ng169\cli\tool;
/**统计缺失的章节，并且去掉重复章节 */




require_once   dirname(dirname(__FILE__)) . "/clibase.php";


use ng169\Y;

class upcate extends Clibase
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
        $where['type'] = $this->_booktype;
        $list = T('mark')->field('bookid,type')->group_by('bookid')->set_limit(2000)->get_all();

        foreach ($list as $v => $b) {
            $this->do($b['bookid'], $b['type']);
        }
        d("任务结束");
    }
    // 获取远程小说列表，根据实际情况修改fun
    public function do($bookid, $type)
    {
        if (!$bookid) return false;

        if ($type == 1) {
            //获取数量
            $tb = 'book';
            $tbid = 'book_id';
        } else {
            $tb = 'cartoon';
            $tbid = 'cartoon_id';
        }
        $old = T($tb)->set_field('category_id,cate_id,lable')->set_where([$tbid => $bookid])->get_one();
        //两个都是空值，就更新
        
        if (!$old['cate_id'] || !$old['lable']) {
            $catetmp = T('mark')->set_where(['bookid' => $bookid, 'type' => $type])->group_by('cate2')->set_field('count(1) as num,bookid,cate2')->order_by('num desc')->get_all();
            //取第0个
            // d($catetmp);
            $cate = $catetmp[0]['cate2'];
            $lable=$this->getlable($bookid, $type);
            T($tb)->update(['cate_id'=>$cate,'lable'=> $lable],[$tbid => $bookid]);
        } else {
            return false;
        }
    }


    public function getlable($bookid, $type)
    {
        //取100个点评，然后取最多的三个；
        $mark=T('mark')->set_field('cate3')->set_where(['bookid' => $bookid, 'type' => $type])->get_all();
        $str='';
        foreach($mark as $v){
            $str.=$v['cate3'];
        }
        // $str = "sdfhletlsflahlajgfd;lsje;r;wj;ralajfe149253573";
        //方法一
        $arr = explode(',',$str);
        $arr= array_filter($arr);
        //字符串分隔到数组中
        $arr = array_count_values($arr);
        //用于统计数组中所有值出现的次数，返回一个数组
        //键名为原数组的键值，键值为出数
        arsort($arr);
        //取三个
        // d($arr);
        // $a3=array_slice($arr,0,3);
        // $a3=array_keys($a3);
        // d($a3);
        $s='';
        $i=0;
        $tmp=$this->gettagarr();
        
        foreach($arr as $k=>$v){
            if($i>=3){
                break;
            }
            if($k){
                // $tagid=T('category')->set_where(['category_name'])->set_where(['category_id'=>$k,'depth'=>3])->get_one();
           
            $tagid=$tmp[$k];
                $s.='L'.$tagid.',';
            }
           $i++;

        }
        return $s;
    }

public function gettagarr(){
    $cache='catetagtmp';
    list($bool,$cache)=Y::$cache->get($cache);
    if($bool){return $cache;}
    $tagid=T('category')->set_where(['category_name'])->set_where(['depth'=>3])->get_all();
    $tagids=array_column($tagid,'category_name','category_id');
    Y::$cache->set($cache,$tagids);
    return $tagids;
}

    // 一些非不要类---------------------------------

    //初始化进程
    public function __construct()
    {
        parent::__construct(); //初始化帮助信息
        $gt = $this->getargv(['type', 'lang', 'bookid', 'tool', 'max']);
        if (isset($gt['type'])) {
            $this->_booktype = $gt['type'];
        }
        if (isset($gt['lang'])) {
            $this->_booktype = $gt['lang'];
        }
        if(isset($gt['bookid'])){
            $this->do($gt['bookid'], $gt['type']);
            die();
        }
    }



    public function help()
    {
        d('1、更新书籍分类标签,支持参数书籍类型type，书籍bookid| 命令upcate.php type=1 bookid=1');
    }
    //重新排序书籍

}
$ob = new upcate();


$ob->start();
