<?php

namespace ng169\model\index;

use ng169\Y;

checktop();
//统计埋点
class index extends Y
{
    public function booknew($type, $lang, $num, $page = 0)
    {
        //取最新的几本小说
        $up = T('book_up_log')->set_field('bookid')->set_where(['type' => $type, 'lang' => $lang])->set_limit([$page, $num])->order_by(['f' => 'uptime', 's' => 'down'])->group_by('bookid')->get_all();
        $bookids = array_column($up, 'bookid');
        if (!sizeof($bookids)) {
            return [];
        }
        if ($type == 2) {
            //漫画
            $list = T('cartoon')->set_field('cartoon_id as book_id,other_name,hits,`desc`,lable,bpic,share_banner')->set_where(['lang' => $lang])->wherein('cartoon_id', $bookids)->get_all();
        } else {
            $list = T('book')->set_field('book_id,other_name,hits,`desc`,lable,bpic,share_banner')->set_where(['lang' => $lang])->wherein('book_id', $bookids)->get_all();
        }
        return $list;
    }
    public function hot($type, $lang, $num)
    {


        if ($type == 2) {
            //漫画
            $list = T('cartoon')->set_field('cartoon_id as book_id,other_name,hits,`desc`,lable,bpic,share_banner')->set_where(['lang' => $lang, 'update_status' => 1])->set_limit($num)->order_by(['f' => 'hits', 's' => 'down'])->get_all();
        } else {
            $list = T('book')->order_by(['f' => 'hits', 's' => 'down'])->set_field('book_id,other_name,hits,`desc`,lable,bpic,share_banner')->set_where(['lang' => $lang, 'update_status' => 1])->set_limit($num)->get_all();
        }
        return $list;
    }
    public function end($type, $lang, $num)
    {


        if ($type == 2) {
            //漫画
            $list = T('cartoon')->set_field('cartoon_id as book_id,other_name,hits,`desc`,lable,bpic,share_banner')->set_where(['lang' => $lang, 'update_status' => 1])->set_limit($num)->get_all();
        } else {
            $list = T('book')->set_field('book_id,other_name,hits,`desc`,lable,bpic,share_banner')->set_where(['lang' => $lang, 'update_status' => 1])->set_limit($num)->get_all();
        }
        return $list;
    }
}
