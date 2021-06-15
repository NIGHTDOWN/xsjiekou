<?php

namespace ng169\control\api;

use ng169\control\apibase;
use ng169\tool\Out;
use ng169\Y;

checktop();
class book extends apibase
{
    protected $noNeedLogin = ['*'];

    // 获取小说首页列表
    // public function control_get_bookList()
    // {
    //     $data = get(['string' => ['page']]);
    //     $other_name = get(['string' => ['other_name']]);
    //     $where['status'] = 1;

    //     $list = T('book')
    //         ->field('book_id,other_name,`desc`,bpic,reward_icon,writer_name,virtual_coin,is_virtual,isfree,update_status')
    //         ->where($where)
    //         ->wherelike('other_name', $other_name['other_name'])
    //         ->order('reward_icon desc')
    //         ->set_limit([$data['page'], 10]);
    //     $arr = $list->get_all();
    //     foreach ($arr as $key => $value) {
    //         $arr[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
    //     }
    //     $this->returnSuccess($arr);
    // }
    // public function control_get_vip()
    // {
    //     $where['status'] = 1;
    //     $where['groom_type'] = 4;
    //     $where['type'] = 1;
    //     $data = T('groom')->field('v.book_id,v.other_name,v.bpic,v.`desc`,reward_icon,writer_name,virtual_coin,is_virtual,update_status,isfree')
    //         ->join_table(['t' => 'book', 'book_id', 'book_id'])
    //         ->where($where)
    //         ->order('list_order desc')
    //         // ->limit([0, 3])
    //         ->get_all();
    //     $book_id = "";
    //     foreach ($data as $key => $value) {
    //         $data[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
    //         $book_id .= $value['book_id'] . ",";
    //     }
    //     $this->returnSuccess($data);
    // }
    // 获取主编力荐小说
    // public function control_hotw_groom()
    // {
    //     $where['status'] = 1;
    //     $where['groom_type'] = 3;
    //     $where['type'] = 1;
    //     $data = T('groom')->field('v.book_id,v.other_name,v.bpic,v.`desc`,reward_icon,writer_name,virtual_coin,is_virtual,update_status,isfree')
    //         ->join_table(['t' => 'book', 'book_id', 'book_id'])
    //         ->where($where)
    //         ->order('list_order desc')
    //         ->limit(5)
    //         ->get_all();
    //     $book_id = "";
    //     foreach ($data as $key => $value) {
    //         $data[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
    //         $book_id .= $value['book_id'] . ",";
    //     }

    //     $this->returnSuccess($data);
    // }
    // 获取限时免费小说
    // public function control_get_freeList()
    // {
    //     $data = get(['int' => ['page']]);
    //     $time = time();
    //     $freebook = T('book_free')->field('book_id,end_time')->where('end_time >' . $time)->where('status', 1)->order('bf_id desc')->find();
    //     $where['status'] = 1;
    //     $where['isfree'] = 0;

    //     $list = T('book')
    //         ->field('book_id,other_name,`desc`,bpic,reward_icon,writer_name,virtual_coin,is_virtual,isfree,update_status')
    //         ->where($where)
    //         ->whereIn('book_id', $freebook['book_id'])
    //         ->order('isfree desc')
    //         ->set_limit([10 * $data['page'], '10']);
    //     // }
    //     $arr = $list->get_all();
    //     // Out::jerror($arr);
    //     foreach ($arr as $key => $value) {
    //         $arr[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
    //     }
    //     if (!empty($freebook)) {
    //         $end_time = $freebook['end_time'];
    //     } else {
    //         $end_time = "60";
    //     }
    //     $tmp = [
    //         'data' => $arr,
    //         'end_time' => $end_time,
    //     ];
    //     $this->returnSuccess($tmp);
    // }
    // public function control_ranking()
    // {
    //     $data = get(['string' => ['page']]);
    //     $where['status'] = 1;
    //     $list = T('book')
    //         ->field('book_id,other_name,`desc`,bpic,reward_icon,writer_name,virtual_coin,is_virtual,isfree,update_status')
    //         ->where($where)
    //         // ->wherelike('other_name', $other_name['other_name'])
    //         ->order('`read` desc')
    //         ->set_limit([$data['page'], 10]);

    //     $arr = $list->get_all();
    //     foreach ($arr as $key => $value) {
    //         $arr[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
    //     }
    //     $this->returnSuccess($arr);
    // }

