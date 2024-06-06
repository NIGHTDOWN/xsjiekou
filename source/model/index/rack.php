<?php

namespace ng169\model\index;

use ng169\Y;
use ng169\tool\Out;
use ng169\tool\Request;

checktop();
//统计埋点
class rack extends Y
{
    /**
     * 显示用户书架，最多显示500本
     */
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
    /**
     * 判断书籍是否已经加入书架
     */
    public function in_rack($uid, $type, $bookid)
    {
        if (!$uid) return false;
        if (!$type) return false;
        if (!$bookid) return false;
        $where['users_id'] = $uid;
        $where['type'] = $type;
        $where['book_id'] = $bookid;
        $where['status'] = 1;
        $res = T('user_groom')->set_field('grooms_id')->where($where)->find();
        return $res ? true : false;
    }
    public function addrack($uid, $type, $bookid)
    {
        if (!$uid) return false;
        if (!$type) return false;
        if (!$bookid) return false;
        $res = $this->in_rack($uid, $type, $bookid);
        if ($res) {
            return false;
            // Out::jerror('已經加入書架了', null, '100128');
        }
        $arr['addtime'] = time();
        $arr['readtime'] = time();
        $arr['users_id'] = $uid;
        $arr['status'] = 1;
        $arr['type'] = $type;
        $arr['book_id'] = $bookid;
        $his = $this->getbookhis($uid, $type, $bookid);
        if ($his) {
            $arr['readsec'] = $his['watch_sec'];
            $arr['readpoinstion'] = $his['watch_sec'];
        }
        if ($arr['type'] == '1') {
            $w = ['book_id' => $arr['book_id']];
            $book = T('book')->field('other_name,bpic,`desc`,isfree,lang')->where($w)->find();

            if ($book) {
                $arr['other_name'] = $book['other_name'];
                $arr['bpic'] = $book['bpic'];
                $arr['desc'] = $book['desc'];
                M('census', 'im')->collectcounts($bookid);
                //从历史记录里面获取阅读定位
                $db = M('book', 'im')->gettpsec($type, $book['lang']);
                $arr['booknumlast'] = T($db)->set_where(['book_id' => $bookid, 'status' => 1])->get_count();

                T('user_groom')->add($arr);
                M('bookcensus', 'im')->addgroom($uid, 1, $bookid);
                Out::jout(__('加入成功'));
            } else {
                Out::jerror(__('书架ID不存在'), null, '100129');
            }
        } else {
            $w1 = ['cartoon_id' => $arr['book_id']];
            $cartoon = T('cartoon')->field('other_name,bpic,`desc`,isfree,lang')->where($w1)->find();
            if ($cartoon) {

                $arr['other_name'] = $cartoon['other_name'];
                $arr['bpic'] = $cartoon['bpic'];
                $arr['desc'] = $cartoon['desc'];
                M('census', 'im')->cartooncollectcounts($bookid);
                $db = M('book', 'im')->gettpsec($type, $cartoon['lang']);
                $arr['booknumlast'] = T($db)->set_where(['cartoon_id' => $bookid, 'status' => 1])->get_count();




                T('user_groom')->add($arr);
                M('bookcensus', 'im')->addgroom($uid, 2, $bookid);
                Out::jout(__('加入成功'));
            } else {
                Out::jerror(__('书架ID不存在'), null, '100129');
            }
        }
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
    /**
     * 获取小说阅读记录
     */
    public function getbookhis($uid, $type, $bookid)
    {
        if (!$uid) return false;
        if (!$type) return false;
        if (!$bookid) return false;
        $his = T('user_history')
            ->set_where(['users_id' => $uid])
            ->set_where(['type' => $type])
            ->set_where(['book_id' => $bookid])
            ->find();
        return $his;
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
    //更新书籍历史记录，及书架记录
    // public function update($uid, $bookid, $type, $sid)
    // {
    //     if ($type == 1) {
    //         M('rack', 'im')->user_read_history($uid, $bookid, '', $sid);
    //     } else {
    //         M('rack', 'im')->user_read_history($uid, '', $bookid,  $sid);
    //     }
    // }
    //获取阅读定位
    public function getpoint($uid, $bookid, $type, $groom)
    {
        if (!$uid) return 0;
        if ($groom) {
            //取书架记录
            $read = T('user_groom')->set_field('readsec')->set_where(['status' => 1, 'book_id' => $bookid, 'type' => $type, 'users_id' => $uid])->get_one();
            if ($read) return $read['readsec'];
        } else {
            //取历史记录
            $read = T('user_history')->set_field('watch_sec')->set_where(['book_id' => $bookid, 'type' => $type, 'users_id' => $uid])->get_one();
            if ($read) return $read['watch_sec'];
        }
        return 0;
    }
}
