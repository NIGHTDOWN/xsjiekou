<?php

namespace ng169\model\index;

use ng169\tool\Out;
use ng169\Y;

checktop();
class order extends Y
{
    /**
     * 支付回调处理逻辑
     */
    public function deal($orderid, $tradenum, $syntony, $ptime)
    {

        $orders = T('order')->field('order_num')->where(['trade_num' => $tradenum])->find();
        $w['order_num'] = $orderid;
        $order = T('order')->where($w)->get_one();
        if (!$order) {
            Out::jerror('订单不存在', null, '100140');
        }
        if ($order['pay_status'] != 0) {
            Out::jerror('订单已经失效', null, '100141');
        }
        $uid = $order['users_id'];
        $pid = $order['create_syntony'];
        // $is = T('order')->get_one(['users_id' => $order['users_id'], 'pay_status' => 1]);
        // if ($is) {

        //     $recharge = T('recharge')->field('recharge_price,dummy_icon,first_send')->where(['applepayId' => $pid, 'isfrist' => 0])->find();
        // } else {
        $recharge = T('recharge')->field('recharge_price,dummy_icon,first_send')->where(['applepayId' => $pid])->find();
        // }
        if (!$recharge) {
            Out::jerror('购买商品不存在或者已下架', null, '100151');
        }
        $user = T('third_party_user')
            ->field('id,remainder,charge_all,create_time,invite_id,isnew')
            ->where(['id' => $uid])->find();

        //添加充值记录
        M('user', 'im')
            ->ulog($uid, $recharge['recharge_price'], $tradenum, $orderid, $ptime, 5, $pid, $syntony);

        //确认订单
        if ($order['proxy_id'] != 0) {
            //$agent_id=$order['users_id'];
            //代理
            $isfirst = M('order', 'im')->sure($orderid, null, $tradenum, $ptime, $pid, $syntony);
            //代充记录
            M('census', 'im')->agentpaylog($order['proxy_id'], $uid, $recharge['recharge_price'], $recharge['dummy_icon'] * 0.2);
            $send = 0;
            $cztype = 3;
        } else {
            //非代理执行动作
            $cztype = 1;
            $isfirst = M('order', 'im')->sure($orderid, $uid, $tradenum, $ptime, $pid, $syntony);
            // if (!$isfirst) {
            //     $send = 0;
            // } else {
            //     $send = $recharge['first_send'];
            // }
            $send = $recharge['first_send'];
            if ($user['invite_id']) {
                M('coin', 'im')->divided($uid, $user['invite_id'], $recharge['dummy_icon']);
            }
        }
        //付款方显示
        // M('order', 'im')->chargelog($order['proxy_id'] ? $order['proxy_id'] : $order['users_id'], $cztype, $recharge['recharge_price'], $recharge['dummy_icon'], $send);
        //到账方显示 
        M('order', 'im')->chargelog($order['users_id'], $cztype, $recharge['recharge_price'], $recharge['dummy_icon'], $send);
        //增加金币
        M('coin', 'im')->cz($uid, $recharge['dummy_icon'] + $send, $recharge['recharge_price']);
        M('count', 'im')->recharge($recharge['recharge_price'], $order['plat']);
        $this->s2s($orderid);

        $isoneday = M('count', 'im')->dayrecharge($user, $recharge['recharge_price'], $order['pay_type'], $order['plat']);

        // if ($isoneday && $isfirst) {
        //     M('count', 'im')->_newrecharge();
        // } else {
        //     M('count', 'im')->_oldrecharge();
        // }
        //书籍购买统计
        if ($order['book_id']) {
            M('bookcensus', 'im')->orderpay(1, $order['book_id'], $order['section_id']);
            M('census', 'im')->bpaycounts($order['book_id'], $recharge['recharge_price']);
        } else {
            M('bookcensus', 'im')->orderpay(2, $order['cartoon_id'], $order['section_id']);
            M('census', 'im')->cpaycounts($order['cartoon_id'], $recharge['recharge_price']);
        }
        return $recharge;
    }
    public function queuedeal($orderid, $tradenum, $syntony, $ptime)
    {

        $orders = T('order')->field('order_num')->where(['trade_num' => $tradenum])->find();
        $w['order_num'] = $orderid;
        $order = T('order')->where($w)->get_one();
        if (!$order) {
            return '订单不存在';
            // Out::jerror('订单不存在', null, '100140');
        }
        if ($order['pay_status'] != 0) {
            return '订单已经失效';
            // Out::jerror('订单已经失效', null, '100141');
        }

        $uid = $order['users_id'];
        $pid = $order['create_syntony'];
        $is = T('order')->get_one(['users_id' => $order['users_id'], 'pay_status' => 1]);
        if ($is) {

            $recharge = T('recharge')->field('recharge_price,dummy_icon,first_send')->where(['applepayId' => $pid, 'isfrist' => 0])->find();
        } else {
            $recharge = T('recharge')->field('recharge_price,dummy_icon,first_send')->where(['applepayId' => $pid])->find();
        }
        if (!$recharge) {
            return '购买商品不存在或者已下架';
            // Out::jerror('', null, '100151');
        }
        $user = T('third_party_user')
            ->field('id,remainder,charge_all,create_time,invite_id,isnew')
            ->where(['id' => $uid])->find();

        //添加充值记录
        M('user', 'im')
            ->ulog($uid, $recharge['recharge_price'], $tradenum, $orderid, $ptime, 5, $pid, $syntony);

        //确认订单
        if ($order['proxy_id'] != 0) {
            //$agent_id=$order['users_id'];
            //代理
            $isfirst = M('order', 'im')->sure($orderid, null, $tradenum, $ptime, $pid, $syntony);
            //代充记录
            M('census', 'im')->agentpaylog($order['proxy_id'], $uid, $recharge['recharge_price'], $recharge['dummy_icon'] * 0.2);
            $send = 0;
            $cztype = 3;
        } else {
            //非代理执行动作
            $cztype = 1;
            $isfirst = M('order', 'im')->sure($orderid, $uid, $tradenum, $ptime, $pid, $syntony);
            // if (!$isfirst) {
            //     $send = 0;
            // } else {
            //     $send = $recharge['first_send'];
            // }
            $send = $recharge['first_send'];
            if ($user['invite_id']) {
                M('coin', 'im')->divided($uid, $user['invite_id'], $recharge['dummy_icon']);
            }
        }

        M('order', 'im')->chargelog($order['proxy_id'] ? $order['proxy_id'] : $order['users_id'], $cztype, $recharge['recharge_price'], $recharge['dummy_icon'], $send);
        //增加金币
        M('coin', 'im')->cz($uid, $recharge['dummy_icon'] + $send, $recharge['recharge_price']);
        M('count', 'im')->recharge($recharge['recharge_price'], $order['plat']);
        $this->s2s($orderid);

        $isoneday = M('count', 'im')->dayrecharge($user, $recharge['recharge_price'], $order['pay_type'], $order['plat']);

        // if ($isoneday && $isfirst) {
        //     M('count', 'im')->_newrecharge();
        // } else {
        //     M('count', 'im')->_oldrecharge();
        // }
        //书籍购买统计
        if ($order['book_id']) {
            M('bookcensus', 'im')->orderpay(1, $order['book_id'], $order['section_id']);
            M('census', 'im')->bpaycounts($order['book_id'], $recharge['recharge_price']);
        } else {
            M('bookcensus', 'im')->orderpay(2, $order['cartoon_id'], $order['section_id']);
            M('census', 'im')->cpaycounts($order['cartoon_id'], $recharge['recharge_price']);
        }
        return $recharge;
    }
    public function fail($ordernum, $trade_num, $thirdpaytime, $create_syntony, $pay_syntony)
    {
        $arr['trade_num'] = $trade_num;
        $arr['pay_status'] = 2;
        $arr['pay_time'] = $thirdpaytime;
        $arr['local_time'] = date('Y-m-d H:i:s', time());
        //$arr['create_syntony'] = $create_syntony;
        $arr['pay_syntony'] = $pay_syntony;
        return T('order')->update($arr, ['order_num' => $ordernum]);
    }
    //返回是否首次充值
    public function sure($ordernum, $uid, $trade_num, $thirdpaytime, $create_syntony, $pay_syntony)
    {

        $isfirst = $this->isfirstcharge($uid);
        if ($isfirst) {
            $arr['first_charge'] = 1;
        } else {
            $arr['first_charge'] = 2;
        }
        $arr['trade_num'] = $trade_num;
        $arr['pay_status'] = 1;
        $arr['pay_time'] = $thirdpaytime;
        $arr['local_time'] = date('Y-m-d H:i:s', time());

        $arr['create_syntony'] = $create_syntony;
        $arr['pay_syntony'] = $pay_syntony;
        T('order')->update($arr, ['order_num' => $ordernum]);
        return $isfirst;
    }
    public function isfirstcharge($uid)
    {
        if (!$uid) {
            return false;
        }
        $userorder = T('order')->field('first_charge')->where(['users_id' => $uid, 'proxy_id' => 0])
            ->where(['pay_status' => 1])->get_one();
        return !$userorder;
    }
    public function chargelog($payuid, $type, $money, $coin, $send_coin)
    {
        $chargesd['users_id'] = $payuid;
        $chargesd['charge_icon'] = $coin;
        $chargesd['charge_type'] = $type;
        $chargesd['charge_time'] = date('Y-m-d H:i:s', time());
        $chargesd['local_time'] = date('Y-m-d H:i:s', time());
        $chargesd['charge_price'] = $money;
        $chargesd['send_coin'] = $send_coin;
        $chargesd['addtime'] = time();
        return T('charge')->add($chargesd);
    }
    public function s2s($ordernum)
    {
        // $this->call($user_re['deviceToken'],$recharge['yuenan_icon'],'IDR',$this->users_id,$data['order_num']);
        if (!$ordernum) {
            return false;
        }
        //Log::txt('开启s2s：' . $ordernum, 's2s.txt');
        $order = T('order')->set_where(['order_num' => $ordernum])->join_table(['t' => 'third_party_user', 'users_id', 'id'])->get_one();
        if ($order) {
            $detoken = $order['deviceToken'];
            $uid = $order['users_id'];
            $currency = "THB";
            $ordernum = $ordernum;
            $recharge = T('recharge')->set_field('yuenan_icon')->get_one(['applepayId' => $order['create_syntony']]);
            $yuan = $recharge['yuenan_icon'];
            $p = $order['pay_type'];
            $ret = M('fb', 'im')->s2s($detoken, $yuan, $currency, $uid, $ordernum, $p);
            // Log::txt($ret, 's2s.txt');
        }
    }
    public function get_charge($uid)
    {
        $index = 'userisfirstpay' . $uid;
        $user = false;
        $cache = Y::$cache->get($index);
        if ($cache[0]) {
            $user = $cache[1];
        } else {
            $user = T('order')->field('order_id')->where(['users_id' => $uid, 'proxy_id' => 0])
                ->where(['pay_status' => 1])->get_one();
            if ($user) {
                Y::$cache->set($index, $user, G_DAY * 31);
            }
        }

        // if ($device_type == 'android') {
        //     $where2['device_type'] = '2';
        // } else {
        //     $where2['device_type'] = '1';
        // }
        $where2['device_type'] = '2';
        if ($user) {
            //正常充值模式
            $where['isfrist'] = 0;
        } else {
            //首充模式
            $where['isfrist'] = 1;
        }

        $where['isshow'] = 1;
        $index2 = 'paylist3_' . $where['isfrist'] . '_' . $where2['device_type'];
        $cache2 = Y::$cache->get($index2);
        if ($cache2[0]) {
            $res = $cache2[1];
        } else {
            $res = T('recharge')
                ->field('recharge_price,dummy_icon,first_send,yuenan_icon,applepayId,invite,intro,USD,isfrist')
                ->where('(device_type=' . $where2['device_type'] . ')')
                ->where($where)
                ->get_all();
            Y::$cache->set($index2, $res, G_DAY);
        }

        // 查询充值参数
        if (sizeof($res) == 0) {
            //未配置首充选项，显示正常模式
            $where['isfrist'] = 0;
            $res = T('recharge')
                ->field('recharge_price,dummy_icon,first_send,yuenan_icon,applepayId,invite,intro,USD,isfrist')
                ->where('(device_type=' . $where2['device_type'] . ')')
                ->where($where)
                ->get_all();
        }

        return $res;
    }

