<?php

namespace ng169\control\apiv1;

use ng169\control\apiv1base;
use ng169\tool\Out;
use ng169\Y;

checktop();
class content extends apiv1base
{
    protected $noNeedLogin = ['*'];
    public function control_get_cartoon()
    {
        M('version', 'im')->lockold($this->head['version']);
        $cartoon = get(['int' => ['cartoon_id' => 1, 'cart_section_id' => 1]]);
        $cartoon_id = $cartoon['cartoon_id'];
        $cart_section_id = $cartoon['cart_section_id'];
        M('census', 'im')->logcount($this->get_userid()); //安装统计
        $lang = T('cartoon')->set_where(['cartoon_id' => $cartoon_id,])->set_field('lang')->get_one();
        $tpsec = M('book', 'im')->gettpsec(2, $lang['lang']);
        $tpsecc = M('book', 'im')->gettpseccontent(2, $lang['lang']);
        $users_id = $this->uid;

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
                Out::jerror('章节不存在', null, '102222');
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
            Y::$cache->set($index, $arr);
        }
        $commonModel = M('book', 'im');
        $commonModel->user_read_history($users_id, "", $cartoon_id, $cart_section_id);
        M('bookcensus', 'im')->sceread($users_id, 2, $cartoon_id, $cart_section_id);
        if ($arr['isfree']) {


            if ($this->get_userid()) {
                $arr['ispay'] = T('expend')->set_field('users_id')->where(['users_id' => $this->get_userid(), 'expend_type' => 2, 'book_id' => $cartoon_id, 'section_id' => $cart_section_id])->get_one() ? 1 : 0;
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
        $this->returnSuccess($arr);
    }
    public function control_get_book()
    {
        M('version', 'im')->lockold($this->head['version']);
        $get = get(['int' => ['book_id' => 1, 'section_id' => 1]]);
        $book_id = $get['book_id'];
        $section_id = $get['section_id'];
        $index = 'bcontent_' . $book_id . '_:' . $section_id;
        $where = [
            'book_id' => $book_id,
            'section_id' => $section_id,
        ];
        $lang = T('book')->set_where(['book_id' => $book_id,])->set_field('lang')->get_one();
        $tpsec = M('book', 'im')->gettpsec(1, $lang['lang']);
        $tpsecc = M('book', 'im')->gettpseccontent(1, $lang['lang']);
        $cache = Y::$cache->get($index);
        if ($cache[0]) {
            $arr = $cache[1];
            if (!$arr['next']) {
                $up = T($tpsec)->set_field('section_id')->set_where(['book_id' => $get['book_id'], 'isdelete' => 0, 'status' => 1])->set_where('list_order>' . $arr['list_order'])->set_field('section_id')->order_by(['f' => 'list_order', 's' => 'up'])->get_one();
                $arr['next'] = $up['section_id'] ? $up['section_id'] : '0';
            }
        } else {
            $data = T($tpsec)->field('section_id,title,book_id,update_time,isfree,secnum,list_order,coin')->where($where)->where(['status' => 1])->where(['isdelete' => 0])->find();
            if (!$data) {
                Out::jerror('小说或章节不存在', null, '100154');
            }
            $content = T($tpsecc,null,"content")->field('sec_content,sec_content_id')->where(['section_id' => $data['section_id']])->where(['isdelete' => 0])->find();
            // 引入aes加密
            if (!$content) {
                Out::jerror('章节不存在或删除', null, '100153');
            }
            $coin = $data['coin'];
            $arr = $data;

            $arr['update_time'] = strtotime($data['update_time']);
            $arr['sec_content_id'] = $content['sec_content_id'];


            $arr['sec_content'] = M('book', 'im')->trimhtml($content['sec_content']);

            $down = T($tpsec)->set_field('section_id')->set_where(['book_id' => $get['book_id'], 'isdelete' => 0, 'status' => 1])->set_where('list_order<' . $data['list_order'])->set_field('section_id')->order_by(['f' => 'list_order', 's' => 'down'])->get_one();
            $arr['pre'] = $down['section_id'] ? $down['section_id'] : '0';
            $arr['coin'] = $coin;
            $up = T($tpsec)->set_field('section_id')->set_where(['book_id' => $get['book_id'], 'isdelete' => 0, 'status' => 1])->set_where('list_order>' . $data['list_order'])->set_field('section_id')->order_by(['f' => 'list_order', 's' => 'up'])->get_one();
            $arr['next'] = $up['section_id'] ? $up['section_id'] : '0';
            if (!$arr['coin'] <= 0  && $arr['isfree'] != 0) {
                $arr['coin'] = M('coin', 'im')->bookcalculate($arr['secnum'], 0.6);
            }
            //内容容错自修复机制；缓存2天；后台修改了数据后2天重新覆盖
            Y::$cache->set($index, $arr, 2 * G_DAY);
        }
        //这些都是要实时更新的
        //缓存中下一章获取失败就试着从数据库在获取一次
        $commonModel = M('book', 'im');
        $commonModel->user_read_history($this->get_userid(), $book_id, "",$get['section_id']);
        M('bookcensus', 'im')->sceread($this->get_userid(), 1, $get['book_id'], $get['section_id']);
        if ($arr['isfree']) {
            if ($this->get_userid()) {
                $arr['ispay'] = T('expend')->set_field('users_id')->where(['users_id' => $this->get_userid(), 'expend_type' => 1, 'book_id' => $book_id, 'section_id' => $section_id])->get_one() ? 1 : 0;
            }

            if (!$arr['coin'] <= 0 && $arr['isfree'] != 0) {
                $arr['coin'] = M('coin', 'im')->bookcalculate($arr['secnum'], 0.6);
            }
            //如果是付费的章节内容截取滞留最大100个单词
            if (!$arr['ispay']) {
                $pattern = "/.{300,}\s/";
                preg_match($pattern, $arr['sec_content'], $result);
                if ($result[0]) {
                    $arr['sec_content'] =   $result[0];
                }
            }
        }
        $this->returnSuccess($arr);
    }
}
