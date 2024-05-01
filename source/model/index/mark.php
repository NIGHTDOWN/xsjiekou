<?php

namespace ng169\model\index;

use ng169\Y;

checktop();
//统计埋点
class mark
{
    //添加记录
    public function log($uid, $bookid, $type, $cate1id, $cate2id, $tagid)
    {

        $size = T('mark')->set_where(['day' => date('Ymd'), 'uid' => $uid,])->get_count();
        if ($size >= 3) {
            return false;
        }
        $insert = [
            'uid' => $uid,
            'bookid' => $bookid,
            'type' => $type,
            'cate1' => $cate1id,
            'cate2' => $cate2id,
            'cate3' => $tagid,
            'day' => date('Ymd'),
            'addtime' => time()
        ];
        return T('mark')->Add($insert);
    }
    public function getnonecate($cityid)
    {
        
        $where['lang'] = $cityid;
        $where['cate_id'] =0;
        $cache = 'nonecategorylist' . $where['lang'];
        list($bool, $data) = Y::$cache->get($cache);
        if ($bool) {
            return $data;
        }

        $book = T('book')->set_where($where)->set_field(['book_id'])->get_all();
        $cartoon = T('cartoon')->set_where($where)->set_field(['cartoon_id'])->get_all();
        $book = array_column($book, 'book_id');
        $cartoon = array_column($book, 'cartoon_id');
        $list = array_merge($book, $cartoon);
        Y::$cache->set($cache, $list, G_DAY);
        return $list;
    }
}
