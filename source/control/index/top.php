<?php

namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\tool\Out;

checktop();



class top extends indexbase
{

    protected $noNeedLogin = ['*'];


    public function control_run()
    {
        $cache = 'topbook100' . $this->langid;
        list($bool, $data) = parent::$cache->get($cache);
        if (!$bool) {
            $w['lang'] = $this->langid;
            $w['status'] = 1;
           
            $data = T('book')->set_where($w)->set_field('book_id,1 as type,`read`,bpic,other_name,lable,`desc`,lang,category_id,cate_id,lable')->set_limit(100)->order_by(['s' => 'down', 'f' => '`read`'])->get_all();
            foreach ($data  as $k => $book) {
                $data[$k]['tags'] =  M('cate', 'im')->getlable($book['lable'], $book['lang']);
            }
            parent::$cache->set($cache, $data, G_DAY);
        }
        if (sizeof($data) > 3) {
            $s1 = array_slice($data, 0, 3);
            $s2 = array_slice($data, 2);
        }
        $this->view(null, ['data' => $data, 's1' => $s1, 's2' => $s2, 'booktype' => 'book']);
    }
    public function control_cartoon()
    {
        $cache = 'topcartoon100' . $this->langid;
        list($bool, $data) = parent::$cache->get($cache);
        if (!$bool) {
            $w['lang'] = $this->langid;
            $w['status'] = 1;
            $data = T('cartoon')->set_field('cartoon_id as book_id,2 as type ,`read`,bpic,other_name,lable,`desc`,lang,category_id,cate_id,lable')->set_where($w)->set_limit(100)->order_by(['s' => 'down', 'f' => '`read`'])->get_all();
            foreach ($data  as $k => $book) {
                $data[$k]['tags'] =  M('cate', 'im')->getlable($book['lable'], $book['lang']);
            }
            parent::$cache->set($cache, $data, G_DAY);
        }
        if (sizeof($data) > 3) {
            $s1 = array_slice($data, 0, 3);
            $s2 = array_slice($data, 2);
        }
        $this->view('top_index', ['data' => $data, 's1' => $s1, 's2' => $s2, 'booktype' => 'cartoon']);
    }
}
