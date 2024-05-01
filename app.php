<?php

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

if (!defined('PATH_URL')) {
    define('PATH_URL', '/');
}

require_once ROOT . 'source/core/enter.php';

use \ng169\tool\Image;
use \ng169\lib\Log;
use \ng169\Y;
use \ng169\tool\Curl as Yurl;

//图片本地化
function applocal()
{
    $list = T('apps')->set_field("package,appid")->set_where(['inico' => 0])->limit(500)->get_all();
    $domain = "https://play.google.com/store/apps/details?hl=zh_cn&id=";
    foreach ($list as $key => $val) {
        if ($val['package']) {
            // $val['package'] = "com.ng.story";
            $googleurl = $domain . $val['package'];
            //取出来图片
            //把图片本地化
            $post = Y::import('curl', 'tool');
            $data = $post->get($googleurl);
            // $data = $post->get('https://www.google.com');
            // Log::txt($data);
            if ($data) {
                //查找
                preg_match_all('/src=*.+(http*.+)=s180/', $data, $arr);

                if ($arr && $arr[1] && $arr[1][0]) {
                    //找到了
                    // d(preg_match('/<main*.+漫画/', $data));
                    // d(preg_match('/<main*.+图书与工具书/', $data), 1);
                    $type = 0;
                    if (preg_match('/<main*.+图书与工具书/', $data)) {
                        $type = 1;
                        echo "小说";
                    }
                    if (preg_match('/<main*.+漫画/', $data)) {
                        $type = 2;
                        echo "漫画";
                    }
                    $img = $arr[1][0] . '=s100';
                    Image::imgtolocal($img, 'appico', $val['package'] . '.png');
                    // 图书与工具书
                    // 漫画
                    //取个app类型
                    if ($type) {
                        T('apps')->update(['inico' => 1, 'type' => $type], ['appid' => $val['appid']]);
                    } else {
                        T('apps')->update(['inico' => 1], ['appid' => $val['appid']]);
                    }

                    echo $val['package'] . "成功\n";
                    //把图片本地化
                } else {
                    // d($arr);
                    //抓取失败，下次不用抓
                    T('apps')->update(['inico' => 2], ['appid' => $val['appid']]);
                    echo $val['package'] . "失败\n";
                }

                // d($arr, 1);
            }
            // d($data, 1);
        }

        # code...
        //判断是否当前域名
        // if (strpos($val[$imgkey], $domain) !== false) {
        //     echo '本地';
        // } else {
        //     $file = Image::imgtolocal($val[$imgkey]);
        //     $new = $domain . '/' . $file;
        //     T($table)->update([$imgkey => $new], $val);
        // }
        //$file=Image::imgtolocal($val[$imgkey]);

    }
}
applocal();
