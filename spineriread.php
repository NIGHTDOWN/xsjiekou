<?php
//iread爬虫
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



use \ng169\tool\Curl;

//获取小说内容

$spiner = new Curl();
$spiner->head([
    // 'devicetoken: b511a21f-9d7d-43be-b9a3-8bec1feda8de',
    // 'token: ',
    // 'deviceversion: 5.1.1',
    // 'deviceos: google Pixel 2',
    // 'apiSign: 52010b153f1179e7c92967a09fa90c00',
    // 'version: 1.4.1',
    // 'timestamp: 1594456217551',
    // 'uid: ',
    // 'devicetype: android',
    // 'apiKey: 9b4af02fddc12d2a38e2deae747beff0',
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
    $url = 'http://read.api.heidongwl.com/books/find?bookId=' . $id;
    $data = $spiner->get($url);

    //更新字数
    //更新状态
    $data = getdata($data);


    if ($data) {
        // d($data);
        //判断当前是否有这边小说没则添加
        $data = $data['data'];
        $data['other_name'] = xhth($data['other_name']);
        $data['book_desc'] = xhth($data['book_desc']);
        $dbbook = T('book')->get_one(['fid' => $id, 'ftype' => 1]);
        //
        if (!$dbbook) {
            $dbbook = T('book')->get_one(['other_name' => $data['other_name']]);
        }

        if ($dbbook) {
            if ($dbbook['update_status'] == 1) {
                d('完结跳出' . $id);
                //更新章节数量
                if (!$dbbook['section']) {
                    M('book', 'im')->fixbooksecnum($dbbook['book_id']);
                }
                return false;
            } else {
                $up = [];
                if ($data['wordnum']) {
                    $up['wordnum'] = $data['wordnum'];
                }
                if ($data['update_section']) {
                    $up['section'] = $data['sections_num'];
                    $up['update_time'] = time();
                    $up['update_status'] = $data['update_status'];
                }
                if (sizeof($data) && $dbbook['section'] != $up['section']) {
                    T('book')->update($up, ['book_id' => $dbbook['book_id']]);
                    d('更新书' . $dbbook['book_id'] . "\n");
                }
            }
            $did = $dbbook['book_id'];
        } else {
            //添加新小说

            $add = [
                'fid' => $id,
                'ftype' => FTYPE,
                'writer_name' => 'lookstory',
                'book_name' => $data['other_name'],
                'status' => 2, //下架
                'wordnum'   => $data['secnum'],
                'section'   => $data['sections_num'],
                'bpic' => $data['front_image_url'],
                'isfree' => $data['isfree'],
                'desc'  => $data['book_desc'],
                'money' => '0.6',
                'create_time' => time(),
                'update_time' => time(),
                'update_status' => $data['
                '],
                'other_name' => $data['other_name'],
            ];
            d('添加新书' . $id . "\n");

            $did = T('book')->add($add);
            // $did=$dbbook['book_id'];
        }

        // getsecinfo($id, $did);
        list($upmu, $zjnum) = getsecinfo($id, $did);
        if ($upmu) {
            //更新章节更新时间
            //更新章节数量
            $up['section'] = $data[$zjnum];
            $up['update_time'] = time();
            T('book')->update($up, ['book_id' =>  $did]);
        }

        M('book', 'im')->fixbooksecnum($did);
    }
}

//获取小说章节
function getsecinfo($id, $bid)
{
    global $spiner;
    $url = 'http://read.api.heidongwl.com/books/sections?bookId=' . $id;
    $data = $spiner->get($url);
    //更新字数
    //更新状态

    $data = getdata($data);

    if ($data) {
        //取得章节列表，对比现有章节数量相同就跳出
        $secnum = T('section')->set_where(['book_id' => $bid, 'status' => 1])->get_count();

        if (sizeof($data) > $secnum) {
            //更新最新的章节

            $upnum = sizeof($data) - $secnum;
            d('更新章节数量' . $upnum);
            for ($j = 0; $j <= $upnum; $j++) {


                $newsecid = $secnum;
                $remotedata = $data[$newsecid];

                $seccontrent = getcontent($id, $remotedata['section_id']);

                if ($seccontrent) {
                    //取到了内容就更新
                    $secadd = [
                        // 'section_id'   => '',
                        'title'        => xhth($remotedata['title']),
                        'list_order'   => $newsecid + 1,
                        'secnum'       => $remotedata['secnum'],
                        // 'isover'       => '',
                        'book_id'      => $bid,
                        'create_time'  => time(),
                        'update_time'  =>  date('Y-m-d H:i:s'),
                        'status'       => 1,
                        'sec_word'     => $newsecid,
                        // 'update_status' => '',
                        'isfree'       => $remotedata['is_free'],
                    ];
                    $secid = T('section')->add($secadd);
                    if ($secid) {
                        //章节添加成功
                        $a2 = ['sec_content' => xhth($seccontrent), 'section_id' => $secid];
                        T('sec_content')->add($a2);
                        //这里断点
                        // d('新增章节' . $secid, 1);
                        //
                    }
                }
                $secnum++;
                // $upnum--;
            }
            return [$upnum, sizeof($data)];
        }
    }
    return false;
}
//获取章节内容
function getcontent($bid, $sid)
{
    //获取远程内容
    global $spiner;
    $url = "http://read.api.heidongwl.com/books/sections/find?sectionId={$sid}&bookId={$bid}";
    $data = $spiner->get($url);

    $data = getdata($data);
    if ($data) {
        $data = decode($data['content'], $data['key']);

        return $data;
    }
    return false;
}
//解密

function decode($data, $key)
{
    $commonModel = M('book', 'im');
    $iv = '2020425hdongkeji';
    $key = sign($key);
    $data = $commonModel->aes_decrypt($data, $key, $iv);
    return $data;
}
function  redPukey($pubKey)
{

    $pem = "-----BEGIN RSA PRIVATE KEY-----\n" . $pubKey . "\n-----END RSA PRIVATE KEY-----\n";
    $publicKey = openssl_pkey_get_private($pem);

    return $publicKey;
}
function sign($dataStr)
{
    $privateKey = "MIICXQIBAAKBgQC2JvNSGNYYLJqO7Hz8EKXfIrSFsXK6dIi3aUxNdaWCDp0f6Spz\nRW55JqRpa27KjtIxPJXTBj6hbWng5IVhKI7PfkWZOTEDZZMaiZjrsye9uzUhO1YA\n2jj2rGPpeytt3SGtXV5GaFXYrJ70/f98QVx92upg0HpkUAya664Jj9PjVQIDAQAB\nAoGAQe39OiTlMSDL3Jl6b53y+73LC2z78sMFTSWeyZagjl+NvaQeilSCNPWoosOQ\n+V4SdGHSdOwYtUMuBImSQWV1ssZTppQPx3EFfpMxUdzJLvXlDZGYOjspCAYKU4XR\nr1ac8pEGQLYXBVomGVbR0v/YRV0EZQzm4t7rgxGp9tGdBcECQQDaXzhB4zyak7k9\nXVEnfhnh1UDEGKnxhEsYzTkoEP7lknc2igqUiExDR7hpg3ta9hc79Au85cdNty/y\n+XNvI48lAkEA1YoCj0ONC+QtbyhDe/3zaypJ9m1P9yoTZV97IPBwihpEILY8BJYS\nevAXTg2fpRrPgaoKxP4x9A0yc4T6J1akcQJBAMNtcfBtR8BiseW8DLPWQ5169vJH\nzFcrePWiPCOiSiv0DyJNGbjh3bZciipLk+rMz/BEsPiFfv8LEStWmTr+TM0CQBHn\ng3VtrYrcs+6JCrd/wIQwxIjT+4t2zK+IRPOrFVSPBT1U6k1cI+qI7PtPax5V1CZE\nEqkXwyp6XMuQz8SyoBECQQCUY8u4AMIzR/Do7yrXyxvVImh/Hts4dUkTCubSDk6c\nHsa/aWymETeKzEjrp7XRPZUppwRqh/47+wcdmAUu5Oy2";

    $privateKey = redPukey($privateKey);
    $crypto = '';
    foreach (str_split(base64_decode($dataStr), 128) as $chunk) {
        openssl_private_decrypt($chunk, $decryptData, $privateKey, OPENSSL_PKCS1_PADDING);
        $crypto .= $decryptData;
    }

    return $crypto;
}
function getdata($curldata)
{
    $data = json_decode($curldata, 1);
    if ($data) {
        if ($data['code'] == 0)  return $data['result'];

        return false;
    }
    return false;
}


function start()
{
    //起始id 430  最总 1217
    $i = 430;
    $i = 1117;
    //   $i = 1140;
    for ($i; $i < 1500; $i++) {
        # code...
        getbookinfo($i);
    }
}

// start();

