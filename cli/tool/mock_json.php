<?php

/**把json数据中的链接采集到本地，并且修改成mock链接*/




require_once   dirname(dirname(__FILE__)) . "/clibase.php";

use ng169\tool\File;
use \ng169\tool\Image;

use ng169\Y;

class mockjson extends Clibase
{
    public  $_booktype = 1; //书籍类型
    public  $_booklang = 6;  //书籍语言
    public  $_bookdstdesc_int = 2; //书籍来源描述
    public  $_bookdstdesc = "同步"; //书籍来源描述
    public  $_domian = "http://api.lookstory.xyz/api/"; //书籍来源描述
    // public  $_domian = "http://xs2.com/api"; //书籍来源描述
    public  $debug = true;
    public  $wordrate = 3;  //计算字数的时候的倍数比列
    // -------------------app 破解获取的相关信息


    //一些临时数据，无需变动
    public $cachename = 'cacheignorb';
    public  $clear = [];
    public function start()
    {
        //读取本地所有书籍跟远程对比
        //远程已经完结的记录下次不再读取
        $gt = $this->getargv(['file', 'clear']);
        if (!isset($gt['file'])) {
            d('请输入要转换的json文件', 1);
        }

        if (isset($gt['clear'])) {

            $this->clear = explode(',', $gt['clear']);
        }
        $file = $gt['file'];
        $json_string = file_get_contents($file);
        $data = json_decode($json_string, true);
        if (!$data) {
            d('json文件格式错误', 1);
        }
        $new = $this->looparr($data);

        if (!File::readContent($file . '.back')) {
            //旧记录文件不存在就新建
            File::writeFile($file . '.back', $json_string, 1);
        }

        File::writeFile($file, json_encode($new), 1);
    }
    public function looparr($arr)
    {
        if (is_array($arr)) {

            foreach ($arr as $k => $v) {
                if (is_array($this->clear) && in_array($k, $this->clear)) {
                    $arr[$k] = null;
                } else {
                    if (is_array($v)) {
                        $arr[$k] = $this->looparr($v);
                    } else {

                        $arr[$k] = $this->checkisres($v);
                    }
                }
            }
        } else {
            // return $arr;
            return $this->checkisres($arr);
        }
        return $arr;
    }

    public function checkisres($val)
    {
        $bool = preg_match("/([http,https]:\/\/)/", $val);
        if ($bool) {
            // d($val);
            $new = $this->getimg($val);
            if ($new) {
                return $new;
            }
            return $val;
        }
        return  $val;
    }
    //抓图片
    public function getimg($img)
    {
        $gt = $this->getargv(['path']);
        $p = null;
        if (isset($gt['path'])) {
            $p = $gt['path'];
        }
        $file = Image::imgtolocal($img, null, null, $p);
        $mock = 'mock:' . $file;
        if ($file) {
            return $mock;
        }

        return null;
    }


    public function help()
    {
        d('把json数据中的链接采集到本地，并且修改成mock链接,参数file、path、clear，；file要转换的json文件，path 图片要保持目录,clear要清空的字段，用逗号分割; ');
    }
    public function fixtag()
    {
        $list = T('tag')->get_all();
        $list = array_column($list, 'tagid', 'tagname');
        $list2 = T('category')->set_where(['depth' => 3])->get_all();
        foreach ($list2 as $key => $value) {
            if (isset($list[$value['category_name']])) {
                T('category')->update(['category_name' => $list[$value['category_name']]], ['category_id' => $value['category_id']]);
            }
        }
    }
}
$ob = new mockjson();


$ob->start();
