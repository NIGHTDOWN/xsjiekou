<?php

namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\tool\Out;
use ng169\Y;

checktop();



class cateset extends indexbase
{

    protected $noNeedLogin = ['*'];


    public function control_run()
    {
        $get = get(['int' => ['bookid', 'type', 'lang']]);
        if (!$get['bookid']) {
            $w['lang'] = $get['lang'];
            $w['category_id'] = 0;
            if ($get['type'] == 2) {
                $db = 'cartoon';
            } else {
                $get['type'] = 1;
                $db = 'book';
            }
            $randbooks = T($db)->set_field($db . '_id as bookid,' . $get['type'] . ' as type,lang')->set_where($w)->set_limit(5)->get_all();
            $index =  random_int(0, sizeof($randbooks) - 1);

            gourl(geturl($randbooks[$index]));
            // Out::page404();
        }
        $detail = M('book', 'im')->detail($get['bookid'], $this->get_userid(), $get['type']);
        $cate1 = T('category')->set_where(['depth' => 1])->get_all();
        $nowtag = [];
        $cate1tmp = array_column($cate1, null, 'category_id');
        if ($detail['data']['category_id']) {
            $nowtag[1] = $cate1tmp[$detail['data']['category_id']]['category_name'] . '>';
        }
        if ($detail['data']['cate_id']) {
            $tmp = T('category')->set_where(['category_id' => $detail['data']['cate_id']])->get_one();;
            if ($tmp) {
                $nowtag[2] = $tmp['category_name'] . '>';
            }
        }
        if ($detail['data']['lable']) {
            $lbs = M('cate', 'im')->getlable($detail['data']['lable'], 5);
            $nowtag[3] = $lbs;
        }

        $detail['cates'] = $cate1;
        $detail['nowtag'] = $nowtag;
        $this->view(null, $detail);
    }
    public function control_cate2()
    {
        $get = get(['int' => ['cateid' => 1]]);
        $cate1 = T('category')->set_where(['depth' => 2, 'pid' => $get['cateid']])->get_all();
        Out::jout($cate1);
    }
    public function control_cate3()
    {
        $get = get(['int' => ['cateid' => 1]]);
        $cate1 = T('category')->set_where(['depth' => 3, 'pid' => $get['cateid']])->get_all();
        $ids = array_column($cate1, 'category_name');
        $lbs = M('cate', 'im')->getlable($ids, 5);
        Out::jout($lbs);
    }
    public function control_set()
    {
        $get = get(['int' => ['cate1', 'cate2', 'bookid', 'type'], 'string' => ['cate3']]);
        $up['category_id'] = $get['cate1'];
        $up['cate_id'] = $get['cate2'];
        $up['lable'] = $get['cate3'];
        if ($get['type'] == 2) {
            $w = ['cartoon_id' => $get['bookid']];
            $bol = T('cartoon')->update($up, $w);
        } else {
            $w = ['book_id' => $get['bookid']];
            $bol = T('book')->update($up, $w);
        }
        if ($bol) {
            $index = 'webbid:' . $get['bookid'] . '_' . $get['type'];
            Y::$cache->del($index);
        } else {
            Out::jout(0);
        }

        Out::jout(1);
    }
}
