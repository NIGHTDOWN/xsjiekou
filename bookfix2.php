<?php
//修复章节序号不对
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
    $w = ['book_id' => $bookid, 'status' => 1];
    $ins = T('book')->set_field('lang')->get_one($w); //泰国书
    $dbsec =  M('book', 'im')->gettpsec(1, $ins['lang']);
    // if (!$ins) return false;
    // if ($ins['section'] < 8) return false;
    // $index = intval($ins['section'] / 3);
    // T('section')->set_where('book_id=' . $bookid)->set_where('list_order<' . $index)->update(['isfree' => 0]);
    // T('section')->set_where('book_id=' . $bookid)->set_where('list_order>=' . $index)->update(['isfree' => 1]);
    // T('book')->update(['isfree' => 1, 'status' => 1], $w);
    // echo $bookid.'更新成功';
    $list = T($dbsec)->set_field('section_id,list_order,title')->get_all($w);
    $patterns = "/\d+/"; //第一种
    //$patterns = "/\d/";   //第二种
    // $strs="left:0px;top:202px;width:90px;height:30px";

    foreach ($list as $a) {
        preg_match_all($patterns, $a['title'], $arr);

        $index = $arr[0][0];
        if ($index) {
            if ($index != $a['list_order']) {
                T($dbsec)->update(['list_order' => $index], ['section_id' => $a['section_id']]);
            }
        }
        // d(intval($a['title']));
    }
    echo "\n修复" . $bookid;
}



function start()
{
    //起始id 430  最总 1217
    $i = 430;
    $i = 20005;
    //   $i = 1140;
    for ($i; $i < 21265; $i++) {
        # code...
        fix($i);
    }
}

fix('20521');
fix('20522');
fix('20524');

fix('20529');
fix('20530');

fix('20533');

fix('20535');

fix('20536');

fix('20538');

fix('20539');

fix('20541');

fix('20544');

fix('20546');

fix('20547');

fix('20548');
fix('20549');
fix('20550');
fix('20551');

fix('20553');

fix('20578');
// getbookinfo(1015);
