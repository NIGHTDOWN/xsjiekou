<?php

namespace ng169\control\apiv1;

use ng169\control\apiv1base;
use ng169\tool\Out;
use ng169\Y;

checktop();
class cartoon extends apiv1base
{
    protected $noNeedLogin = ['*'];
    // 获取漫画首页列表信息
    // public function control_get_vip()
    // {
    //     $data = get(['int' => ['page']]);

    //     // $other_name = get(['string' => ['other_name']]);
    //     $where['status'] = 1;
    //     $where['groom_type'] = 4;
    //     $where['type'] = 2;
    //     $data = T('groom')
    //         ->field('v.cartoon_id,v.other_name,v.bpic,isfree,update_status,v.`desc`,writer_name')->join_table(['t' => 'cartoon', 'cartoon_id', 'cartoon_id'])
    //         ->where($where)
    //         ->order('list_order asc')
    //         //->limit([$data['page'], 3])
    //         ->get_all();
    //     $this->returnSuccess($data);
    // }
    // public function control_get_cartoonList()
    // {
    //     $data = get(['int' => ['page']]);

    //     // $other_name = get(['string' => ['other_name']]);

    //     $where['status'] = 1;
    //     $where['groom_type'] = 3;
    //     $where['type'] = 2;
    //     $data = T('groom')
    //         ->field('v.cartoon_id,v.other_name,v.bpic,isfree,update_status,v.`desc`,v.writer_name')->join_table(['t' => 'cartoon', 'cartoon_id', 'cartoon_id'])
    //         ->where($where)
    //         ->order('list_order asc')
    //         ->limit([$data['page'], 3])
    //         ->get_all();
    //     $this->returnSuccess($data);
    // }
    // 获取主编力荐漫画
    // public function control_hotw_cart_groom()
    // {
    //     $where['status'] = 1;
    //     $where['groom_type'] = 3;
    //     $where['type'] = 2;
    //     $data = T('groom')->field('cartoon_id,other_name,bpic,`desc`')
    //         ->where($where)
    //         ->order('list_order asc')
    //         ->limit([0, 5])
    //         ->get_all();
    //     $cartoon_id = "";
    //     foreach ($data as $key => $value) {
    //         $data[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
    //         $cartoon_id .= $value['cartoon_id'] . ",";
    //     }
    //     $cartoon_id = rtrim($cartoon_id, ",");
    //     $option = T('option')
    //         ->field('option_value')
    //         ->where(['option_name' => 'virtual_setting'])
    //         ->find();
    //     $datas = json_decode($option['option_value']);
    //     $arrs = (array) $datas;
    //     if ($arrs['virtual'] == '1') {
    //         $cartoon = T('cartoon')->field('reward_icon,writer_name,virtual_coin,is_virtual,update_status,cartoon_id')->whereIn('cartoon_id', $cartoon_id)->order('virtual_coin desc')->get_all();
    //     } else {
    //         $cartoon = T('cartoon')->field('reward_icon,writer_name,virtual_coin,is_virtual,update_status,cartoon_id')->whereIn('cartoon_id', $cartoon_id)->order('reward_icon desc')->get_all();
    //     }
    //     // foreach ($data as $key => $value) {
    //     //     foreach ($cartoon as $ke => $val) {
    //     //         if ($value['cartoon_id'] == $val['cartoon_id']) {

