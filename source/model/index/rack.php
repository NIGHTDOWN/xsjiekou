<?php

namespace ng169\model\index;

use ng169\Y;
use ng169\tool\Out;
use ng169\tool\Request;

checktop();
//统计埋点
class rack extends Y
{
    public function  list($uid)
    {

        if (!$uid) return false;
        M('census', 'im')->logcount($uid); //安装统计
        //更新用户登入时间，版本号
        // T('third_party_user')->update(['last_login_time' => time(),], ['id' => $uid]);
        $buw = ['users_id' => $uid];

        $book_ids = [];
        $cartoon_ids = [];
        $datas = T('user_groom')
            // ->field('*')
            ->where('status=1')
            ->order_by('readtime desc')
            ->where($buw)
            ->limit(500)
            ->get_all();

        foreach ($datas as $k => $book) {
            if ($book['type'] == 1) {
                array_push($book_ids, $book['book_id']);
            } else {
                array_push($cartoon_ids, $book['cartoon_id']);
            }
        }
        $data = array_column($datas, null, 'book_id');

        if (sizeof($book_ids)) {
            $tmp = T('book')->set_field('book_id,section,update_status')->whereIn('book_id', $book_ids)->get_all();

            foreach ($tmp as $key => $value) {
                if ($data[$value['book_id']]) {
                    $data[$value['book_id']]['newnum'] = $value['section'];
                }
            }
        }
        if (sizeof($cartoon_ids)) {
            $tmp = T('cartoon')->set_field('cartoon_id,section,update_status')->whereIn('cartoon_id', $cartoon_ids)->get_all();
            foreach ($tmp as $key => $value) {
                if ($data[$value['cartoon_id']]) {
                    $data[$value['cartoon_id']]['newnum'] = $value['section'];
                }
            }
        }
        $datas = array_values($data);
        return $datas;
    }
}
