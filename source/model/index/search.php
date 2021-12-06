<?php

namespace ng169\model\index;

use ng169\Y;

checktop();
//统计埋点
class search extends Y
{
    //奖励书币,$type奖励类型

    public function search($lang, $word, $page = 0, $uid = 0)
    {
        $size = 5;
        $this->page_size = $size;
        // $get = get(['string' => ['keyword' => 1, 'page']]);
        $cityid = $lang;
        $other_name = $word;
        //$where['status'] = 1;
        $where = "`status`=1 and (other_name like \"%$other_name%\" or writer_name like \"%$other_name%\")";
        $devicetype = $this->head['devicetype'];
        if ($devicetype == 'iphone') {
            $order = "i_recharge desc";
        } else {
            $order = "recharge desc";
        }
        $book = T('book')
            ->field('book_id,other_name,`desc`,bpic,writer_name,isfree,update_status,1 as type')
            ->where($where)
            ->order($order)->set_global_where(['lang' => $cityid])
            ->set_limit([$page, $size])
            ->get_all();
        $cartoon = T('cartoon')
            ->field('cartoon_id,other_name,`desc`,bpic,writer_name,isfree,update_status,2 as type')
            ->where($where)->set_global_where(['lang' => $cityid])
            ->order($order)
            ->set_limit([$page, $size])
            ->get_all();

        foreach ($book as $key => $value) {
            $book[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
            $book[$key]['type'] = 1;
        }
        foreach ($cartoon as $key => $value) {
            $cartoon[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
            $cartoon[$key]['type'] = 2;
            $cartoon[$key]['book_id'] = $value['cartoon_id'];
            unset($cartoon[$key]['cartoon_id']);
        }
        $data = array_merge($book, $cartoon);
        M('book', 'im')->keylog($uid, $other_name);
        $this->hotbooklog($data, $lang);
        return $data;
    }
    //记录搜索，表 hot_search
    public function hotbooklog($data, $lang)
    {
        //无书籍就不记录
        if (!sizeof($data)) return false;
        //命中就取第一个
        $book = $data[0];
        $where = ['bookid' => $book['book_id']];
        $insert['title'] = $book['other_name'];
        $insert['bookid'] = $book['book_id'];
        $insert['type'] = $book['type'];
        $insert['lang'] = $lang;
        $insert['lang'] = $lang;
        $in = T('hot_search')->set_where($where)->get_one();
        if ($in) {
            T('hot_search')->update(['sernum' => $in['sernum'] + 1], $where);
        } else {
            T('hot_search')->add($insert);
        }
    }
}