    //     //         }
    //     //     }
    //     // }
    //     $this->returnSuccess($data);
    // }
    // 获取漫画详细信息
    public function control_get_cartoonDetail()
    {
        $cartoon_id = get(['int' => ['cartoon_id' => 1]]);
        $cartoon_id = $cartoon_id['cartoon_id'];

        //判断是否已经添加书架
        $type = 2;
        $get['bookid'] = $cartoon_id;
        $detail = M('book', 'im')->detail($get['bookid'], $this->get_userid(), $type);
        if ($detail) {
            $detail['inrack'] = M('rack', 'im')->in_rack($this->get_userid(), $type, $get['bookid']);
            $detail['sahretag'] = implode(',', array_column($detail['data']['tags'], 'tag'));
            // d($detail);
            $detail['his'] = M('rack', 'im')->getbookhis($this->get_userid(), $type, $get['bookid']);
            $tjcache = 'tjcache' . $get['bookid'] . $detail['data']['type'];
            list($bool, $cache) = Y::$cache->get($tjcache);

            if ($bool) {
                if (sizeof($cache)) {
                    $detail = array_merge($detail, $cache);
                }
            } else {
                $similars = M('book', 'im')->getsimilar($get['bookid'], $detail['data']['type'], 6);

                $author = M('book', 'im')->getsimilarauthor($get['bookid'], $detail['data']['type'], 3);

                if (sizeof($similars)) {
                    $detailtmp['similar'] = T('cartoon')->set_field('bpic,cartoon_id as book_id,' . $type . ' as type,other_name,lable,lang,`read`')->whereIn('cartoon_id', $similars)->set_limit(50)->get_all();
                    foreach ($detailtmp['similar']  as $k => $book) {
                        $detailtmp['similar'][$k]['tags'] =  M('cate', 'im')->getlable($book['lable'], $book['lang']);
                    }
                }
                if (sizeof($author)) {
                    $detailtmp['author'] = T('cartoon')->set_field('bpic,cartoon_id as book_id,' . $type . ' as type,other_name,lable,lang,`read`')->whereIn('cartoon_id', $author)->set_limit(50)->get_all();
                    foreach ($detailtmp['author']  as $k => $book) {
                        $detailtmp['author'][$k]['tags'] =  M('cate', 'im')->getlable($book['lable'], $book['lang']);
                    }
                }
                if (sizeof($detailtmp)) {
                    $detail = array_merge($detail, $detailtmp);
                    Y::$cache->set($tjcache, $detailtmp, G_DAY * 2);
                }
            }

            $this->returnSuccess($detail);
        } else {
            $this->returnSuccess([]);
        }
    }
    private function getdiscussavater($discuss)
    {
        // $discusstmp = array_column($discuss, null, 'users_id');
        $userids = array_column($discuss, 'users_id');
        if ($userids && sizeof($userids)) {
            $us = T('third_party_user')->field('id,avater')->whereIn('id', $userids)->get_all();
            $ua = array_column($us, 'avater', 'id');
            foreach ($discuss as $k => $dis) {
                $discuss[$k]['avater'] = $ua[$dis['users_id']];
            }
        } else {
        }
        return $discuss;
    }
    // 获取漫画章节
    public function control_get_cart_section()
    {

        $cartoon_id = get(['int' => ['cartoon_id' => 1]]);
        $data = M('content', 'im')->cart_section($this->get_userid(), $cartoon_id['cartoon_id']);

        $this->returnSuccess($data);
    }
    // 获取漫画内容
    // public function control_get_cart_sec_content()
    // {
    //     return false;
    //     $cartoon = get(['int' => ['cartoon_id' => 1, 'cart_section_id' => 1]]);
    //     $cartoon_id = $cartoon['cartoon_id'];
    //     $cart_section_id = $cartoon['cart_section_id'];

    //     $device_type = $this->head['devicetype'];
    //     $version = $this->head['version'];
    //     if ($device_type == 'android' && version_compare($version, '1.0.16', '<')) {

    //         Out::jerror('版本太舊了，請更新', null, '100130');
    //     }
    //     $users_id = $this->uid;
    //     // if (empty($cartoon_id) && empty($cart_section_id)) {
    //     //     $this->returnData(200, Lang::get("พารามิเตอร์ที่ต้องกันเว้นว่างไม่ได้"));
    //     // }
    //     $where = [
    //         'cartoon_id' => $cartoon_id,
    //         'cart_section_id' => $cart_section_id,
    //     ];
    //     $commonModel = M('book', 'im');
  
    //     M('census', 'im')->uprackreadtime($this->get_userid(), $cartoon_id, 1, $cart_section_id);
    //     $data = T('cartoon_section')->field('cart_section_id,title,cartoon_id,likes,collects,update_time,isfree')->where($where)->where(['status' => 1])->where(['isdelete' => 0])->find();
    //     $content = T('cart_sec_content')->field('cart_sec_content,cart_sec_content_id')->where(['cart_section_id' => $data['cart_section_id']])->where(['isdelete' => 0])->find();
    //     $discuss = T('discuss_cart_section')
    //         ->field('cart_sec_discuss_id,star,nick_name,discuss_time,content')
    //         ->where(['cart_section_id' => $cart_section_id])
    //         ->where(['status' => 1])
    //         ->order('star desc,discuss_time desc')
    //         ->limit([0, 1])
    //         ->get_all();
    //     $w = ['cart_section_id' => $cart_section_id];
    //     $count = T('discuss_cart_section')->where($w)->where(['status' => 1])->get_count();