    /**
     * 创建订单
     * @uid 用户ID
     * @pid 商品id
     * paytype 支付类型
     * bookid 书id
     * booktype 书类型
     * sid   章节id
     * active_id 充值入口
     */

    public function create($uid, $pid, $paytype, $bookid = 0, $booktype = 0, $sid = 0, $active_id = 1)
    {

        if (!$uid) return false;
        if (!$pid) return false;
        if (!$paytype) return false;
        // $data = get(['int' => ['book_id', 'section_id', 'pay_type' => 1, 'agent_id'], 'string' => ['applepayId' => 1, 'type' => 1]]);
        $recharge = T('recharge')->field('recharge_price,dummy_icon,first_send,USD')->where(['applepayId' => $pid])->find();

        if (!$recharge) return false;
        $devicetype = getdevicetype(Y::$wrap_head);
        $arr['order_num'] = date('YmdHis') . $uid . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        //中文金额
        $arr['price'] = $recharge['USD'];
        $arr['pay_status'] = 0;
        $arr['make_time'] = date('Y-m-d H:i:s', time());
        $arr['dates'] = date('Ymd');
        $arr['fact_price'] = $recharge['recharge_price'];
        $arr['plat'] = $devicetype;
        $arr['source_version'] = @Y::$wrap_head['version'];
        $arr['create_syntony'] = $pid;
        $arr['user_message'] = $devicetype;
        // 新加
        $id = $uid;
        if (isset($data['agent_id']) && $data['agent_id']) {
            // //代充
            // if ($data['agent_id'] != $this->users_id) {
            //     $user = T('third_party_user')->field('id')->where(['id' => $data['agent_id']])->find();
            //     if ($user) {
            //         $arr['users_id'] = $data['agent_id'];
            //         $arr['proxy_id'] = $this->users_id;
            //     } else {
            //         Out::jerror('代充用户不存在', null, '100138');
            //     }
            // } else {
            //     Out::jerror('代充对象不能是自己', null, '100137');
            // }
            // //代充无首充
            // $arr['first_charge'] = 2;
        } else {
            $userorder = T('order')->field('first_charge')->where(['users_id' => $id, 'create_syntony' => $pid, 'pay_status' => 1, 'proxy_id' => 0])->find();
            // $userorder = T('order')->field('first_charge')->where('users_id', $this->users_id)->where('create_syntony', $data['applepayId'])->where('pay_status', 1)->find();
            $arr['users_id'] = $uid;
            // $arr['proxy_id'] = $this->users_id;
            if ($userorder) {
                $arr['first_charge'] = 2;
            } else {
                $arr['first_charge'] = 1;
            }
        }

        $arr['book_id'] = $bookid;
        $arr['section_id'] = $sid;
        $arr['booktype'] = $booktype;
        // if (isset($data['type']) && isset($data['book_id']) && isset($data['section_id'])) {
        //     if ($data['type'] == 1) {
        //         $arr['book_id'] = $data['book_id'];
        //         $arr['section_id'] = $data['section_id'];
        //         // $arr['type'] = 1;
        //     } elseif ($data['type'] == 2) {
        //         $arr['cartoon_id'] = $data['book_id'];
        //         $arr['cart_section_id'] = $data['section_id'];
        //         // $arr['type'] = 2;
        //     }
        //     $arr['active_id'] = $data['type'];
        // }
        $arr['pay_type'] = $paytype;
        $arr['active_id'] = $active_id;
        $id = T('order')->add($arr);
        $arr['id'] = $id;
        M('census', 'im')->ordercount(); //下单订单统计
        // M('census', 'im')->ordercount(); //下单订单统计
        M('bookcensus', 'im')->ordernum($booktype, $bookid, $sid);
        M('count', 'im')->countorder();
        // $result['order_num'] = $arr['order_num'];
        return $arr;
        // $this->returnSuccess($result);
    }
}
