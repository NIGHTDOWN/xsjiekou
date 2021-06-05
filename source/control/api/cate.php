<?php

namespace ng169\control\api;

use ng169\control\apibase;
use ng169\tool\Out;
use ng169\Y;
use ng169\tool\Request;

checktop();
class cate extends apibase
{

    protected $noNeedLogin = ['*'];
    //删除书籍相关缓存
    public function control_get()
    {
        $data = get(['int' => ['c1', 'c2', 'c3', 'c4', 'page']]);
        $arr = [];
        $cityid = $this->head['cityid'];
        $where['lang'] = $cityid;
        if (!$data['c1']) Out::jout($arr);
        if (!$data['c2']) Out::jout($arr);
        $filed = "other_name,`desc`,bpic_dsl,bpic,writer_name,isfree,update_status,lable";
        if ($data['c1'] <= 2) {
            $type = 'book';
            $filed .= ",book_id,1 as type";
        } else {
            $type = 'cartoon';
            $filed .= ",cartoon_id as book_id,2 as type";
        }
        $where['cate_id'] = $data['c2'];
        $list = T($type)
            ->field($filed)
            ->where($where);
        $lable = "";
        if ($data['c3']) {
            $lable = ' lable like  "%L' . $data['c3'] . ',%" ';
            $list = $list->where($lable);
        }


        // ->wherelike('other_name', $other_name['other_name'])
        if ($data['c4']) {
        }
        $desc = "update_time desc";
        switch ($data['c4']) {
            case '1':
                # code...
                $desc = "update_time desc";
                break;
            case '2':
                $desc = "collect desc";
                break;
            case '3':
                # code...
                $list = $list->where(['update_status' => 1]);
                break;
            default:
                # code...
                break;
        }
        $list = $list->order($desc)->set_limit([$data['page'], 10]);

        $arr = $list->get_all();
        Out::jout($arr);
    }
}
