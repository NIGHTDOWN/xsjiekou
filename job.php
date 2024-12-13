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

//图片本地化
function imgtolocal($table, $prikey, $imgkey)
{
    $list = T($table)->set_field("$prikey,$imgkey")->get_all();
    $domain = "http://xspic.ng169.com";
    foreach ($list as $key => $val) {
        # code...
        //判断是否当前域名
        if (strpos($val[$imgkey], $domain) !== false) {
            echo '本地';
        } else {
            $file = Image::imgtolocal($val[$imgkey]);
            $new = $domain . '/' . $file;
            T($table)->update([$imgkey => $new], $val);
        }
        //$file=Image::imgtolocal($val[$imgkey]);

    }
}
//拉取目录图片
//  imgtolocal('book', 'book_id', 'bpic');
 imgtolocal('cartoon', 'cartoon_id', 'bpic');
//  imgtolocal('cartoon', 'cartoon_id', 'bpic_detail');

function imgtolocal2($table, $prikey, $imgkey)
{
    $list = T($table)->set_field("$prikey,$imgkey")->get_all();
    $domain = "http://xspic.ng169.com";
    foreach ($list as $key => $val) {
        # code...
        //判断是否当前域名
        $json = json_decode($val[$imgkey], 1);
        $json = $json['cart_sec_content'];
        foreach ($json as $key2 => $value) {
            # code...
            if (strpos($value['url'], $domain) !== false) {
                echo '本地';
            } else {
                $file = Image::imgtolocal($value['url']);
                // $file = 'aa333333sss33' . $value['url'];
				//判断文件大小为0保持原图。
				$fsize=filesize('/d/xs/pic/'.$file);
			
				if($fsize>1000){
					$new = $domain . '/' . $file;
	
					$json[$key2]['url'] = $new;
				}
                //T($table)->update([$imgkey=>$new],$val);
            }
        }
        //d($json, 1);
        $json = ['cart_sec_content' => $json];
        //d(json_encode($json), 1);
        T($table)->update(['cart_sec_content' => json_encode($json)], [$prikey => $val[$prikey]]);
        // if (strpos($val[$imgkey], $domain) !== false) {
        //     echo '本地';
        // } else {
        //     $file = Image::imgtolocal($val[$imgkey]);
        //     $new = $domain . '/' . $file;
        //     //T($table)->update([$imgkey=>$new],$val);
        // }
        //$file=Image::imgtolocal($val[$imgkey]);
    }
}
 imgtolocal2('cart_sec_content', 'cart_sec_content_id', 'cart_sec_content');

