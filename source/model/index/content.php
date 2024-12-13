<?php

namespace ng169\model\index;

use ng169\Y;

checktop();
//统计埋点
class content
{
    public function cartoon($bid, $sid, $uid)
    {
        // $cartoon = get(['int' => ['cartoon_id' => 1, 'cart_section_id' => 1]]);
        $cartoon_id = $bid;
        $cart_section_id = &$sid;
        M('census', 'im')->logcount($uid); //安装统计
        $lang = T('cartoon')->set_where(['cartoon_id' => $cartoon_id,])->set_field('lang,other_name')->get_one();
        if (!$lang) return false;
        $tpsec = M('book', 'im')->gettpsec(2, $lang['lang']);
        if (!$sid) {
            $first = T($tpsec)->set_field('cart_section_id as section_id')->set_where(['cartoon_id' => $bid, 'status' => 1])->order_by(['s' => 'up', 'f' => 'list_order'])->get_one();
            $sid = $first['section_id'];
        }

        $tpsecc = M('book', 'im')->gettpseccontent(2, $lang['lang']);
        $users_id = $uid;
        $where = [
            'cartoon_id' => $cartoon_id,
            'cart_section_id' => $cart_section_id,
        ];
        $index = 'ccontent_' . $cartoon_id . '_:' . $cart_section_id;
        $cache = Y::$cache->get($index);

        if ($cache[0]) {
            $arr = $cache[1];
            if (!$arr['next']) {
                $up = T($tpsec)->set_where(['cartoon_id' => $cartoon_id, 'isdelete' => 0, 'status' => 1])->set_where('list_order>' . $arr['list_order'])->set_field('cart_section_id')->order_by(['f' => 'list_order', 's' => 'up'])->get_one();
                $arr['next'] = $up['section_id'] ? $up['section_id'] : '0';
            }
        } else {
            // M('census', 'im')->uprackreadtime($this->get_userid(), $cartoon_id, 1, $cart_section_id);
            $data = T($tpsec)->field('cart_section_id,title,cartoon_id,likes,collects,update_time,isfree,charge_coin,list_order')->where($where)->where(['status' => 1])->where(['isdelete' => 0])->find();

            if (!$data) {
                return false;
                // Out::jerror('章节不存在', null, '102222');
            }
            $content = T($tpsecc,null,"content")->field('cart_sec_content,cart_sec_content_dsl,cart_sec_content_id')->where(['cart_section_id' => $data['cart_section_id']])->where(['isdelete' => 0])->find();

            // aes加密
            $cart_sec_contents = json_decode($content['cart_sec_content'], true);
            // foreach ($cart_sec_contents['cart_sec_content'] as $key => $value) {
            //     $cart_sec_contents['cart_sec_content'][$key]['url'] = $commonModel->encrypt($value['url']);
            // }
            $dsldata = json_decode($content['cart_sec_content_dsl'], 1);
            $arr = array(
                'cart_section_id' => $data['cart_section_id'],
                'title' => $data['title'],
                'other_name' => $lang['other_name'],
                'cartoon_id' => $data['cartoon_id'],
                'hits' => $data['likes'],
                'coin' => $data['charge_coin'],
                'update_time' => strtotime($data['update_time']),
                'images' => $cart_sec_contents['cart_sec_content'],
                'images_dsl' => $dsldata['pic'],
                'dsl' => $dsldata['dsl'],
                'isfree' => $data['isfree'],
                'list_order' => $data['list_order'],
                'ispay' =>  0,
                // 'isvip' => M('user', 'im')->checkvip($this->uid),
            );
            // M('census', 'im')->cartoonfreereadcounts($cartoon_id);
            $down = T($tpsec)->set_where(['cartoon_id' => $cartoon_id, 'isdelete' => 0, 'status' => 1])->set_where('list_order<' . $data['list_order'])->set_field('cart_section_id')->order_by(['f' => 'list_order', 's' => 'down'])->get_one();
            $up = T($tpsec)->set_where(['cartoon_id' => $cartoon_id, 'isdelete' => 0, 'status' => 1])->set_where('list_order>' . $data['list_order'])->set_field('cart_section_id')->order_by(['f' => 'list_order', 's' => 'up'])->get_one();
            $arr['next'] = $up['cart_section_id'] ? $up['cart_section_id'] : '0';
            $arr['pre'] = $down['cart_section_id'] ? $down['cart_section_id'] : '0';

            Y::$cache->set($index, $arr, 7200);
        }
        $commonModel = M('book', 'im');
        $commonModel->userReadHistory($users_id, 2, $cartoon_id, $cart_section_id);
        M('bookcensus', 'im')->sceread($users_id, 2, $cartoon_id, $cart_section_id);
        if ($arr['isfree']) {


            if ($users_id) {
                $arr['ispay'] = T('expend')->set_field('users_id')->where(['users_id' => $users_id, 'expend_type' => 2, 'book_id' => $cartoon_id, 'section_id' => $cart_section_id])->get_one() ? 1 : 0;
            }
            // d($arr['ispay']);
            if (!$arr['ispay']) {

                //判断是否收费章节，是就直接跳转登入页面
                // Out::jerror('请登入', null, 110110);
                //这里截断
                if (is_array($arr['images']) && sizeof($arr['images']) > 3) {
                    $arr['images'] = array_slice($arr['images'], 0, 3);
                }
                if (is_array($arr['images_dsl']) && sizeof($arr['images_dsl']) > 3) {
                    $arr['images_dsl'] = array_slice($arr['images_dsl'], 0, 3);
                }
            }
        }
        return $arr;
        // $this->returnSuccess($arr);
    }
    public function book($bid, $sid, $uid)
    {
        $book_id = $bid;
        $section_id = &$sid;
        $index = 'bcontent_' . $book_id . '_:' . $section_id;
        $where = [
            'book_id' => $book_id,
            'section_id' => $section_id,
        ];
        $lang = T('book')->set_where(['book_id' => $book_id,])->set_field('lang,other_name')->get_one();
        $tpsec = M('book', 'im')->gettpsec(1, $lang['lang']);
        if (!$sid) {
            $first = T($tpsec)->set_field('section_id')->set_where(['book_id' => $bid, 'status' => 1])->order_by(['s' => 'up', 'f' => 'list_order'])->get_one();
            $sid = $first['section_id'];
            $where['section_id'] = $sid;
        }

        $tpsecc = M('book', 'im')->gettpseccontent(1, $lang['lang']);
        $cache = Y::$cache->get($index);

        if ($cache[0]) {
            $arr = $cache[1];
            if (!$arr['next']) {
                $up = T($tpsec)->set_field('section_id')->set_where(['book_id' => $book_id, 'isdelete' => 0, 'status' => 1])->set_where('list_order>' . $arr['list_order'])->set_field('section_id')->order_by(['f' => 'list_order', 's' => 'up'])->get_one();
                $arr['next'] = $up['section_id'] ? $up['section_id'] : '0';
            }
        } else {
            $data = T($tpsec)->field('section_id,title,book_id,update_time,isfree,secnum,list_order,coin')->where($where)->where(['status' => 1])->where(['isdelete' => 0])->get_one();

            if (!$data) {
                return false;
                // Out::jerror('小说或章节不存在', null, '100154');
            }
           
            $content = T($tpsecc,null,"content")->field('sec_content,sec_content_id')->where(['section_id' => $data['section_id']])->where(['isdelete' => 0])->find();
            // 引入aes加密
            if (!$content) {
                return false;
                // Out::jerror('章节不存在或删除', null, '100153');
            }
            $coin = $data['coin'];
            $arr = $data;

            $arr['update_time'] = strtotime($data['update_time']);
            $arr['sec_content_id'] = $content['sec_content_id'];


            $arr['sec_content'] = M('book', 'im')->trimhtml($content['sec_content']);

            $down = T($tpsec)->set_field('section_id')->set_where(['book_id' => $book_id, 'isdelete' => 0, 'status' => 1])->set_where('list_order<' . $data['list_order'])->set_field('section_id')->order_by(['f' => 'list_order', 's' => 'down'])->get_one();
            $arr['pre'] = $down['section_id'] ? $down['section_id'] : '0';
            $arr['coin'] = $coin;
            $arr['other_name'] = $lang['other_name'];
            $up = T($tpsec)->set_field('section_id')->set_where(['book_id' => $book_id, 'isdelete' => 0, 'status' => 1])->set_where('list_order>' . $data['list_order'])->set_field('section_id')->order_by(['f' => 'list_order', 's' => 'up'])->get_one();
            $arr['next'] = $up['section_id'] ? $up['section_id'] : '0';
            if (!$arr['coin'] <= 0  && $arr['isfree'] != 0) {
                $arr['coin'] = intval(M('coin', 'im')->bookcalculate($arr['secnum'], 0.6));
                //更新章节金币值
              
                T($tpsec)->update(['coin' => $arr['coin']], ['section_id' => $arr['section_id']]);
            }
            //内容容错自修复机制；缓存2天；后台修改了数据后2天重新覆盖
            Y::$cache->set($index, $arr,  G_DAY);
        }
        //这些都是要实时更新的
        //缓存中下一章获取失败就试着从数据库在获取一次
        $commonModel = M('book', 'im');
        $commonModel->userReadHistory($uid,1, $book_id,  $section_id);
        M('bookcensus', 'im')->sceread($uid, 1, $book_id, $section_id);
        if ($arr['isfree']) {
            if ($uid) {
                $arr['ispay'] = T('expend')->set_field('users_id')->where(['users_id' => $uid, 'expend_type' => 1, 'book_id' => $book_id, 'section_id' => $section_id])->get_one() ? 1 : 0;
            }

            if (!$arr['coin'] <= 0 && $arr['isfree'] != 0) {
                $arr['coin'] = M('coin', 'im')->bookcalculate($arr['secnum'], 0.6);
            }
            //如果是付费的章节内容截取滞留最大100个单词
            if (!$arr['ispay']) {
                // d($arr['sec_content']);
                // $pattern = "/.{300,}\s/";
                // preg_match($pattern, $arr['sec_content'], $result);
                // d($result[0]);
                // if ($result[0]) {
                //     $arr['sec_content'] =   $result[0];
                // }
                $arr['sec_content'] = mb_substr($arr['sec_content'], 0, 300);
            }
        }

        return ($arr);
    }
    //获取漫画章节列表
    public function cart_section($uid, $bookid)
    {
        $cartoon_id = $bookid;
        $index = 'cseclist_:' . $cartoon_id;
        $cache = Y::$cache->get($index);
        if ($cache[0]) {
            $data = $cache[1];
        } else {
            $data = M('book', 'im')->getcartsectionlist($cartoon_id);
            //缓存半天
            if ($data) {
                Y::$cache->set($index, $data, 600);
            }
        }
        $expend_ispay_arr = [];
        if ($uid) {
            $expend_list = T('expend')->field('ispay,section_id')->where(['users_id' => $uid, 'book_id' => $cartoon_id, 'expend_type' => 2])->get_all();
            $expend_ispay_arr = array_column($expend_list, 'ispay', 'section_id');
            foreach ($data as $key => $val) {
                if ($val['isfree'] == 1) {
                    if (isset($expend_ispay_arr[$val['section_id']])) {
                        $data[$key]['ispay'] = $expend_ispay_arr[$val['section_id']];
                    }
                } elseif ($val['isfree'] == 4) {
                    $data[$key]['isadvert'] = 1;
                    if (isset($expend_ispay_arr[$val['section_id']])) {
                        $data[$key]['ispay'] = $expend_ispay_arr[$val['section_id']];
                    }
                }
            }
        }
        return ($data);
    }
    //获取小说章节列表
    public function book_section($uid, $bookid)
    {
        $book_id =  ['book_id' => $bookid];
        if (!$uid) {
            $users_id = false;
        } else {
            $users_id = $uid;
        }
        $w = $book_id;
        $money = T('book')->field('money,section,lang')->where($book_id)->find();
        $index = 'bseclist_:' . $book_id['book_id'];
        //取最新一条
        //判断 是否相等，相等就取缓存，不相等就取数据库的
        $w['isdelete'] = 0;
        $w['status'] = 1;
        if ($money['lang'] == 0) {
            $tpsec = 'section';
        } else {
            $tpsec = 'section_' . $money['lang'];
        }
        $now = T($tpsec)->set_field('list_order')->where($w)->order('list_order desc')->get_one();
        if ($now['list_order'] != $money['section']) {
            $data = M('book', 'im')->getsectionlist($w['book_id'], $money['money']);
            T('book')->update(['section' => $now['list_order']], $book_id);
            Y::$cache->set($index, $data, 1800);
        } else {
            $cache = Y::$cache->get($index);
            if ($cache[0]) {
                $data = $cache[1];
            } else {
                $data = M('book', 'im')->getsectionlist($w['book_id'], $money['money']);
                Y::$cache->set($index, $data, 600);
            }
        }


        $expend_ispay_arr = [];
        if ($users_id) {

            //有用户id才取消耗表
            // $sec_ids = implode(',', array_column($data, 'section_id'));
            //$sec_ids = array_column($data, 'section_id');
            $expend_list = T('expend')->field('ispay,section_id')->where(['users_id' => $users_id, 'book_id' => $book_id, 'expend_type' => 1])
                ->get_all();
            $expend_ispay_arr = array_column($expend_list, 'ispay', 'section_id');


            foreach ($data as $key => $val) {
                if ($val['isfree'] == 1) {
                    if (isset($expend_ispay_arr[$val['section_id']])) {
                        $data[$key]['ispay'] = $expend_ispay_arr[$val['section_id']];
                    }
                } elseif ($val['isfree'] == 4) {
                    $data[$key]['isadvert'] = 1;
                    if (isset($expend_ispay_arr[$val['section_id']])) {
                        $data[$key]['ispay'] = $expend_ispay_arr[$val['section_id']];
                    }
                }
                // $data[$key]['update_time'] = strtotime($val['update_time']);
            }
        }
        return ($data);
    }
}
