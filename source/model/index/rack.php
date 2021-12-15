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
    /**删除对应书架书籍
     * uid 用户id
     *@string  book_ids 小说id,多个逗号分割
     *@string  cartoon_ids 漫画id ,多个逗号分割
     *  */
    public function del($uid, $book_ids, $cartoon_ids)
    {
        if (!$uid) return false;
        $a1 = false;
        $a2 = false;
        if ($book_ids) {
            $a1 = T('user_groom')->where(['type' => 1,  'users_id' => $uid])->whereIn('book_id', $book_ids)->del();
        }

        if ($cartoon_ids) {
            $a2 =  T('user_groom')->where(['type' => 2,  'users_id' => $uid])->whereIn('book_id', $cartoon_ids)->del();
        }
        return $a1 || $a2;
    }
    public function readhis($uid, $page, $num = 10)
    {
        if (!$uid) return false;
        $list = T('user_history')
            ->set_where(['users_id' => $uid])
            ->order_by(['f' => 'watch_time', 's' => 'down'])
            ->set_limit([$page, $num])->get_all();
        return $list;
    }
    /**清空阅读记录 */
    public function clearhis($uid)
    {
        if (!$uid) return false;
        $a1 = false;
        $a1 = T('user_history')->where(['users_id' => $uid])->del();
        return $a1;
    }
    /**删除阅读记录 */
    public function delhis($uid, $ids)
    {
        if (!$uid) return false;
        $a1 = false;

        if ($ids) {
            $a1 = T('user_history')->where(['users_id' => $uid])->whereIn('his_id', $ids)->del();
        }
        return $a1;
    }
}