    // 获取小说章节
    // 获取小说章节
    public function control_get_section()
    {
        $book_id = get(['string' => ['book_id' => 1]]);
        if (!($this->uid)) {
            $users_id = false;
        } else {
            $users_id = $this->uid;
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
            Y::$cache->set($index, $data, 46000);
        } else {
            $cache = Y::$cache->get($index);
            if ($cache[0]) {
                $data = $cache[1];
            } else {
                $data = M('book', 'im')->getsectionlist($w['book_id'], $money['money']);
                Y::$cache->set($index, $data, 46000);
            }
        }


        $expend_ispay_arr = [];
        if ($users_id) {
            // $isvip = M('user', 'im')->checkvip($this->uid);
            // if ($isvip) {
            //     foreach ($data as $key => $val) {
            //         $data[$key]['isfree'] = 0;
            //         $data[$key]['update_time'] = strtotime($val['update_time']);
            //     }
            //     $this->returnSuccess($data);
            // }
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


        $this->returnSuccess($data);
    }
    // 获取小说详情
    public function control_get_bookDetail()
    {
        $book_id = get(['int' => ['book_id' => 1]]);
        $book_id = $book_id['book_id'];
        M('census', 'im')->logcount($this->get_userid()); //安装统计
        $w['book_id'] = $book_id;
        $index = 'bid:' . $book_id;
        //取书籍
        //取最新章节
        //取评论
        //判断是否登入
        //登入 修复书籍书架状态
        //修复未审核的评论
        $cache = Y::$cache->get($index);
        if ($cache[0]) {
            $arr = $cache[1];
        } else {
            $data = T('book')
                ->field('other_name,book_id,bpic,bpic_dsl,writer_name,`desc`,section,update_status,isfree,wordnum,end_share,share_banner,lang')
                ->where($w)
                ->find();
            if (!$data) {
                Out::jerror('书籍不存在', null, '100143');
            }
            $data['isgroom'] = 0;
            $w['status'] = 1;
            // $star = T('discuss')->where($w)->get_count();
            $w['isdelete'] = 0;
            if ($data['lang'] == 0) {
                $tpsec = 'section';
            } else {
                $tpsec = 'section_' . $data['lang'];
            }
            $new_section = T($tpsec)->where($w)->set_field('title,section_id,list_order')->order_by(['s' => 'down', 'f' => 'list_order'])->get_one();
            $update_section = $new_section['list_order'];
            unset($w['isdelete']);
            $sumss = T('discuss')->set_field('sum(star) as sums,count(1) as counts')->where($w)->get_one();
            $sums = $sumss['sums'];
            $star = $sumss['counts'];
            $replynum = "";
            if ($star == 0 && $sums == 0) {
                $replynum = 5;
            } else {
                $replynum = $sums / $star;
                $replynum = round($replynum, 1);
            }
            $discuss = T('discuss')
                ->field('discuss_id,star,nick_name,discuss_time,content,users_id')
                ->where($w)
                ->order('discuss_time desc')
                ->limit(5)
                ->get_all();

            $discuss = $this->getdiscussavater($discuss);

            $count = $star;
            $data['desc'] = str_replace("&quot;", "\"", $data['desc']);
            $data['section_count'] = $data['section'];
            $data['replynum'] = $replynum;
            $data['update_section'] = $update_section;
            $data['new_section_title'] = $new_section['title'];
            $data['new_section_id'] = $new_section['section_id'];
            unset($data['section']);
            $arr = [
                'data' => $data,
                'discussd' => [
                    'discuss' => $discuss,
                    'count' => $count,
                ],
            ];
            if (!$arr['data']['wordnum']) {
                // 修复字数
                M('book', 'im')->fixbooknum($book_id);
            }
            Y::$cache->set($index, $arr, 43200);
        }

        if ($this->get_userid()) {
            if (T('user_groom')->set_field('isgroom')->get_one(['status' => 1, 'book_id' => $book_id, 'users_id' => $this->get_userid(), 'type' => 1])) {
                $arr['data']['isgroom'] = 1;
            }
            $owenDiscuss = T('discuss')
                ->field('discuss_id,star,nick_name,discuss_time,content,users_id')
                ->where($w)
                ->where(['users_id' => $this->uid])
                ->order('discuss_time desc')
                // ->limit(1)
                ->get_one();
            if ($owenDiscuss) {
                // foreach ($owenDiscuss as  $key => $value) {
                # code...
                $owenDiscuss['avater'] = parent::$wrap_user['avater'];
                // }

                $arr['discussd']['discuss'] = array_merge([$owenDiscuss], $arr['discussd']['discuss']);
            }
        }

        // 点击统计
        M('census', 'im')->hitcounts($book_id);
        M('bookcensus', 'im')->readnum($this->get_userid(), 1, $book_id);

        // d(1,1);
        if ($arr) {
            $this->returnSuccess($arr);
        }
    }
    private function getdiscussavater($discuss)
    {
        $discusstmp = array_column($discuss, null, 'users_id');
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

    // 获取热门推荐小说
    // public function control_hot_groom()
    // {
    //     $where['status'] = 1;
    //     $where['groom_type'] = 1;
    //     $where['type'] = 1;
    //     $data = T('groom')->field('v.book_id,v.other_name,v.bpic,isfree')->join_table(['t' => 'book', 'book_id', 'book_id'])
    //         ->where($where)
    //         ->order('list_order desc')
    //         ->get_all();
    //     $this->returnSuccess($data);
    // }
    // 获取小说内容
    // public function control_get_section_content()
    // {

    //     $get = get(['int' => ['book_id' => 1, 'section_id' => 1]]);

    //     $book_id = $get['book_id'];
    //     $section_id = $get['section_id'];
    //     $device_type = $this->head['devicetype'];
    //     $version = $this->head['version'];
    //     if ($device_type == 'android' && version_compare($version, '1.0.16', '<')) {

    //         Out::jerror('版本太舊了，請更新', null, '100130');
    //     }

    //     $users_id = $this->uid;
    //     $where = [
    //         'book_id' => $book_id,
    //         'section_id' => $section_id,
    //     ];

    //     $commonModel = M('book', 'im');

    //     $commonModel->user_read_history($users_id, $book_id, "");

    //     M('census', 'im')->uprackreadtime($this->get_userid(), $book_id, 1, $section_id); //更新书架记录
    //     $data = T('section')->field('section_id,title,book_id,update_time,isfree')->where($where)->where(['status' => 1])->where(['isdelete' => 0])->find();
    //     if (!$data) {
    //         Out::jerror('小说或章节不存在', null, '100154');
    //     }
    //     $content = T('sec_content')->field('sec_content,sec_content_id')->where(['section_id' => $data['section_id']])->where(['isdelete' => 0])->find();
    //     // 引入aes加密
    //     if (!$content) {
    //         Out::jerror('章节不存在或删除', null, '100153');
    //     }
    //     $aes = Y::$newconf['aes'];
    //     $arr = array(
    //         'section_id' => $data['section_id'],
    //         'title' => $data['title'],
    //         'isfree' => $data['isfree'],
    //         'ispay' => T('expend')->where(['users_id' => $this->uid, 'expend_type' => 1, 'book_id' => $book_id, 'section_id' => $section_id])->get_one() ? 1 : 0,
    //         'isvip' => M('user', 'im')->checkvip($this->uid),
    //         'book_id' => $data['book_id'],
    //         'update_time' => strtotime($data['update_time']),
    //         'sec_content_id' => $content['sec_content_id'],
    //         'sec_content' => htmlspecialchars_decode($content['sec_content']),
    //         'amd5' => $commonModel->encrypt($aes['key']),
    //     );
    //     $arr['sec_contents'] = str_replace("/p", "\n\n", $arr['sec_content']);

    //     unset($arr['sec_content']);

    //     $arr['sec_content'] = strip_tags(htmlspecialchars_decode($arr['sec_contents']));
    //     unset($arr['sec_contents']);

    //     $arr['sec_content'] = str_replace("<", "   ", $arr['sec_content']);
    //     $arr['sec_content'] = str_replace(">", "   ", $arr['sec_content']);
    //     $arr['sec_content'] = str_replace("&nbsp;", "   ", $arr['sec_content']);
    //     $arr['sec_content'] = str_replace("&#39;", "'", $arr['sec_content']);
    //     $arr['sec_content'] = $commonModel->aes_encrypt($arr['sec_content']);

    //     M('census', 'im')->freereadcounts($book_id);
    //     M('bookcensus', 'im')->sceread($this->get_userid(), 1, $get['book_id'], $get['section_id']);
    //     $this->returnSuccess($arr);
    // }
    // public function control_test()
    // {
    //     $commonModel = M('book', 'im');
    //     $c = 'YnOZ81XtErb8ossJOF26maLNyTjRnOVbjtMQ4eyc+G1BTn\/wzsfEEG\/UrNefJSya\/VWO4j\/mXT1srYhe5oHWmcw7tisHXUAPCYG2mJLXZ\/2ZcZf\/DSuOyzQmy6z3T83CKuk\/g2QSGrjPtVglbym6IRuJyQl3z4Z2L4sLtWO0LVp\/+WYvJF4hyK1ugLr6d9G579MEox0sRwt1jGnWeWhE7cXZto0SiqQ\/zWNmD\/f0LBDLy+DqYIr\/FkIKQR+G8hjxet1+kd3yUtlLbgZwnLWfjq0Z4wXZX\/d7cUbhuhr90HNjwaI8XVe+i2Vne1C+10uQu36bUrGqQ79Pf8h+1UsOwAhBdJyKLmfWlJ7dLYfTXqzgy087DEPLcA+J7GtDybUfrwqZhr8BKQLzP+BctuWa41DgRZcbz+LNm7GgqfztkOCJslzXyYwX2Lyhc1Oq783DJANINxtC7RCe2IzKn14ungW0GogFWWRZy5Fc\/WPNpAIWbwNxsYOWOSD9WZwx5\/UHEn5LR7ZlCVlY076ieYnkeUgZkvqLGf9uTwB9geI3l8xHRiu2hQIaMjFOo1OQbyFzmaWujx9MAhkY6qi3kChtdTbR9m4RnsYQFnpeXClFcs+QT59aspSzql8FbOScjDG7W0zaY3ZgkJXN7BfY4SIJPUBeRUMOAX4MLZmQkuvgXR9xl0+hDMhfCdb4lYQstCz9IFY21zLZcX9DnRPKoihjeiKL6aGTmpYSqqOlGYv5Zx5i\/xCFBECgk04SQfvCAMF0xxWf7mMv51fgIryw06JiqP4DUW2pUqZ9RjbqybPuqOpLE3k5Xw8GHfWKb8okxh4SloMZ1pYYNmUBYd2dgCHNoQWr7xVw030E57PBitnvQgnvas6sRvN3dCCao3lBlLKgFjJrtV3VaFK49PyT8x8zDLY3Y7K8OXjTaSLYt4QovA57gp+aOfKdHbUgPyMRGe2XDjhHBTAdn4LbkRyK73SgY0TvaHhxVz5lRhA4c3WfxpP479Bj\/O0rtH840tI2iai4h4lb4zNgO4Xp9iR3KSj6xYambfigixplOgoZEI37\/B0xzwXbN8qwCLv8ue7pOJNnv871ApTNW7dB22hmZ3nQVrexnA7f1tTuXgRJ0jybcqyX+O6uDK\/fSN9BEr4CezVNUi1bLDgr7oszMhY33hmY+JyCMd5gRBeM6s1M4n9h3rPbVf\/t9LOvWknjlFyUAUcqn6iN\/26zrFAaDJrJgmoTAcSvS224aGOfGnWZ4rt54fyyiIkexQZQGToE\/KuljxETqRaj5i+AhHmlcPefwsyvODPJ\/GuYBFhxxgc8er6vWwf2geS6vP3E23fgMJqboAIWm4+doe7Xc8TVGvr5bIUL47hvqhIG6PV+IAn83bCF3M1BCnHoJk9spiGBxH4KZNgtrnxdFdLAzSIPhOZCZze+E+oSIJbDF\/13ZD8D0L9vpnYL8FaAz7J4vGPuS8Qyg2TfR23jwew7n7w5E3SxFFc2f9wTd11w2TE2X8dxMWuL5SkPu+iAtNDpJFNYsjNI9NvkzoOcMEbyACvSnxiuri\/rDtQBz5CWtXX3\/vdTn8TcWM0pqs6hmjR7syMtOohGju5ggZICBGoPZc5f+KGTW3eTn8NL58oNvpxjIH6\/Xlssp9cSERlPmfIz5WfYaKGxwisS3KGr\/ZQW1l6EB9yKQFZTLy4IO\/lXijYrondAr+QrFxXB4ZYtwzZ\/S7mDq2AmaNQ9+qlHqcTARlnLak1I5xXLjpTukEBS6LrRGkVXljQhrNCn5RKKfynYwFOuJkIcrA43Z7h3vkv+kz\/aEYzlrWiK1Ao\/TkLl\/wX\/R7bUMUpa\/r3x2JGie9PTK+f6mQLKXCAx4kto74a5bWKK9AG7GMAUNwJ3gU9ufzAgj4vGvWzKR4t9m0ZLaJJ5QbfuxGnyIGX61U9yzgoq9WK6vxMNUSMwGlHx9dB5UMn+7PYVB3uyh1OoNzSeTzx7Ghz6MnZWasK7K0RHxdoj85LCF35Vf9nZV3ojsiBdCV6kZx1NH+x2bCquyny4g92isC3owITOEYZCtGfw7+r2J\/fWjLKvO68Cl75MNuD7Rjc1RijwhRaonrC0Kf+kJ2tK9zdYyXz+Gi0WsVxn+QmTpcPhr9SbKTslHS1hqqSFPTInNdxrSJYFikoErCF4bhVCVocD4f2mpOThcKIngHhcfxo6laievE7wpF7KWpaaoebj\/0TggwLv\/Km+DuPObjjsfbbWpS\/IQXB0Iu7SKDQrEHj5GV\/C8q0eqdlNJN4hiZQb8XNZnF7yC36faM2nZDknR\/FxXgt7QAlKPz\/+MOfGlZ\/7IWENmGb30ciOcfUG5q6lbsSKbZIupjuahu3sSP8c\/1YYjfcvR1gUa2AwRRLk+PYZ5AILPq\/9R+jj491h\/B1THMrdVtHqmK3\/4Cga2Q3GonggTdnqlg4ypriABUktCslI6YDjaG0ED3CSa3lu1hS6Mq0YkwJRq1fH9mNl0D5TqGE0YHErIi1\/sf6z3FR54c4FmWOWpqCHGuoePPg0XfCBdv5rzRIaUF05wCPJ1CjltpXseIxNY0kaIx0diTW\/VHVeDar2gWnx3EviNHiHM8L5iAIq7eyKhYlGpS2GOSZmh6fPyeRSoGxtOB2wXU9QiH9w3tlBnbpLhDD5h76cIzphGn8Lmx1Dy+SvhfkviPsF08GG85tx+rGZUNpI8jWlCcuIIE0NZsV3CDX3jXZDKCV45wi7rrT7W39Fn+XvZa5g3ixlRHUVOf1evtQ9ThAovLA3fGz4mj959gDTHKykXro4bKoRQ1HIuos0SczwzPyfsU8g79Pyny6m107qz69LRDRtyMBiplylyIFOV+YcVAtbmURKyLgXcqP1YLXXm1UFXUVVeuhrvn34OccGkZMab68mLD6hFgiaNHb0jXDswTVLUatREVcZaMPTlNd\/ctPmcyV7BaWCFpQ9y24aGmA1NrCZutjyA73pG\/mYyWeDwNpgMgmKHfqJbdOlO37uXdZDtWtTWFb9UNgwBhi8OknO1YyS\/ZXbAtIcmOQM7SPAHTRX7DOIr8WT63YHfrkgS1HQweZ7u\/d6yHDv6F8dN1eFZ2HXB8e7o8JgwYc2jMt4TlNYeSqX02uhmsA8hnzuWKqTnIMUh8eASRhgrX5lRwntYKYmBJG8JAy5lUaaJ8fdxFLiVs19H6PtDLC3rLYkdnj97k9QIkG9rXe0T1suvYzEft0Vby\/SxZjZRi9s86i1OtO98zF9ArYePpsgtTlKwa5Q4lc4YkZ6NNyC8meDnjXXgDgPEtBay0YpW6nbUPV0EvnciF89mI4cQsTBvByS4GJxb0iX7Pz2KT2M0Qgr9f3gp3h3pzHgIUmzsKBeOwM1cZdHIyJuQSDrGJ9+POHT4w4xiBHhAOWgv7rwWAjK0i0UA2MO8GIMTH7jCD1RDFQZ5CtptbX9WiMTOUNsBxBm0chtIIWRz5Qc\/Hm1tcrDMwQHckb6+MR6CHWgZS4ASht371SMugVPP\/tqfRe0xmCDM6wSgYVplINyLLipbu0gFItpvnw\/cmfg8OskxUl1NnXXa2qioxFW7DTAqZSGPGbdRGp98rE6IRaGjljG0mGEj+0gjU7qRqZlpDunYx63gM9CQ\/EiN\/FsoutBUfHqEGAt2M6mUxzyXChEd7o5ocyAWmUdO2c8F+TRGob2\/SiLtqNlijy4FYbStz\/UMlj3x4YLAfdp2fwl0jIKuQO4hLf+icINWIQTV9ptNSOucuB3nmc+MgBmsgAsmKZVJwePZ1YCKfw8fUk6b\/Ly\/2bKMENV1Gaf\/x1kwFor2oNQgWUE9mF8Ofc9e0jvNBr5oZMdfe0mUP5oN9WavyG5ulq1PopvFoT743w0i5BPOCHVeUbNJRX2qd\/8CwJMwirUXaiq6NJUdD9mUQgp7P35D1xR28i+Rh5AZTArUTz8NvTxJfJ+BObiKm376ShfDh4PASSif9+vMIyKHNli+y2S4LOn8n7B7PQcStSO2J6ydhQBzvOPZO93a47MSBm2j+h+Ew1TqB\/tHHWSMVP9vpZ3toiY+xiPwD+YQsmaOLrkOr6WDgoZT6Q5fDesXikYbHKm1XTjNS\/zpg+yLXVSf1dI5hHYGhCn0OwvF7XoaAuwkh9sx2psgUfkXO\/aM7CpzeW5kwgkZ5kWvlu3rtV2snqaS8G\/Q1b5K3u+bzptrOtn3bNvY1vebvijKH1nZj3e3Y+xHTA7aoJAud84Lxje1aVhnwaDff8eJOjg4ZCZDRURQJeAzFpMkSbdXgarCJ6wtPRn7iB\/lVKV\/cYeoVSUTsz0Ot1J7MWCa766ozS\/Fh0Nji7ktnojlM+Hga4airNnAMFb4nS0kTDVS8Ffu6CgX0t+a6EVD3GSlqmnNeBy\/uxKG92ts2qDQf\/P3B5KapeiOdVMpvtaNr1bMHC7mm+Cw3\/8to4z0iUGUTht8s\/OMVmm5s20vrTxnmXkmIF9Rk9Y6dFDGE3V5gDkFLFDyxJg83QACshExVm9vzOgRLGuvC9YRlXXPEyF7qPsy\/nhuqZ+P5yhU0I5LWaEqB06393YCbVRks5wx8CGcfLxj3K3fCeJnUeJynZUDZUMUTMntce0ov985ZtmnwvWAp1Qy7kyfSctdG23BZM98Hza88rUtEorsmz7cfGtWHr6mkzJghDsq70Zcc0swCKW\/nUB7cud3PDE53ACYLojdirNxZFpk4YR8ZE3gWhZUkO1tnqtFGrjK9QObLbeNxjMSuk+JI6caQow1iRQzeOXxised3vwxpXVjJIereIKUrYF1MQx\/MJqDU94rmy6gGe\/sv\/QM2ogg7FesVqHKeqD\/0GK3g2FUuxuW\/SlCdGbZYIJsB96P3Wff\/dN5yoy2NosaCIiBFRqc40lvEg4QQ1vmDavsqBhUMWXl0vPC5kgbQ5W5fIL68YnksOe3XEhf\/G8vLDKnlv05rSc4Q2+SIBwZ1+bgrKllRg9jxkb6RCtHAq7Sm8OrFMnVpmvg4466OY+6+KtAi+omd0Kmjw5tCSKzmH6PQE8qbt2sZrK7mjhexCyuenCjmIwYZC1G\/tY5m85XvWKUha67i8qW5Qt7lrsosxcfMob3D8j4FkWjGDSgfMhyeFXL9GGOI9N2QQzXTm30RnP3QEyPAIFNokIBMZe689BBrwxyRnMqHlTJOsAkTPQz2QG1sGj\/JvspQBGc2glTV5n60\/Mx48gcIk3KbAcTVIC7d7PvHY0\/DGW5nCJ0jAQfdWyFhz49NvSPy8KpaPHOD+KdBeBW+BTAvheJpB93OiIQaKeNMOJt\/Juo0vSKyTui6CQVn6fM9XcnZgGLapXqtPqMId3KxwfSSqxMKuaEiFLQagiOVhtlMDXtZ2+WEVG7E3KVqyCG95jM8h+YOaazR3wKo4fozv1IkpdWe3qO9VkQYERCoffWKGcqsJpNiLJwkN4Zyyn4HEzCp3l2F1SSp39w1hGlgge\/wsHUP88Iun+SnPjaEKaNN5qpykpXSZy1d1P9lY2r\/A6KqHcV\/7+\/pgxBWJ7eOaMmqstisQ9Qm54i9H3Kwsi\/eR\/zyQ\/wEPITes+nk0KFS+SBCA7zKOTOVUJOEcoBtNO+JBbNGntFvkCYQfRX0MvhknBHt9IVBwiO52gH27hs4Y1MgKy6+CEUOjTrLiK6hlt7YTdEh3pAVfO5cMhIOlvouwA1QIdwO3HeiJLj736ZzMl0ePNPp6Tfbl2Jch5G5CkABZ6Qi4IlhEG\/mwcrWmjK2pGtxiS\/KBZhsIMxCaHpP2+3kwoMwqSwEAphp5a+R+8B4H7QTDlK8SQ27j1tQpuVfF\/1r4yGklphgNbmfYDStaup25ODmdcdI2frDqsKhsx8fnGkA2ZCaaq1DeqLQ5Od7Xf+TP0yrtxs\/PZNvV7MBfiD5pk1so5K1YTAQ80qZsXRc9XnTQeDj8bRXL4lMGx9k+pmgen7K1\/Qu285nRskxpFSnmPJL8Sk2csn6SrBuIO33flp1u4LY5xde8dnxIeQ2t+OYEhVLCx8rv6Wo63APEzcvLsJGtQv6Kl0k2N0KBawkfcMmrJBsg0sB6obDU1Z1bKPHSIuX9IwI+lQnbwEKe8N4VVep9dD1r5Rd+OPRpnu\/RQNLCUR8OxcJsEnNJMqNBt1ddYTubpNS0ednkWDbeNqAfkAmqGj9121tkYGZXsH5+bxHYwkdrFPAfbF30lssjfSSL11jbocVzNMjntZ3wu0zLhcJtTztGeW8diYvu1eNBlCYJ4yl8ZanvPpMSHwJ8x5gzWcF8NkuBGHMNAgo22fR9Vg9YTq0HvkMRFvop\/eXQwThhs+pFsymrvrTj6V3ZyEaUNZEl4o8VhKhffLTMXUQkxMbchInHRy\/Ou1glKTO5YnlyYoS+ueDiHIV\/KdFUBhwrXoZ58wtlGse420BwCmgm8dO82nSEGQxlvZK1ZbXxRtIzMTR+hpXuQP1eTL30O0JdfgFLfcdK4Llz2r6ZAScs=';
    //     $publickey = "MIICXQIBAAKBgQC2JvNSGNYYLJqO7Hz8EKXfIrSFsXK6dIi3aUxNdaWCDp0f6Spz\nRW55JqRpa27KjtIxPJXTBj6hbWng5IVhKI7PfkWZOTEDZZMaiZjrsye9uzUhO1YA\n2jj2rGPpeytt3SGtXV5GaFXYrJ70/f98QVx92upg0HpkUAya664Jj9PjVQIDAQAB\nAoGAQe39OiTlMSDL3Jl6b53y+73LC2z78sMFTSWeyZagjl+NvaQeilSCNPWoosOQ\n+V4SdGHSdOwYtUMuBImSQWV1ssZTppQPx3EFfpMxUdzJLvXlDZGYOjspCAYKU4XR\nr1ac8pEGQLYXBVomGVbR0v/YRV0EZQzm4t7rgxGp9tGdBcECQQDaXzhB4zyak7k9\nXVEnfhnh1UDEGKnxhEsYzTkoEP7lknc2igqUiExDR7hpg3ta9hc79Au85cdNty/y\n+XNvI48lAkEA1YoCj0ONC+QtbyhDe/3zaypJ9m1P9yoTZV97IPBwihpEILY8BJYS\nevAXTg2fpRrPgaoKxP4x9A0yc4T6J1akcQJBAMNtcfBtR8BiseW8DLPWQ5169vJH\nzFcrePWiPCOiSiv0DyJNGbjh3bZciipLk+rMz/BEsPiFfv8LEStWmTr+TM0CQBHn\ng3VtrYrcs+6JCrd/wIQwxIjT+4t2zK+IRPOrFVSPBT1U6k1cI+qI7PtPax5V1CZE\nEqkXwyp6XMuQz8SyoBECQQCUY8u4AMIzR/Do7yrXyxvVImh/Hts4dUkTCubSDk6c\nHsa/aWymETeKzEjrp7XRPZUppwRqh/47+wcdmAUu5Oy2";

    //     $data = 'cidps+04KaFfuWBIVuHtfpsV\/\/\/Nl8Fx6wUo2MVITKhdStP\/6LRZB7GMWjQ\/Oei4g4lp1dquLFEYGNNgZ7Ki38nrQYR9GgDa199qzpE9P7ruABjbMyBZfNFcSUgXJzolhls7PAuFRVtpndDXIyfMoIN7asGj4H85Ygt153thBcg=';
    //     $v = $this->sign($data, $publickey);
    //     $arr['sec_contents'] = $commonModel->aes_decrypt($c, '0000000000881739', '2020425hdongkeji');
    //     // $arr['sec_contentss'] = base64_decode($c);
    //     $this->returnSuccess($arr);
    // }

    // public  function sign($dataStr, $privateKey)
    // {

    //     $privateKey = $this->redPukey($privateKey);

    //     $crypto = '';

    //     foreach (str_split(base64_decode($dataStr), 128) as $chunk) {

    //         openssl_private_decrypt($chunk, $decryptData, $privateKey, OPENSSL_PKCS1_PADDING);

    //         $crypto .= $decryptData;
    //     }

    //     return $crypto;
    // }
    // function  redPukey($pubKey)
    // {


    //     // $pem = chunk_split($pubKey, 64, "\n"); //转换为pem格式的公钥

    //     $pem = "-----BEGIN RSA PRIVATE KEY-----\n" . $pubKey . "\n-----END RSA PRIVATE KEY-----\n";

    //     $publicKey = openssl_pkey_get_private($pem);

    //     return $publicKey;
    // }

    // 获取小说内容
    public function control_get_wap_content()
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
            //M('census', 'im')->uprackreadtime($this->get_userid(), $book_id, 1, $section_id); //更新书架记录
            $data = T($tpsec)->field('section_id,title,book_id,update_time,isfree,secnum,list_order,coin')->where($where)->where(['status' => 1])->where(['isdelete' => 0])->find();
            if (!$data) {
                Out::jerror('小说或章节不存在', null, '100154');
            }
            $content = T($tpsecc)->field('sec_content,sec_content_id')->where(['section_id' => $data['section_id']])->where(['isdelete' => 0])->find();
            // 引入aes加密
            if (!$content) {
                Out::jerror('章节不存在或删除', null, '100153');
            }
            $coin = $data['coin'];
            $arr = $data;

            $arr['update_time'] = strtotime($data['update_time']);
            $arr['sec_content_id'] = $content['sec_content_id'];

            // $arr['sec_content'] = (htmlspecialchars_decode($content['sec_content'], ENT_QUOTES));
            // $arr['sec_content'] = str_replace('</p>', "\n\n\r ", $arr['sec_content']);
            // $arr['sec_content'] = str_replace('<br />', "\n\n\r", $arr['sec_content']);
            // $arr['sec_content'] = preg_replace('#<[^>]+>#', ' ', $arr['sec_content']);
            // $arr['sec_content'] = str_replace("<", "   ", $arr['sec_content']);
            // $arr['sec_content'] = str_replace(">", "   ", $arr['sec_content']);
            // $arr['sec_content'] = str_replace("&nbsp;", "   ", $arr['sec_content']);
            // $arr['sec_content'] = str_replace("&#39;", "'", $arr['sec_content']);
            // $arr['sec_content'] = str_replace("&quot;", "\"", $arr['sec_content']);
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
        $commonModel->user_read_history($this->get_userid(), $book_id, "");
        M('bookcensus', 'im')->sceread($this->get_userid(), 1, $get['book_id'], $get['section_id']);
        if ($this->get_userid()) {
            $arr['ispay'] = T('expend')->set_field('users_id')->where(['users_id' => $this->get_userid(), 'expend_type' => 1, 'book_id' => $book_id, 'section_id' => $section_id])->get_one() ? 1 : 0;
        }

        if (!$arr['coin'] <= 0 && $arr['isfree'] != 0) {
            $arr['coin'] = M('coin', 'im')->bookcalculate($arr['secnum'], 0.6);
        }

        $this->returnSuccess($arr);
    }
    public function control_get_wapcontent()
    {
        $get = get(['int' => ['book_id' => 1, 'section_id' => 1]]);
        $book_id = $get['book_id'];
        $lang = T('book')->set_where(['book_id' => $book_id,])->set_field('lang')->get_one();
        $tpsec = M('book', 'im')->gettpsec(1, $lang['lang']);
        $tpsecc = M('book', 'im')->gettpseccontent(1, $lang['lang']);
        $section_id = $get['section_id'];
        // $device_type = $this->head['devicetype'];
        // $users_id = $this->uid;
        $where = [
            'book_id' => $book_id,
            'section_id' => $section_id,
        ];

        // $commonModel = M('book', 'im');
        // $commonModel->user_read_history($users_id, $book_id, "");
        // M('census', 'im')->uprackreadtime($this->get_userid(), $book_id, 1, $section_id); //更新书架记录
        $data = T($tpsec)->field('section_id,title,book_id,update_time,isfree,secnum,list_order')->where($where)->where(['status' => 1])->where(['isdelete' => 0])->find();
        if (!$data) {
            Out::jerror('小说或章节不存在', null, '100154');
        }
        if ($data['isfree'] != 0) {
            Out::jerror('收费章节，请下载app阅读', null, '104154');
        }
        $content = T($tpsecc)->field('sec_content,sec_content_id')->where(['section_id' => $data['section_id']])->where(['isdelete' => 0])->find();
        // 引入aes加密
        if (!$content) {
            Out::jerror('章节不存在或删除', null, '100153');
        }

        $w = ['book_id' => $get['book_id']];
        // $money = T('book')->field('money')->where($w)->find();
        // if ($data['isfree'] == 1) {
        //     $coin = M('coin', 'im')->bookcalculate($data['secnum'], $money['money']);


        // } elseif ($data['isfree'] == 4) {
        //     $coin = M('coin', 'im')->bookcalculate($data['secnum'], $money['money']);

        // } else {
        //     $coin = 0.0;
        // }
        $boj = M('book', 'im');
        $arr = array(
            'section_id' => $data['section_id'],
            'title' => $data['title'],
            'isfree' => $data['isfree'],
            // 'ispay' => T('expend')->where(['users_id' => $users_id, 'expend_type' => 1, 'book_id' => $book_id, 'section_id' => $section_id])->get_one() ? 1 : 0,
            // 'isvip' => M('user', 'im')->checkvip($this->uid),
            'ispay' => 0,
            'book_id' => $data['book_id'],
            'update_time' => strtotime($data['update_time']),
            'sec_content_id' => $content['sec_content_id'],
            'sec_content' => $boj->nl2p($boj->trimhtml($content['sec_content'])),
            // 'sec_content' => htmlspecialchars_decode($content['sec_content']),
        );

        // $arr['sec_content'] = (htmlspecialchars_decode($arr['sec_content']));
        // $arr['sec_content'] = preg_replace('/[\n,\r\n]+/', '<p/>', $arr['sec_content']);
        // $arr['sec_content'] = preg_replace('/\s+/', '&nbsp;', $arr['sec_content']);      
        // $arr['sec_content'] = preg_replace("#[\x{04}-\x{15}]#u", "", $arr['sec_content']);
        // d($arr['sec_content']);
        // M('census', 'im')->freereadcounts($book_id);
        M('bookcensus', 'im')->sceread($this->get_userid(), 1, $get['book_id'], $get['section_id']);
        $down = T($tpsec)->set_where(['book_id' => $get['book_id'], 'isdelete' => 0, 'status' => 1])->set_where('list_order<' . $data['list_order'])->set_field('section_id')->order_by(['f' => 'list_order', 's' => 'down'])->get_one();
        $up = T($tpsec)->set_where(['book_id' => $get['book_id'], 'isdelete' => 0, 'status' => 1])->set_where('list_order>' . $data['list_order'])->set_field('section_id')->order_by(['f' => 'list_order', 's' => 'up'])->get_one();
        $arr['next'] = $up['section_id'] ? $up['section_id'] : '0';
        $arr['pre'] = $down['section_id'] ? $down['section_id'] : '0';
        // $arr['coin'] = $coin;
        $this->returnSuccess($arr);
    }
    //随机获取书籍
    public function control_get_randList()
    {
        M('version', 'im')->cheknew($this->head['version']);
        // $nums = get(['int' => ['num']]);
        // $nums = $nums['num'] ? $nums['num'] : 8;
        // $cityid = $this->head['cityid'];
        // $list = T('book')->set_global_where(['status' => 1, 'lang' => $cityid])->field('book_id,other_name,`desc`,bpic,writer_name,isfree,update_status')->set_limit($nums)->order_by('RAND()')->get_all();
        $size = 500;
        $cityid = $this->head['cityid'];
        $index = "bookrand_" . $cityid;
        $cache = Y::$cache->get($index);
        $lists = [];
        if ($cache[0]) {
            $lists = $cache[1];
        } else {
            $lists = $cache[1];
            $liststmp = T('book')->set_global_where(['status' => 1, 'lang' => $cityid])->field('book_id')->set_limit($size)->order_by('section desc')->get_all();
            $lists = array_column($liststmp, 'book_id');
        }

        $id = array_rand($lists, 8);
        $ids = [];
        for ($i = 0; $i < sizeof($id); $i++) {

            array_push($ids, $lists[$id[$i]]);
        }
        $list = T('book')->set_global_where(['status' => 1, 'lang' => $cityid])->field('book_id,other_name,`desc`,bpic_dsl,bpic,writer_name,isfree,update_status,1 as type')
            ->wherein('book_id', $ids)
            ->get_all();
        Out::jout($list);
    }
    public function control_mostlike()
    {
        M('version', 'im')->cheknew($this->head['version']);
        $nums = get(['int' => ['num', 'type' => 1]]);
        $type = $nums['type'];
        $nums = $nums['num'] ? $nums['num'] : 8;
        $cityid = $this->head['cityid'];
        if ($type == 1) {

            // $list = T('book')->set_global_where(['status' => 1, 'lang' => $cityid])->field('book_id,other_name,`desc`,bpic_dsl,bpic,writer_name,isfree,update_status')->set_limit($nums)->order_by('RAND()')->get_all();
            $list=M('bookrandom','im')->getbook($this->head['cityid'], $nums);
        } else {
            //取漫画

            // $index = 'cartoonminmax' . $cityid;
            // $cache = Y::$cache->get($index);
            // if ($cache[0]) {
            //     $array = $cache[1];
            // } else {
            //     $max = T('cartoon')->field('cartoon_id')->order_by('cartoon_id desc')->get_one();

            //     $min = T('cartoon')->field('cartoon_id')->order_by('cartoon_id Asc')->get_one();
            //     $array = array($min['cartoon_id'], $max['cartoon_id']);
            //     Y::$cache->set($index, $array, G_DAY);
            // }

            // $randon = rand($array[0], $array[1]);
            // if ($randon > $array[1] - $nums) {
            //     $w = 'cartoon_id<=' . $randon;
            // } else {
            //     $w = 'cartoon_id>=' . $randon;
            // }

      
            // $list = T('cartoon')->set_global_where(['status' => 1, 'lang' => $cityid])->field('cartoon_id as book_id,bpic_dsl,other_name,`desc`,bpic,writer_name,isfree,update_status,2 as type')->set_limit($nums)->order_by('RAND()')->get_all();
            $list=M('bookrandom','im')->getcartoon($this->head['cityid'], $nums);
        }
        Out::jout($list);
    }
    // 获取热门推荐小说
    public function control_get_new_book()
    {
        M('version', 'im')->cheknew($this->head['version']);
        $where['status'] = 1;
        $where['groom_type'] = 3;
        $sityid = $this->head['cityid'];
        $index = 'get_new_book2' . $sityid;
        $cache = Y::$cache->get($index);
        if ($cache[0]) {
            $data = $cache[1];
        } else {

            // $data = T('book')->set_field('book_id,other_name,bpic,1 as type,bpic_dsl,writer_name,book_id,hits as recommend_num')->set_where(['status' => 1, 'lang' => 0, 'lang' => $sityid])->limit(80)->order_by('section desc,hits desc')->get_all();
            //排名前50的随机
            $nums=50;
            $data=M('bookrandom','im')->getbook($this->head['cityid'], $nums);
            foreach ($data as $key => $value) {
                # code...
                $data[$key]['recommend_num']=rand(10000,99999)."";
            }
            if (is_array($data) && sizeof($data)) {
                Y::$cache->set($index, $data, 86400);
            }
            // Y::$cache->set($index, $data, 86400);


        }
        // $data2 = array_rand($data, 8);
        // $datatmp = [];
        // foreach ($data2 as $ss) {
        //     // $datatmp.push();

        //     array_push($datatmp, $data[$ss]);
        // }

        $this->returnSuccess($data);
    }
    // 获取幻灯片图片
    public function control_get_banner()
    {
        M('version', 'im')->cheknew($this->head['version']);
        $where1['status'] = '1';
        // $where1['scan_seat'] = '1';
        // $where = 'goal_type !=3';
        $plat = $this->head['devicetype'];
        $cityid = $this->head['cityid'];
        $where1['cityid'] = $cityid;
        if ($plat == 'iphone') {
            $where3 = "plat!='android'";
        } elseif ($plat == 'android') {
            $where3 = "plat!='ios'";
        }
        $index = 'get_banner' . $cityid;
        $cache = Y::$cache->get($index);
        if ($cache[0]) {
            $this->returnSuccess($cache[1]);
        } else {
            $data = T('banner')
                ->field('scan_seat,goal_type,goal_window,book_id,banner_pic,banner_url,cartoon_id')
                ->where($where1)
                ->where($where3)
                ->order('list_order asc')
                ->get_all();
            if (is_array($data) && sizeof($data)) {
                Y::$cache->set($index, $data, 86400);
            }

            $this->returnSuccess($data);
        }
    }
    public function control_new()
    {
        M('version', 'im')->cheknew($this->head['version']);
        $data = get(['string' => ['page']]);
        $where['status'] = 1;

        $cityid = $this->head['cityid'];
        $where['lang'] = $cityid;
        $index = 'get_new1_' . $data['page'] . '_' . $cityid;
        $cache = Y::$cache->get($index);
        if ($cache[0]) {
            $this->returnSuccess($cache[1]);
        } else {
            $list = T('book')
                ->field('book_id,other_name,`desc`,bpic_dsl,bpic,reward_icon,writer_name,virtual_coin,is_virtual,isfree,update_status')
                ->where($where)
                // ->wherelike('other_name', $other_name['other_name'])
                ->order('update_time desc')
                ->set_limit([$data['page'], 10]);

            $arr = $list->get_all();
            foreach ($arr as $key => $value) {
                $arr[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
                $data[$key]['recommend_num']=rand(10000,99999)."";
            }
            if (is_array($arr) && sizeof($arr)) {
                Y::$cache->set($index, $arr, 86400);
            }

            $this->returnSuccess($arr);
        }


        // $this->returnSuccess($arr);
    }
    public function control_newbook()
    {
        M('version', 'im')->cheknew($this->head['version']);
        $data = get(['string' => ['page']]);
        $where['status'] = 1;
        $cityid = $this->head['cityid'];
        $where['lang'] = $cityid;
        $list = T('book')
            ->field('book_id,other_name,`desc`,bpic_dsl,bpic,reward_icon,writer_name,virtual_coin,is_virtual,isfree,update_status')
            ->where($where)
            // ->wherelike('other_name', $other_name['other_name'])
            ->order('book_id desc')
            ->set_limit([$data['page'], 10]);

        $arr = $list->get_all();
        foreach ($arr as $key => $value) {
            $arr[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
            $data[$key]['recommend_num']=rand(10000,99999)."";
        }
        $this->returnSuccess($arr);
    }
    public function control_free()
    {
        M('version', 'im')->cheknew($this->head['version']);
        $data = get(['string' => ['page']]);
        $where['status'] = 1;
        $where['isfree'] = 0;
        $cityid = $this->head['cityid'];
        $where['lang'] = $cityid;
        $list = T('book')
            ->field('book_id,other_name,`desc`,bpic,reward_icon,bpic_dsl,writer_name,virtual_coin,is_virtual,isfree,update_status')
            ->where($where)
            // ->wherelike('other_name', $other_name['other_name'])
            ->order('book_id desc')
            ->set_limit([$data['page'], 10]);

        $arr = $list->get_all();
        foreach ($arr as $key => $value) {
            $arr[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
        }
        $this->returnSuccess($arr);
    }
    public function control_end()
    {
        M('version', 'im')->cheknew($this->head['version']);
        $data = get(['string' => ['page']]);
        $where['status'] = 1;
        $where['update_status'] = 1;
        $cityid = $this->head['cityid'];
        $where['lang'] = $cityid;
        $list = T('book')
            ->field('book_id,other_name,`desc`,bpic,reward_icon,bpic_dsl,writer_name,virtual_coin,is_virtual,isfree,update_status')
            ->where($where)
            // ->wherelike('other_name', $other_name['other_name'])
            ->order('book_id desc')
            ->set_limit([$data['page'], 10]);

        $arr = $list->get_all();
        foreach ($arr as $key => $value) {
            $arr[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
        }
        $this->returnSuccess($arr);
    }
}
