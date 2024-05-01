<?php
//批量上架书，并且更新收费章节
/**
 * 本服务接收两个参数  IP 端口
 * 列子 ：php opsock 192.168.1.1 8080
 */
/*
header('Access-Control-Allow-Origin:*');
// 响应类型
header('Access-Control-Allow-Methods:POST');
// 响应头设置
header('Access-Control-Allow-Headers:x-requested-with,content-type');*/
define('ROOT', dirname(__FILE__) . '/');
define('FTYPE', 1);
#相对URL路径
error_reporting(E_ALL ^ E_NOTICE);
if (!defined('PATH_URL')) {
    define('PATH_URL', '/');
}
//这里配置要修复的书id集合
function start($lang)
{
    //起始id 430  最总 1217
    //最大取2000
    $list = T('book')->set_field('book_id')->set_where(['lang' => $lang])->set_limit(2000)->get_all();
    foreach ($list as $key => $value) {
        # code...
        fix($value['book_id']);
    }
    // for ($i; $i < 21265; $i++) {
    //     # code...
    //     fix($i);
    // }
}
function xhth($str)
{
    // The CEO\'s Surrogate Wife
    $str = str_replace(["\'", "'"], '’', $str);

    return $str;
}


require_once ROOT . 'source/core/enter.php';


function fix($bookid)
{
    //获取章节数量
    //调整上架状态，收费状态
    //调整章节列表收费状态
    $w = ['book_id' => $bookid, 'status' => 2];
    // $w = ['book_id' => $bookid];
    $ins = T('book')->set_field('section,wordnum,lang')->get_one($w); //泰国书

    if (!$ins) return false;
    $tpsec = M('book', 'im')->gettpsec(1, $ins['lang']);
    if (!$ins['wordnum']) {
        M('book', 'im')->fixbooknum($bookid);
    }
    if ($ins['section'] < 25) {
        T($tpsec)->set_where('book_id=' . $bookid)->update(['isfree' => 0]);
        return false; //小于8章的全部设置免费
    }


    $index = intval($ins['section'] / 3);
    T($tpsec)->set_where('book_id=' . $bookid)->set_where('list_order<' . $index)->update(['isfree' => 0]);
    T($tpsec)->set_where('book_id=' . $bookid)->set_where('list_order>=' . $index)->update(['isfree' => 1]);
    T('book')->update(['isfree' => 1, 'status' => 1], $w);
    echo $bookid . '更新成功';
}





start(1);
start(2);
start(3);
// getbookinfo(1015);
