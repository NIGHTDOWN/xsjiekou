<?php

namespace ng169\control\api;

use ng169\control\apibase;
use ng169\tool\Out;
use ng169\Y;

checktop();

class user extends apibase
{
    protected $noNeedLogin = ['get_invite', 'add_invite'];
    //用户评论

    public function control_discuss()
    {
        $data = get(['string' => ['book_id', 'star' => 1, 'content' => 1, 'cartoon_id']]);
        $data['users_id'] = $this->get_userid();
        // $data['plat'] = $this->head['devicetype'];
        // $arr = M('user', 'im')->add_discuss($data);
        if ($data['book_id']) {
            $booktype = 1;
            $bookid = $data['book_id'];
        } else {
            $booktype = 2;
            $bookid = $data['cartoon_id'];
        }
        $content = $data['content'];
        $star = $data['star'];
        $arr = M('user', 'im')->add_discuss($this->get_userid(), $booktype, $bookid, $content, $star, getdevicetype($this->head));
        if ($arr) {
            Out::jout('评论成功');
        } else {
            Out::jerror('评论失败', null, '100155');
        }
    }
    //用户打赏

    public function control_reward()
    {
        // $data = get(['int' => ['book_id' => 1, 'reward_price' => 1, 'type' => 1]]);

        // $user = parent::$wrap_user;
        // if ($user['remainder'] < $data['reward_price']) {
        //     Out::jerror('余额不足', null, '100111');
        // } else {
        //     M('coin', 'im')->change($this->get_userid(), -$data['reward_price']);
        //     $res['reward_price'] = $data['reward_price'];
        //     $res['users_id'] = $this->get_userid();
        //     $res['nick_name'] = $user['nickname'];
        //     $res['avater'] = $user['avater'];
        //     $res['reward_time'] = date('Y-m-d H:i:s', time());
        //     if ($data['type'] == '1') {
        //         $w = ['book_id' => $data['book_id']];
        //         $book = T('book')->field('other_name')->where($w)->get_one();
        //         $res['book_id'] = $data['book_id'];
        //         $res['book_name'] = $book['other_name'];
        //         // 执行打赏记录
        //         T('reward')->add($res);

        //         $book_other = T('book_other')->field('reward_icon,virtual_coin')->where($w)->get_one();
        //         $reward_icon['reward_icon'] = $book_other['reward_icon'] + $data['reward_price'];
        //         $reward_icon['virtual_coin'] = $book_other['virtual_coin'] + $data['reward_price'];
        //         // 更新书籍打赏记录
        //         T('book_other')->update($reward_icon, $w);

        //         $virtual_reward = T('virtual_reward')->set_field('avater')->where($w)->get_one();
        //         if (!($virtual_reward)) {
        //             $virtual = json_decode($virtual_reward['avater']);
        //             $virtuals = (array) $virtual;
        //             $count = count($virtuals['avater']);
        //             if ($count > 30) {
        //                 array_splice($virtuals['avater'], 0, 15);
        //             }
        //             array_push($virtuals['avater'], ['url' => $res['avater'], 'name' => '0']);
        //             $virtualsd['avater'] = json_encode($virtuals);
        //             T('virtual_reward')->update($virtualsd, $w);
        //         } else {
        //             $virtuals['avater'] = [];
        //             array_push($virtuals['avater'], ['url' => $res['avater'], 'name' => '0']);
        //             $virtualsd['avater'] = json_encode($virtuals);
        //             $virtualsd['book_id'] = $data['book_id'];
        //             T('virtual_reward')->add($virtualsd);
        //         }
        //         // 记录消费情况
        //         $expend['bother_name'] = $book['other_name'];
        //         $expend['nick_name'] = $user['nickname'];
        //         $expend['book_id'] = $data['book_id'];
        //         $expend['users_id'] = $this->get_userid();
        //         $expend['expend_time'] = date('Y-m-d H:i:s', time());
        //         $expend['expend_red'] = $data['reward_price'];
        //         $expend['expend_type'] = 3;
        //         $expend['plat'] = $this->head('devicetype');
        //         $expend['ispay'] = 1;
        //         $expend['remainder'] = $arr['remainder'];
        //         T('expend')->add($expend);
        //     } else {
        //         $w = ['cartoon_id' => $data['book_id']];
        //         $cartoon = T('cartoon')->set_field('other_name')->where($w)->get_one();
        //         $res['cartoon_id'] = $data['book_id'];
        //         $res['cartoon_name'] = $cartoon['other_name'];
        //         // 执行打赏
        //         T('reward')->add($res);
        //         $cartoon_other = T('cartoon_other')->set_field('reward_icon,virtual_coin')->where($w)->get_one();
        //         $reward_icon['reward_icon'] = $cartoon_other['reward_icon'] + $data['reward_price'];
        //         $reward_icon['virtual_coin'] = $cartoon_other['virtual_coin'] + $data['reward_price'];
        //         // 更新漫画打赏数
        //         T('cartoon_other')->update($reward_icon, $w);

        //         $virtual_reward = T('virtual_reward')->set_field('avater')->where($w)->get_one();
        //         if (!($virtual_reward)) {
        //             $virtual = json_decode($virtual_reward['avater']);
        //             $virtuals = (array) $virtual;
        //             $count = count($virtuals['avater']);
        //             if ($count > 30) {
        //                 array_splice($virtuals['avater'], 0, 15);
        //             }
        //             array_push($virtuals['avater'], ['url' => $res['avater'], 'name' => '0']);
        //             $virtualsd['avater'] = json_encode($virtuals);
        //             T('virtual_reward')->update($virtualsd, $w);
        //         } else {
        //             $virtuals['avater'] = [];
        //             array_push($virtuals['avater'], ['url' => $res['avater'], 'name' => '0']);
        //             $virtualsd['avater'] = json_encode($virtuals);
        //             $virtualsd['cartoon_id'] = $data['book_id'];
        //             T('virtual_reward')->add($virtualsd);
        //         }
        //         // 记录消费情况
        //         $expend['cother_name'] = $cartoon['other_name'];
        //         $expend['nick_name'] = $user['nickname'];
        //         $expend['cartoon_id'] = $data['book_id'];
        //         $expend['users_id'] = $this->users_id;
        //         $expend['expend_time'] = date('Y-m-d H:i:s', time());
        //         $expend['expend_red'] = $data['reward_price'];
        //         $expend['expend_type'] = 3;
        //         $expend['plat'] = $this->head('devicetype');
        //         $expend['ispay'] = 1;
        //         $expend['remainder'] = $arr['remainder'];
        //         T('expend')->add($expend);
        //     }
        //     //书籍统计
        //     M('census', 'im')->aword($get['type'], $get['book_id'], $data['reward_price']);
        //     // 统计打赏数
        //     M('census', 'im')->_aword($data['reward_price']);
        //     // M( 'census', 'im' )->reward_count( $data['reward_price'] );
        //     // M( 'census', 'im' )->ireward_count( $data['reward_price'] );
        //     $user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $this->get_userid()]);

        //     Out::jout($user);
        // }
    }