    //     if (!($users_id)) {
    //         $re = T('user_hits_count')->where($w)->where(['users_id' => $users_id])->find();
    //         if ($re) {
    //             $hit_status = 1;
    //         } else {
    //             $hit_status = 0;
    //         }
    //     } else {
    //         $hit_status = "";
    //     }
    //     $res = [
    //         'discuss' => $discuss,
    //         'count' => $count,
    //     ];
    //     // aes加密
    //     $cart_sec_contents = json_decode($content['cart_sec_content'], true);
    //     foreach ($cart_sec_contents['cart_sec_content'] as $key => $value) {
    //         $cart_sec_contents['cart_sec_content'][$key]['url'] = $commonModel->encrypt($value['url']);
    //     }
    //     $arr = array(
    //         'cart_section_id' => $data['cart_section_id'],
    //         'title' => $data['title'],
    //         'cartoon_id' => $data['cartoon_id'],
    //         'hits' => $data['likes'],
    //         'update_time' => strtotime($data['update_time']),
    //         'hit_status' => $hit_status,
    //         'cart_sec_contents' => $cart_sec_contents,
    //         'discussd' => $res,
    //         'isfree' => $data['isfree'],
    //         'ispay' => T('expend')->where(['users_id' => $this->get_userid(), 'expend_type' => 2, 'book_id' => $cartoon_id, 'section_id' => $cart_section_id])->get_one() ? 1 : 0,
    //         'isvip' => M('user', 'im')->checkvip($this->uid),
    //     );
    //     M('census', 'im')->cartoonfreereadcounts($cartoon_id);
    //     M('bookcensus', 'im')->sceread($this->get_userid(), 1, $cartoon_id, $cartoon['cart_section_id']);
    //     $down = T('cartoon_section')->set_where(['cartoon_id' => $cartoon_id])->set_where('cart_section_id<' . $cartoon['cart_section_id'])->set_field('cart_section_id')->order_by(['f' => 'cart_section_id', 's' => 'down'])->get_one();
    //     $up = T('cartoon_section')->set_where(['cartoon_id' => $cartoon_id])->set_where('cart_section_id>' . $cartoon['cart_section_id'])->set_field('cart_section_id')->order_by(['f' => 'cart_section_id', 's' => 'up'])->get_one();
    //     $arr['next'] = $up['cart_section_id'] ? $up['cart_section_id'] : '0';
    //     $arr['pre'] = $down['cart_section_id'] ? $down['cart_section_id'] : '0';
    //     $this->returnSuccess($arr);
    // }
    public function control_new_cartoon()
    {
        M('version', 'im')->cheknew($this->head['version']);
        $get = get(['int' => ['page']]);
        $where['status'] = 1;
        $cityid = $this->head['cityid'];
        $where['lang'] = $cityid;
        $index = 'cartget_cartoon_page_:' . $get['page'] . "_" . $cityid;
        $cache = Y::$cache->get($index);
        if ($cache[0]) {
            $this->returnSuccess($cache[1]);
        } else {
            // recommend_num
            $data = T('cartoon')
                ->field('cartoon_id,other_name,bpic_dsl,bpic,`desc`,hits as recommend_num,writer_name,isfree,update_status,2 as type')
                ->where($where)
                ->order('section desc,hits desc')
                ->limit([$get['page'], 10])
                ->get_all();
            // $data=M('bookrandom','im')->getcartoon($this->head['cityid'],5);
            if (is_array($data) && sizeof($data)) {
                Y::$cache->set($index, $data, 46000);
            }
            // Y::$cache->set($index, $data, 86400);
            $this->returnSuccess($data);
        }
    }
    public function control_get_randList()
    {
        M('version', 'im')->cheknew($this->head['version']);
        $list = M('bookrandom', 'im')->getcartoon($this->head['cityid']);
        Out::jout($list);
    }
    //获取推荐漫画
    public function control_hot_cartoon()
    {
        M('version', 'im')->cheknew($this->head['version']);
        $get = get(['int' => ['page']]);
        $where['status'] = 1;
        $cityid = $this->head['cityid'];
        $where['lang'] = $cityid;
        $index = 'cartget_cartoon7_' . $get['page'] . "_" . $cityid;
        $cache = Y::$cache->get($index);
       
        if ($cache[0]) {
            $this->returnSuccess($cache[1]);
        } else {
            // recommend_num
            $data = M('bookrandom', 'im')->getcartoon($this->head['cityid'], 5);
           
            if (is_array($data) && sizeof($data)) {
                foreach ($data as $key => $value) {
                    # code...
                    $data[$key]['recommend_num'] = rand(10000, 99999) . "";
                }
                Y::$cache->set($index, $data, 46000);
            }
            // Y::$cache->set($index, $data, 86400);
            $this->returnSuccess($data);
        }
    }
    // 获取漫画内容
    public function control_get_wap_content()
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
            $arr = array(
                'cart_section_id' => $data['cart_section_id'],
                'title' => $data['title'],
                'cartoon_id' => $data['cartoon_id'],
                'hits' => $data['likes'],
                'coin' => $data['charge_coin'],
                'update_time' => strtotime($data['update_time']),
                'images' => $cart_sec_contents['cart_sec_content'],
                'images_dsl' => json_decode($content['cart_sec_content_dsl'], 1),
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
        $commonModel->userReadHistory($users_id, 2, $cartoon_id);
        
        M('bookcensus', 'im')->sceread($users_id, 2, $cartoon_id, $cart_section_id);
        if ($arr['isfree']) {


            if ($this->get_userid()) {
                $arr['ispay'] = T('expend')->set_field('users_id')->where(['users_id' => $this->get_userid(), 'expend_type' => 2, 'book_id' => $cartoon_id, 'section_id' => $cart_section_id])->get_one() ? 1 : 0;
            }
            // d($arr['ispay']);
            if (!$arr['ispay']) {
                //8.0.0以下版本

                // if (is_array($arr['images']) && sizeof($arr['images']) > 3) {
                //     $arr['images']= array_slice($arr['images'],0,3);
                // }
                // if (is_array($arr['images_dsl']) && sizeof($arr['images_dsl']) > 3) {
                //     $arr['images_dsl']= array_slice($arr['images_dsl'],0,3);
                // }
            }
        }
        $this->returnSuccess($arr);
    }
    public function control_get_wapcontent()
    {
        $cartoon = get(['int' => ['cartoon_id' => 1, 'cart_section_id' => 1]]);
        $cartoon_id = $cartoon['cartoon_id'];
        $cart_section_id = $cartoon['cart_section_id'];
        $lang = T('cartoon')->set_where(['cartoon_id' => $cartoon_id,])->set_field('lang')->get_one();
        $tpsec = M('book', 'im')->gettpsec(2, $lang['lang']);
        $tpsecc = M('book', 'im')->gettpseccontent(2, $lang['lang']);
        $device_type = $this->head['devicetype'];
        $version = $this->head['version'];

        $users_id = $this->uid;

        $where = [
            'cartoon_id' => $cartoon_id,
            'cart_section_id' => $cart_section_id,
        ];
        $commonModel = M('book', 'im');
        $commonModel->user_read_history($users_id, "", $cartoon_id);
        // M('census', 'im')->uprackreadtime($this->get_userid(), $cartoon_id, 1, $cart_section_id);
        $data = T($tpsec)->field('cart_section_id,title,cartoon_id,likes,collects,update_time,isfree,charge_coin,list_order')->where($where)->where(['status' => 1])->where(['isdelete' => 0])->find();
        if (!$data) {
            Out::jerror('章节不存在', null, '102222');
        }
        if ($data['isfree'] != 0) {
            Out::jerror('收费章节，请下载app阅读', null, '105154');
        }
        $content = T($tpsecc,null,"content")->field('cart_sec_content,cart_sec_content_dsl,cart_sec_content_id')->where(['cart_section_id' => $data['cart_section_id']])->where(['isdelete' => 0])->find();
        // $discuss = T('discuss_cart_section')
        //     ->field('cart_sec_discuss_id,star,nick_name,discuss_time,content')
        //     ->where(['cart_section_id' => $cart_section_id])
        //     ->where(['status' => 1])
        //     ->order('star desc,discuss_time desc')
        //     ->limit(1)
        //     ->get_all();
        $w = ['cart_section_id' => $cart_section_id];
        // $count = T('discuss_cart_section')->where($w)->where(['status' => 1])->get_count();
        if (!($users_id)) {
            $re = T('user_hits_count')->where($w)->where(['users_id' => $users_id])->find();
            if ($re) {
                $hit_status = 1;
            } else {
                $hit_status = 0;
            }
        } else {
            $hit_status = "";
        }
        $res = [
            // 'discuss' => $discuss,
            // 'count' => $count,
        ];
        // aes加密
        $cart_sec_contents = json_decode($content['cart_sec_content'], true);
        // foreach ($cart_sec_contents['cart_sec_content'] as $key => $value) {
        //     $cart_sec_contents['cart_sec_content'][$key]['url'] = $commonModel->encrypt($value['url']);
        // }
        $arr = array(
            'cart_section_id' => $data['cart_section_id'],
            'title' => $data['title'],
            'cartoon_id' => $data['cartoon_id'],
            'hits' => $data['likes'],
            'coin' => $data['charge_coin'],
            'update_time' => strtotime($data['update_time']),
            'hit_status' => $hit_status,
            'images' => $cart_sec_contents['cart_sec_content'],
            'images_dsl' => $cart_sec_contents['cart_sec_content_dsl'],
            // 'discussd' => $res,
            'isfree' => $data['isfree'],
            'ispay' => T('expend')->where(['users_id' => $this->uid, 'expend_type' => 2, 'book_id' => $cartoon_id, 'section_id' => $cart_section_id])->get_one() ? 1 : 0,
            'isvip' => M('user', 'im')->checkvip($this->uid),
        );

        // M('census', 'im')->cartoonfreereadcounts($cartoon_id);
        M('bookcensus', 'im')->sceread($this->get_userid(), 2, $cartoon_id, $cart_section_id);
        $down = T($tpsec)->set_where(['cartoon_id' => $cartoon_id, 'isdelete' => 0, 'status' => 1])->set_where('list_order<' . $data['list_order'])->set_field('cart_section_id')->order_by(['f' => 'list_order', 's' => 'down'])->get_one();
        $up = T($tpsec)->set_where(['cartoon_id' => $cartoon_id, 'isdelete' => 0, 'status' => 1])->set_where('list_order>' . $data['list_order'])->set_field('cart_section_id')->order_by(['f' => 'list_order', 's' => 'up'])->get_one();
        $arr['next'] = $up['cart_section_id'] ? $up['cart_section_id'] : '0';
        $arr['pre'] = $down['cart_section_id'] ? $down['cart_section_id'] : '0';
        $this->returnSuccess($arr);
    }
    // 获取幻灯片图片
    // public function control_get_banner()
    // {

