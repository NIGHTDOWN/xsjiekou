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
#相对URL路径
error_reporting(E_ALL ^ E_NOTICE);
if (!defined('PATH_URL')) {
    define('PATH_URL', '/');
}

require_once ROOT . 'source/core/enter.php';

use \ng169\tool\Curl;

//获取小说内容

$spiner = new Curl();
$spiner->head([
    'devicetoken: b511a21f-9d7d-43be-b9a3-8bec1feda8de',
    'token: ',
    'deviceversion: 5.1.1',
    'deviceos: google Pixel 2',
    'apiSign: 52010b153f1179e7c92967a09fa90c00',
    'version: 1.4.1',
    'timestamp: 1594456217551',
    'uid: ',
    'devicetype: android',
    'apiKey: 9b4af02fddc12d2a38e2deae747beff0',
    // 'Connection:Keep-Alive',
    // 'Accept-Encoding:gzip',
    // 'User-Agent:okhttp/4.1.0'
    // 'Content-Type'=>'application/x-www-form-urlencoded',
    //Content-Length:16,
]);
function getbookinfo($id)
{
    global $spiner;
    // $url = 'https://api.hinovelasia.com/api/book/detail';
    $url = 'https://apiv1.aikoversea.com/api/book/get_bookDetail';
    $data = $spiner->post($url, ['book_id' => $id]);
    //更新字数
    //更新状态
    $data = getdata($data);

    if ($data) {
        // d($data);
        //判断当前是否有这边小说没则添加
        $data = $data['data'];
        $dbbook = T('book')->get_one(['book_id' => $id]);

        if ($dbbook) {
            if ($dbbook['update_status'] == 1) {
                d('完结跳出' . $id);

                return false;
            }
            //判断当前小说更新章节是否跟后台一样；一样的话就直接跳出
            // if ($dbbook['']) {
            // }
            else {
                $up = [];
                if ($data['wordnum']) {
                    $up['wordnum'] = $data['wordnum'];
                }
                if ($data['update_section']) {
                    $up['section'] = $data['update_section'];
                    $up['update_time'] = time();
                    $up['update_status'] = $data['update_status'];
                }
                if (sizeof($data) && $dbbook['section'] != $up['section']) {
                    T('book')->update($up, ['book_id' => $id]);
                    d('更新书' . $id . "\n");
                }
            }
        } else {
            //添加新小说

            $add = [
                'book_id' => $id,
                'writer_name' => 'lookstory',
                'book_name' => $data['other_name'],
                'status' => 2, //下架
                'wordnum'   => $data['wordnum'],
                'section'   => $data['update_section'],
                'bpic' => $data['bpic'],
                'isfree' => $data['isfree'],
                'desc'  => $data['desc'],
                'money' => '0.6',
                'create_time' => time(),
                'update_time' => time(),
                'update_status' => $data['update_status'],
                'other_name' => $data['other_name'],
            ];
            d('添加新书' . $id . "\n");
            T('book')->add($add);
        }

        getsecinfo($id);
    }
}

//获取小说章节
function getsecinfo($bid, $num)
{
    global $spiner;
    $url = 'https://apiv1.aikoversea.com/api/cartoon/get_cart_section';
    $data = $spiner->post($url, ['cartoon_id' => $bid]);
    //更新字数
    //更新状态

    $data = getdata($data);
    $data = $data['list'];
    if ($data) {
        //取得章节列表，对比现有章节数量相同就跳出

        if ($data[$num - 1]) {
            return $data[$num - 1]['cart_section_id'];
        }
        return false;
    }
    return false;
}
//获取章节内容
function getcontent($bid, $sid)
{
    //获取远程内容
    global $spiner;
    $url = 'https://apiv1.aikoversea.com/api/cartoon/get_wap_content';
    $data = $spiner->post($url, ['cart_section_id' => $sid, 'cartoon_id' => $bid]);
    $data = getdata($data);

    if ($data) {
        return $data['cart_sec_contents'];
    }
    return false;
}
//解密
function decode()
{
}
function getdata($curldata)
{
    $data = json_decode($curldata, 1);
    if ($data) {
        if ($data['code'] == 1)  return $data['result'];

        return false;
    }
    return false;
}




function getneedfix()
{
    $sql = "SELECT * FROM ( SELECT a1.cart_section_id, a1.cartoon_id, a1.list_order, cart_sec_content FROM tp_cartoon_section AS a1 LEFT JOIN tp_cart_sec_content AS a2 ON a1.cart_section_id = a2.cart_section_id WHERE a1.`status` = 1 ) AS a3 WHERE ISNULL(cart_sec_content)";
    $data = T('')->get_all(null, null, 1, $sql);
    d('待修复章节数量' . sizeof($data));
    //获取远程该章节对应的id
    foreach ($data as $fix) {
        $bid = $fix['cartoon_id'];
        $num = $fix['list_order'];
        $thissecid = $fix['cart_section_id'];
        $remoteid = getsecinfo($bid, $num);

        if ($remoteid) {
            $content = getcontent($bid, $remoteid);

            if ($content) {
                // d($content, 1);
                // $a2 = ['sec_content' => $content, 'section_id' => $thissecid];

                // T('sec_content')->add($a2);
                $a2 = ['cart_sec_content' => json_encode($content), 'cart_section_id' => $thissecid];

                T('cart_sec_content')->add($a2);
                d('修复' . $bid . '_' . $num);
            }
        }
    }
}


getneedfix();
// getbookinfo(1015);