    public function control_remainder()
    {
        $user = T('third_party_user')->get_one(['id' => parent::$wrap_user['id']]);
        $user['vip_end_time'] = date('m/d/Y', strtotime($user['vip_end_time']));
        Out::jout($user);
    }
    // 用户解锁章节

    public function control_deblocking()
    {

        $data = get(['string' => ['book_id', 'expend_red', 'section_id', 'cartoon_id', 'cart_section_id', 'isauto']]);
        //d($data);
        $user = parent::$wrap_user;
        if ($user['remainder'] < $data['expend_red']) {
            Out::jerror('余额不足', null, '100111');
        }
        if (($data['book_id']) && ($data['section_id']) && ($data['expend_red'])) {
            $bool = M('coin', 'im')->unlocktxt($this->get_userid(1), $data['book_id'], $data['section_id'], $data['expend_red'], $this->head['devicetype'], $data['isauto']);

            // $user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $this->get_userid()]);
            // Out::jout($user);
        } elseif (($data['cartoon_id']) && ($data['cart_section_id']) && ($data['expend_red'])) {
            $bool = M('coin', 'im')->unlockcartoon($this->get_userid(1), $data['cartoon_id'], $data['cart_section_id'], $data['expend_red'], $this->head['devicetype'], $data['isauto']);
        } else {
            Out::jerror('获取参数失败', null, '10001');
        }
        if ($bool) {
            $user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $this->get_userid(1)]);
            Out::jout($user);
        } else {
            Out::jerror('解锁失败', null, '10091');
        }
    }
    // 进入任务栏

    public function control_at_task()
    {
        //$coin = Config::get( 'task' );
        $coin = Y::$newconf['task'];
        $model = M('task', 'im');
        $uid = $this->get_userid();
        $result['share_count'] = $model->share_record($uid);
        $result['advert_count'] = (int) $model->read_advert($uid);
        $result['advert_counts'] = $model->read_advert($uid) >= 5 ? 1 : 0;
        $result['read_count'] = $model->day_read($uid);
        $result['reply_count'] = $model->reply($uid);
        $result['share_coin'] = $coin['share_coin'];
        $result['read_coin'] = $coin['read_coin'];
        $result['answer_coin'] = $coin['answer_coin'];
        $result['advert_coin'] = $coin['advert_coin'];
        $result['question_coin'] = $coin['question_coin'];
        //$result['question_coin'] = $coin['question_coin'];
        $result['reply_count'] = T('reply')->get_one(['users_id' => $uid]) ? 1 : 0;
        Out::jout($result);
    }
    // 获取用户签到的天数

    public function control_get_signday()
    {

        $signs = T('sign')->get_all();
        $usign = T('mall_sign')->where(['users_id' => $this->get_userid(), 'date' => date('Ym')])->order_by(['f' => 'signday', 's' => 'up'])->get_all();
        $sifn = array_column($usign, 'num', 'signday');
        foreach ($signs as $k => $v) {
            $day = $v['sign_day'];
            if (isset($sifn[$day])) {
                $signs[$k]['num'] = $sifn[$day];
                $signs[$k]['issign'] = 1;
            } else {
                $signs[$k]['num'] = 0;
                $signs[$k]['issign'] = 0;
            }
        }
        $bqnum = 5;
        //判断补签次数
        $w3 = ['users_id' => $this->get_userid(), 'bqflag' => 1, 'date' => date('Ym')];
        $bqsignnum = T('mall_sign')->where($w3)->get_count();
        Out::jout(['sgin' => $signs, 'num' => $bqnum - $bqsignnum]);
    }
    // 用户签到
    //补签（补签数目）、连续签到双倍、下一月连续请0
    public function control_sign()
    {
        $uid = $this->get_userid(1);
        $data = get(['int' => ['day']]);
        $coin = M('task', 'im')->sign($uid, $data['day']);

        if ($coin) {
            // $user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $uid]);
            Out::jout($coin);
        } else {
            Out::jerror('签到失败', null, '100122');
        }
    }
    // 新加    每日分享奖励金币

    public function control_add_share()
    {
        return false;
        $book_id = get(['int' => ['book_id' => 1]]);
        $time = date('Y-m-d');
        $uid = $this->get_userid();
        $w = ['users_id' => $this->get_userid()];
        $w['share_date'] = $time;
        $share_coin = Y::$newconf['task'];
        $share_record = T('user_share_record')->field('share_time')->where($w)->order('share_time desc')->get_one();

        $share_time = date('Y-m-d');

        if (!$share_record) {
            $data['users_id'] = $uid;
            $data['share_time'] = date('Y-m-d H:i:s');
            $data['share_date'] = $time;
            $data['book_id'] = $book_id['book_id'];
            $data['share_coin'] = $share_coin['share_coin'];
            T('user_share_record')->add($data);
            M('census', 'im')->task_reward_count($uid, $share_coin['share_coin'], $share_coin['day_share']);
            M('coin', 'im')->change($uid, $share_coin['share_coin']);
        } else {
        }
        M('census', 'im')->_share();
        $user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $this->get_userid()]);
        Out::jout($user);
    }
    //每日阅读奖励金币

    public function control_add_read()
    {
        $where['read_day'] = date('Y-m-d', time());
        $where['users_id'] = $this->get_userid();
        $uid = $this->get_userid();
        $read_coin = Y::$newconf['task'];
        $time = time();
        $read_record = T('user_day_read')->where($where)->get_one();

        if (!$read_record) {
            $data['end_time'] = date('Y-m-d H:i:s', $time);
            $data['start_time'] = date('Y-m-d H:i:s', $time - 1200);
            $data['read_day'] = date('Y-m-d', $time);
            $data['users_id'] = $this->users_id;
            $data['read_coin'] = $read_coin['read_coin'];
            T('user_day_read')->add($data);
            M('census', 'im')->task_reward_count($uid, $read_coin['read_coin'], $read_coin['day_read']);
            //T( 'task_reward_count' )->add( $tcount );
            M('coin', 'im')->change($uid, $read_coin['read_coin']);
            $user = T('third_party_user')->set_field('remainder,golden_bean')->get_one(['id' => $this->get_userid()]);
            Out::jout($user);
        } else {
            Out::jerror('今日已经领取了', null, '100123');
        }
    }
    // 每日看广告送金币

    public function control_add_read_advert()
    {
        $time = time();
        $where['watch_time'] = date('Y-m-d', $time);
        $where['users_id'] = $this->users_id;
        $uid = $this->get_userid();
        $advert_coin = Y::$newconf['task'];
        $record = T('user_read_advert')->where($where)->get_one();
        if (!$record) {
            $data['watch_time'] = $where['watch_time'];
            $data['watch_times'] = date('Y-m-d H:i:s', $time);
            $data['nums'] = 1;
            $data['users_id'] = $uid;
            $data['coin'] = $advert_coin['advert_coin'];

            T('user_read_advert')->add($data);
        } else {
            if (5 <= $record['nums']) {
                // if ( false ) {
                //先去掉看广告限制（林波）
                Out::jerror('次数已经用完', null, '100124');
            } else {

                $data['watch_times'] = date('Y-m-d H:i:s', $time);
                $data['nums'] = $record['nums'] + 1;

                $data['coin'] = $advert_coin['advert_coin'] + $record['coin'];
                T('user_read_advert')->update($data, $where);
            }
        }
        M('coin', 'im')->change($uid, $advert_coin['advert_coin']);
        M('census', 'im')->task_reward_count($uid, $advert_coin['advert_coin'], $advert_coin['day_advert']);
        $result = T('third_party_user')->field('remainder')->where(['id' => $this->get_userid(1)])->get_one();
        $record_count = T('user_read_advert')->field('nums')->where($where)->get_one();
        $result['record_count'] = $record_count['nums'];
        Out::jout($result);
    }
    // 每次邀请统计

    public function control_add_invite()
    {
        // $devicetype = $this->request->header( '' );
        if ($this->get_userid()) {
            M('census', 'im')->_startinvitation($this->get_userid());
        }

        // M( 'census', 'im' )->iyqcount();
        Out::jout('null');
    }

    public function control_getfriend()
    {
        $get = get(['int' => ['id' => 1]]);
        $user = T('third_party_user')->set_field('nickname,id,avater')->get_one($get);
        if (!$user) {
            Out::jerror('用户ID不存在', null, '100156');
        }
        Out::jout($user);
    }
    // 用户浏览记录

    public function control_user_read_history()
    {
        $users_id = $this->users_id;
        $userModel = M('user', 'im');
        $data = $userModel->user_read_history($users_id);
        if ($data) {
            $this->returnSuccess($data);
        } else {
            $data = [];
            $this->returnSuccess($data);
        }
    }
    // 绑定极光推送id

    public function control_jpush_mess()
    {
        $data = get(['string' => ['regsiterion_id' => 1]]);
        $data['users_id'] = $this->users_id;
        $w = ['regsiterion_id' => $data['regsiterion_id']];
        $uw = ['users_id' => $this->users_id];
        if ($data) {
            $arr = T('jpush_mess')->where($w)->get_one();
            if ($arr) {
                // T( 'jpush_mess' )->where( $uw )->del();
                $res = T('jpush_mess')->update($data, $w);
                // if ( $res == 0 ) {
                //     $res = 1;
                // }
            } else {
                T('jpush_mess')->del($uw);
                $res = T('jpush_mess')->add($data);
            }
            $re = [];
            $this->returnSuccess($re);
            // if ( $res ) {

            // } else {
            //     Out::jerror( '绑定失败', null, '100125' );
            // }
        }
    }

    public function control_get_invite()
    {
        $res = T('invite')
            ->field('invite_id,invite_title,invite_icon')
            ->get_all();
        if ($res) {
            $this->returnSuccess($res);
        } else {
            Out::jerror('获取失败', null, '100126');
        }
    }
    // 获取充值记录

    public function control_get_charge_record()
    {
        $page = get(['int' => ['page']]);
        $userModel = M('user', 'im');
        $arr = $userModel->get_charge_record($page['page'], $this->users_id);
        if ($arr) {
            $this->returnSuccess($arr);
        } else {
            $arr = [];
            $this->returnSuccess($arr);
        }
    }

    // 获取个人评论

    public function control_get_owen_discuss()
    {
        // $page = $get(['int' => ['page']]);
        // $arr = T('discuss')
        //     ->field('discuss_id,star,nick_name,discuss_time,content,users_id,cartoonname,bookname')
        //     ->where(['users_id' => $users_id])
        //     ->order('star `desc`,discuss_time desc')
        //     ->set_limit([$page['page'] * 10, 10])->get_all();
        // foreach ($arr as $key => $value) {
        //     if (empty($value['cartoonname'])) {
        //         $arr[$key]['bookname'] = $value['bookname'];
        //     } else {
        //         $arr[$key]['bookname'] = $value['cartoonname'];
        //     }
        //     // 越南时间转换
        //     $discuss_time = substr($value['discuss_time'], 0, -3);
        //     $time = strtotime($discuss_time);
        //     $arr[$key]['discuss_time'] = date('d-m-Y H:i', $time);
        //     // 获取个人评论回复
        //     $answers = T('further_discuss')->field('further_id,discuss_id,further_comment,reply_name')->where(['discuss_id' => $value['discuss_id']])->get_all();
        //     $arr[$key]['answer'] = $answers;
        // }
        // $this->returnSuccess($arr);
    }
    // 投诉与建议

    public function control_add_agree()
    {
        $data = get(['string' => ['content' => 1, 'email' => 1]]);

        $data['users_id'] = $this->users_id;
        $data['plat'] = $this->head['devicetype'];
        $data['agree_time'] = date('Y-m-d H:i:s', time());
        $user = parent::$wrap_user;
        $data['username'] = $user['nickname'];

        $res = T('agree')->add($data);

        if ($res) {
            $this->returnSuccess('提交成功');
        } else {
            Out::jerror('反馈失败', null, '100127');
        }
    }
    // 获取我邀请的好友

    public function control_get_my_invite()
    {
        $page = get(['int' => ['page']]);
        $w = ['u_id' => $this->get_userid(), 'status' => 1];
        $arr = T('user_invite')
            ->field('users_id,invite_time,icon,nick_name')
            ->where($w)
            ->set_limit([$page['page'], 10])->get_all();
        $user_count = T('user_invite')->where($w)->get_count();
        $icon_counts = T('user_invite')->where($w)->set_field('sum(icon) as sums')->get_one();
        $icon_count = $icon_counts['sums'];

        foreach ($arr as $key => $value) {
            // 越南时间转换
            $invite_time = substr($value['invite_time'], 0, -3);
            $time = strtotime($invite_time);
            $arr[$key]['invite_time'] = date('d-m-Y H:i', $time);
        }
        $res = [
            'user_invite' => $arr,
            'user_count' => $user_count,
            'icon_count' => $icon_count,
        ];
        $this->returnSuccess($res);
    }
    //新充值明细

    public function control_get_month_charge()
    {

        // $array = [
        //     1 => 'เติมเงิน',
        //     2 => 'ระบบส่ง',
        //     3 => 'เติมให้เพื่อน',
        //     4 => 'VIPครึ่งปี',
        //     5 => 'VIPรายปี',
        // ];

        // $pages = get(['int' => ['page']]);

        // $page = $pages['page'];
        // $list = T('charge')->field('users_id,charge_icon,send_coin,local_time,charge_type')
        //     ->where(['users_id' => $this->get_userid()])->order('local_time desc')->set_limit([$page, 200])->get_all();
        // foreach ($list as $key => $value) {
        //     $list[$key]['charge_types'] = $list[$key]['charge_type'];
        //     $list[$key]['charge_type'] = $array[$list[$key]['charge_type']];

        //     $dates = $value['local_time'];
        //     $times = date('Y-m-d H:i:s', strtotime("$dates -1 hour"));
        //     $charge_time = substr($times, 0, -3);
        //     $time = strtotime($charge_time);
        //     $list[$key]['charge_time'] = date('Y-m-d H:i', $time);
        //     $ltime = date('Y-m-d', strtotime($dates));
        //     $list[$key]['local_time'] = strtotime($ltime);
        //     $index = date('Y-m', strtotime($dates));
        //     if (!isset($sum[$index])) {
        //         $sum[$index]['timesd'] = ($index);
        //         //d( 'charge_time like \"'.$index.'%\"' );
        //         $data = T('charge')->set_where('charge_time like "' . $index . '%"')->where(['users_id' => $this->get_userid()])->field('sum(charge_icon+send_coin) as tol')->get_one();
        //         $sum[$index]['coin_sum'] = $data['tol'];
        //     } else {
        //         // $sum[$index]['coin_sum'] += $value['charge_icon']+$value['send_coin'];
        //     }
        //     $arr[$key]['expend_times'] = strtotime($etime);
        // }
        // $ret = ['list' => $list, 'sum' => array_values($sum)];
        // $this->returnSuccess($ret);
    }
    //新消费记录

    public function control_get_month_expend_record()
    {
        $page = get(['int' => ['page']]);
        $list = T('expend')->field('users_id,section_id,expend_red,expend_time,cart_section_id,cother_name,bother_name,section_title,cart_section_title,expend_type')
            //->where( 'date_sub(curdate(), INTERVAL 7 DAY) <= date("local_time")' )
            ->where(['users_id' => $this->get_userid()])->order('expend_time desc')->set_limit([$page['page'], 100])->get_all();
        $sum = [];
        foreach ($list as $key => $value) {

            if ($value['expend_type'] == 1) {
                if ($value['cart_section_id'] != 0) {
                    $arr[$key]['other_name'] = $value['cother_name'];
                    $arr[$key]['section_title'] = $value['cart_section_title'];
                } elseif ($value['section_id'] != 0) {
                    $arr[$key]['other_name'] = $value['bother_name'];
                    $arr[$key]['section_title'] = $value['section_title'];
                }
            } else {
                if ($value['bother_name'] != '') {
                    $arr[$key]['other_name'] = $value['bother_name'];
                    $arr[$key]['section_title'] = '';
                } elseif ($value['cother_name'] != '') {
                    $arr[$key]['other_name'] = $value['cother_name'];
                    $arr[$key]['section_title'] = '';
                }
            }
            unset($arr[$key]['bother_name']);
            unset($arr[$key]['cother_name']);
            unset($arr[$key]['cart_section_title']);
            unset($arr[$key]['cart_section_id']);
            unset($arr[$key]['section_id']);
            $dates = $value['expend_time'];
            $times = date('Y-m-d H:i:s', strtotime("$dates -1 hour"));
            $expend_time = substr($times, 0, -3);
            $time = strtotime($expend_time);
            //$mouth =   $value[];
            $list[$key]['expend_time'] = date('Y-m-d H:i', $time);
            $arr[$key]['expend_red'] = -$value['expend_red'];
            $etime = date('Y-m-d', strtotime($dates));
            // $index = $etime;
            $index = date('Y-m', strtotime($dates));
            if (!isset($sum[$index])) {
                $sum[$index]['timesd'] = ($index);
                // $sum[$index]['coin_sum'] = $value['expend_red'];
                $data = T('expend')->set_where('expend_time like "' . $index . '%"')->where(['users_id' => $this->get_userid()])->field('sum(expend_red) as tol')->get_one();

                $sum[$index]['coin_sum'] = round($data['tol'], 2);
            } else {
                // $sum[$index]['coin_sum'] += $value['expend_red'];
            }
            $arr[$key]['expend_times'] = strtotime($etime);
        }
        $ret = ['list' => $list, 'sum' => array_values($sum)];
        $this->returnSuccess($ret);
    }

    public function control_day_task_record()
    {
        // $page = get(['int' => ['page']]);
        // $list = T('task_reward_count')->field('users_id,treward_coin,task_time,task_type')
        //     //->where( 'date_sub(curdate(), INTERVAL 7 DAY) <= date("local_time")' )
        //     ->where(['users_id' => $this->get_userid()])->order('task_time desc')->set_limit([$page['page'], 100])->get_all();
        // foreach ($list as $key => $value) {
        //     $dates = $value['task_time'];
        //     $times = date('Y-m-d H:i:s', strtotime("$dates -1 hour"));
        //     $charge_time = substr($times, 0, -3);
        //     $time = strtotime($charge_time);
        //     $list[$key]['task_time'] = date('Y-m-d H:i', $time);

        //     $ltimes = date('Y-m-d', strtotime($dates));
        //     $list[$key]['local_time'] = strtotime($ltimes);
        //     // $index = $ltimes;
        //     $index = date('Y-m', strtotime($dates));
        //     if (!isset($sum[$index])) {
        //         $sum[$index]['timesd'] = ($index);
        //         // $sum[$index]['coin_sum'] = $value['treward_coin'];
        //         $data = T('task_reward_count')->set_where('task_time like "' . $index . '%"')->where(['users_id' => $this->get_userid()])->field('sum(treward_coin) as tol')->get_one();

        //         $sum[$index]['coin_sum'] = $data['tol'];
        //     } else {
        //         //$sum[$index]['coin_sum'] += $value['treward_coin'];
        //     }
        //     $arr[$key]['expend_times'] = strtotime($etime);
        // }
        // $ret = ['list' => $list, 'sum' => array_values($sum)];

        // $this->returnSuccess($ret);
    }

    public function control_userinfo()
    {
        $user = T('third_party_user')->get_one(['id' => $this->get_userid()]);
        $user['uid'] = $user['id'];
        $user['isanswer'] = T('reply')->get_one(['users_id' => $user['uid']]) ? 1 : 0;
        $user['vip_end_time'] = date('m/d/Y', strtotime($user['vip_end_time']));
        unset($user['password']);
        Out::jout($user);
    }

    public function control_correction()
    {
        $data = get(['int' => ['type' => 1, 'wid' => 1, 'sectionid' => 1, 'titletype' => 1], 'string' => ['content' => 1]]);
        // switch ($data['type']) {
        //     case '2':
        //         # code...
        //         $where = ['section_id' => $data['sectionid'], 'cartoon_id' => $data['id']];
        //         $isexist = T('cartoon_section')->get_one($where);
        //         break;

        //     default:
        //         $where = ['section_id' => $data['sectionid'], 'book_id' => $data['id']];
        //         $isexist = T('section')->get_one($where);
        //         # code...
        //         break;
        // }
        // if ($isexist) {
        //     Out::jerror('章节不存在', null, '100134');
        // }
        $data['uid'] = $this->get_userid(1);
        $data['username'] = parent::$wrap_user['nickname'];
        // $data['title'] = $isexist['title'];
        $data['addtime'] = time();
        $insert = T('n_book_wrong')->add($data);
        if ($insert) {
            Out::jout('提交成功');
        } else {
            Out::jerror('提交失败', null, '100135');
        }
    }

    public function control_urge()
    {
        $data = get(['int' => ['type' => 1, 'wid' => 1]]);
        switch ($data['type']) {
            case '2':
                # code...
                $where = ['cartoon_id' => $data['id']];
                $isexist = T('cartoon')->get_one($where);
                break;

            default:
                $where = ['book_id' => $data['id']];
                $isexist = T('book')->get_one($where);
                # code...
                break;
        }
        if ($isexist) {
            Out::jerror('不存在', null, '100134');
        }
        $data['uid'] = $this->get_userid(1);
        $data['username'] = parent::$wrap_user['nickname'];
        $data['title'] = $isexist['other_name'];
        $data['addtime'] = time();
        $insert = T('n_book_urge')->add($data);
        if ($insert) {
            Out::jout('提交成功');
        } else {
            Out::jerror('提交失败', null, '100135');
        }
    }

    public function control_edit()
    {
        $get = get(['string' => ['avater', 'sex', 'nickname', 'more', 'borth']]);
        $get = array_filter($get);
        $bool = T('third_party_user')->update($get, ['id' => $this->get_userid(1)]);
        if ($bool) {
            $user = T('third_party_user')->get_one(['id' => $this->get_userid(1)]);
            $user['uid'] = $user['id'];
            $user['token'] = M('user', 'im')->gettoken($this->get_userid());
            Out::jout($user);
        } else {
            Out::jerror('100200', '编辑失败');
        }
    }

    public function control_cgpwd()
    {
        $get = get(['string' => ['newpwd' => 'md5']]);
        if (Y::$wrap_user['password'] == '') {
            $bool = T('third_party_user')->update(['password' => $get['newpwd']], ['id' => $this->get_userid(1)]);
        } else {
            $get = get(['string' => ['newpwd' => 'md5', 'oldpwd' => 'md5']]);
            if (Y::$wrap_user['password'] == $get['oldpwd']) {
                $bool = T('third_party_user')->update(['password' => $get['newpwd']], ['id' => $this->get_userid(1)]);
            } else {
                Out::jerror('100300', '密码错误');
            }
        }

        if ($bool) {
            $log = new \ng169\control\api\login();
            $log->control_logout();
            Out::jout(T('third_party_user')->get_one(['id' => $this->get_userid()]));
        } else {
            Out::jerror('100200', '编辑失败');
        }
    }
}