    //     $where1['status'] = '1';
    //     $where1['scan_seat'] = '2';
    //     $where = 'goal_type !=2';
    //     $plat = $this->head['devicetype'];
    //     if ($plat == 'iphone') {
    //         $where3 = "plat!='android'";
    //     } elseif ($plat == 'android') {
    //         $where3 = "plat!='ios'";
    //     }
    //     $data = T('banner')
    //         ->field('scan_seat,goal_type,goal_window,list_order,cartoon_id,banner_pic,banner_url,banner_name')
    //         ->where($where1)
    //         ->where($where)
    //         ->where($where3)
    //         ->order('list_order asc')
    //         ->get_all();
    //     $cartoon_id = "";
    //     foreach ($data as $key => $value) {
    //         $cartoon_id .= $value['cartoon_id'] . ",";
    //     }
    //     $cartoon_id = rtrim($cartoon_id, ",");
    //     $cartoon = T('cartoon')->field('other_name,`desc`,cartoon_id')->whereIn('cartoon_id', $cartoon_id)->get_all();
    //     foreach ($data as $key => $value) {
    //         foreach ($cartoon as $k => $v) {
    //             if ($value['cartoon_id'] == $v['cartoon_id']) {
    //                 $data[$key]['desc'] = str_replace("&quot;", "\"", $v['desc']);
    //                 $data[$key]['book_name'] = $v['other_name'];
    //             }
    //         }
    //         $data[$key]['book_id'] = $value['cartoon_id'];
    //         unset($data[$key]['cartoon_id']);
    //     }
    //     $this->returnSuccess($data);
    // }
    // 获取热门漫画推荐
    // public function control_hot_cart_groom()
    // {
    //     // 写入缓存
    //     $get = get(['int' => ['page']]);
    //     $where['status'] = 1;
    //     $data = T('cartoon')->field('v.cartoon_id,v.other_name,v.bpic,isfree,v.update_status,v.`desc`,writer_name')->join_table(['t' => 'cartoon_other', 'cartoon_id', 'cartoon_id'])
    //         ->where($where)
    //         ->order('cartoon_other.recharge DESC,cartoon_other.i_recharge desc')
    //         ->limit([$get['page'], 5])
    //         ->get_all();
    //     $this->returnSuccess($data);
    // }

}
