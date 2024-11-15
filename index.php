<?php
namespace ng169;
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header('Access-Control-Allow-Methods: POST');
    header('Access-Control-Allow-Headers: Content-Type');
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 3600');
    exit(0);
}
header('Access-Control-Allow-Origin:*'); 
// 响应类型 
header('Access-Control-Allow-Methods:PUT,GET,POST,OPTIONS'); 
// 响应头设置 
header('Access-Control-Allow-Headers:x-requested-with,content-type');
header('Access-Control-Allow-Headers:*');


/*header('Access-Control-Allow-Headers:');*/

define('ROOT',__DIR__.'/');

require_once ROOT.'source/core/enter.php';
APP::run();

die();
