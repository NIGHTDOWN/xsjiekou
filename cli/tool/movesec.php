<?php
namespace ng169\cli\tool;
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

require_once ROOT . 'source/core/enter.php';

use \ng169\tool\Curl;

//获取小说内容


function start($langtmp)
{
    $list = T('book')->set_limit(500)->set_where(['lang' => $langtmp])->set_field('book_id,lang')->get_all();

    foreach ($list as $key => $value) {

        $sec = T('section')->set_where(['book_id' => $value['book_id'], 'status' => 1, 'isdelete' => 0])->order_by('list_order asc')->get_all();

        $lists = array_column($sec, 'section_id');
        // d($value['book_id'] . '章节' . sizeof($lists) . '');
        if (sizeof($lists) > 0) {
            if ($lists > 1000) {
                $tmp = array_chunk($lists, 500);
                $secc = [];
                foreach ($tmp as $key => $value2) {
                    $secctmp = T('sec_content')->wherein('section_id', $value2)->get_all();
                    $secc = array_merge($secc, $secctmp);
                }
            } else {
                $secc = T('sec_content')->wherein('section_id', $lists)->get_all();
            }

            //大于1000章的要分段，不然数据库溢出，终端
            
            $secc = array_column($secc, 'sec_content', 'section_id');
            // if ('20686' == $value['book_id']) {
            //     d(sizeof($secc),1);
            // }
            foreach ($sec as $in) {
                $id = $in['section_id'];
                unset($in['section_id']);
                $sid = T('section_' . $value['lang'])->add($in);
                T('sec_content_' . $value['lang'])->add(['section_id' => $sid, 'sec_content' => $secc[$id]]);
            }
            T('section')->update(['isdelete' => 1], ['book_id' => $value['book_id'], 'status' => 1, 'isdelete' => 0]);
            d($value['book_id'] . '章节' . sizeof($lists) . '移动成功');
        }
    }
}

start(1);
start(2);
start(3);
start(4);
