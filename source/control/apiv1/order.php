<?php

namespace ng169\control\api;

use ng169\control\apiv1base;
use ng169\tool\Out;
use ng169\Y;

checktop();
class order extends apiv1base
{
    protected $noNeedLogin = ['order_log', 'get_vip_charge'];
    //生成订单
    public function control_creat()
    {
        $data = get(['int' => ['book_id', 'section_id', 'pay_type' => 1, 'agent_id'], 'string' => ['applepayId' => 1, 'type' => 1]]);
        $recharge = T('recharge')->field('recharge_price,dummy_icon,first_send')->where(['applepayId' => $data['applepayId']])->find();
        $arr['order_num'] = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $arr['price'] = $recharge['recharge_price'];
        $arr['pay_status'] = 0;
        $arr['make_time'] = time();
        $arr['dates'] = date('Ymd');
        $arr['fact_price'] = $recharge['recharge_price'];
        $arr['plat'] = $this->head['devicetype'];
        $arr['source_version'] = $this->head['version'];
        $arr['create_syntony'] = $data['applepayId'];
        // $arr['plat'] = $this->head['devicetype'];
        // $arr['source_version'] = $this->head['version'];
        $arr['user_message'] = $this->head['devicetype'];
        // 新加
        $id = $this->get_userid(1);
        if (isset($data['agent_id']) && $data['agent_id']) {
            //代充
            if ($data['agent_id'] != $this->users_id) {
                $user = T('third_party_user')->field('id')->where(['id' => $data['agent_id']])->find();
                if ($user) {
                    $arr['users_id'] = $data['agent_id'];
                    $arr['proxy_id'] = $this->users_id;
                } else {
                    Out::jerror('代充用户不存在', null, '100138');
                }
            } else {
                Out::jerror('代充对象不能是自己', null, '100137');
            }
            //代充无首充
            $arr['first_charge'] = 2;
        } else {
            $userorder = T('order')->field('first_charge')->where(['users_id' => $id, 'create_syntony' => $data['applepayId'], 'pay_status' => 1, 'proxy_id' => 0])->find();
            // $userorder = T('order')->field('first_charge')->where('users_id', $this->users_id)->where('create_syntony', $data['applepayId'])->where('pay_status', 1)->find();
            $arr['users_id'] = $this->users_id;
            // $arr['proxy_id'] = $this->users_id;
            if ($userorder) {
                $arr['first_charge'] = 2;
            } else {
                $arr['first_charge'] = 1;
            }
        }

        if (isset($data['type']) && isset($data['book_id']) && isset($data['section_id'])) {
            if ($data['type'] == 1) {
                $arr['book_id'] = $data['book_id'];
                $arr['section_id'] = $data['section_id'];
                // $arr['type'] = 1;
            } elseif ($data['type'] == 2) {
                $arr['cartoon_id'] = $data['book_id'];
                $arr['cart_section_id'] = $data['section_id'];
                // $arr['type'] = 2;
            }
            $arr['active_id'] = $data['type'];
        }
        $arr['pay_type'] = $data['pay_type'];

        T('order')->add($arr);
        M('census', 'im')->ordercount(); //下单订单统计
        // M('census', 'im')->ordercount(); //下单订单统计
        M('bookcensus', 'im')->ordernum($data['type'], $data['book_id'], $data['section_id']);
        M('count', 'im')->countorder();
        $result['order_num'] = $arr['order_num'];
        $this->returnSuccess($result);
    }
    //生成vip订单
    public function control_vip_creat()
    {
        $data = get(['int' => ['pay_type' => 1], 'string' => ['applepayId' => 1]]);
        $recharge = T('recharge')->field('recharge_price,dummy_icon,first_send')->where(['applepayId' => $data['applepayId'], 'type' => 1])->find();
        if (!$recharge) {
            Out::jerror('vip商品不存在', null, '100238');
        }
        $arr['order_num'] = date('YmdHis') . str_pad(mt_rand(1, 99999), 5, '0', STR_PAD_LEFT);
        $arr['price'] = $recharge['recharge_price'];
        $arr['pay_status'] = 0;
        $arr['make_time'] = date('Y-m-d H:i:s', time());
        $arr['fact_price'] = $recharge['recharge_price'];
        $arr['plat'] = $this->head['devicetype'];
        $arr['source_version'] = $this->head['version'];
        $arr['create_syntony'] = $data['applepayId'];
        // $arr['plat'] = $this->head['devicetype'];
        // $arr['source_version'] = $this->head['version'];
        $arr['user_message'] = $this->head['devicetype'];
        // 新加
        $id = $this->get_userid(1);
        $arr['pay_type'] = $data['pay_type'];
        $arr['trade_type'] = 2;
        $arr['users_id'] = $id;
        T('order')->add($arr);
        //M('census', 'im')->ordercount(); //下单订单统计
        M('count', 'im')->countorder();
        $result['order_num'] = $arr['order_num'];
        $this->returnSuccess($result);
    }
    // 用户取消订单处理
    public function control_fail()
    {
        $data = get(['string' => ['order_num' => 1, 'pay_status' => 1]]);
        //$recharge = T('recharge')->field('recharge_price,dummy_icon,first_send')->where(['applepayId' => $data['applepayId']])->find();
        if ($data['pay_status'] < 2) {
            Out::jerror('状态非法', null, '100139');
        }
        $arr['pay_status'] = $data['pay_status'];
        $arr['local_time'] = date('Y-m-d H:i:s', time());
        $where['users_id'] = $this->users_id;
        $where['order_num'] = $data['order_num'];
        $where['plat'] = $this->head['devicetype'];
        M('census', 'im')->_dayorder();
        T('order')->update($arr, $where);
        Out::jout('取消成功');
    }
    // 获取充值参数
    public function control_get_charge()
    {
        $device_type = $this->head['devicetype'];
        $agent_ids = get(['int' => ['agent_id']]);
        $agent_id = $agent_ids['agent_id'];
        // $where = ['type' => 0];
        // $index = 'userisfirstpay' . $this->get_userid(1);

        // $cache = Y::$cache->get($index);

        // if ($cache[0]) {
        //     $user = $cache[1];
        // } else {
        //     $user = T('order')->field('order_id')->where(['users_id' => $this->get_userid(1), 'proxy_id' => 0])
        //         ->where(['pay_status' => 1])->get_one();
        //     if ($user) {
        //         Y::$cache->set($index, $user);
        //     }
        // }

        // if ($device_type == 'android') {
        //     $where2['device_type'] = '2';
        // } else {
        //     $where2['device_type'] = '1';
        // }
        // if ($user) {
        //     //正常充值模式
        //     $where['isfrist'] = 0;
        // } else {
        //     //首充模式
        //     $where['isfrist'] = 1;
        // }

        // $where['isshow'] = 1;
        // $index2 = 'paylist_' . $where['isfrist'] . '_' . $where2['device_type'];
        // $cache2 = Y::$cache->get($index2);
        // if ($cache2[0]) {
        //     $res = $cache2[1];
        // } else {
        //     $res = T('recharge')
        //         ->field('recharge_price,dummy_icon,first_send,yuenan_icon,applepayId,invite,intro,USD,isfrist')
        //         ->where('(device_type=' . $where2['device_type'] . ')')
        //         ->where($where)
        //         ->get_all();
        //     Y::$cache->set($index2, $res, G_DAY);
        // }
        // // 查询充值参数

        // if (sizeof($res) == 0) {
        //     //未配置首充选项，显示正常模式
        //     $where['isfrist'] = 0;
        //     $res = T('recharge')
        //         ->field('recharge_price,dummy_icon,first_send,yuenan_icon,applepayId,invite,intro,USD,isfrist')
        //         ->where('(device_type=' . $where2['device_type'] . ')')
        //         ->where($where)
        //         ->get_all();
        // }

        // if ($agent_id) {
        //     foreach ($res as $k => $v) {
        //         $res[$k]['first_send'] = 0;
        //     }
        // }
        $res = M('order', 'im')->get_charge($this->get_userid());
        $this->returnSuccess($res);
    }
    //根据国家汇率计算显示值
    public function control_get_charges()
    {
        $device_type = $this->head['devicetype'];
        $agent_ids = get(['int' => ['agent_id']]);
        $agent_id = $agent_ids['agent_id'];
        $where = ['type' => 0];
        $index = 'userisfirstpay' . $this->get_userid(1);

        $cache = Y::$cache->get($index);

        if ($cache[0]) {
            $user = $cache[1];
        } else {
            $user = T('order')->field('order_id')->where(['users_id' => $this->get_userid(1), 'proxy_id' => 0])
                ->where(['pay_status' => 1])->get_one();
            if ($user) {
                Y::$cache->set($index, $user);
            }
        }

        if ($device_type == 'android') {
            $where2['device_type'] = '2';
        } else {
            $where2['device_type'] = '1';
        }
        if ($user) {
            //正常充值模式
            $where['isfrist'] = 0;
        } else {
            //首充模式
            $where['isfrist'] = 1;
        }

        $where['isshow'] = 1;
        $index2 = 'paylist_' . $where['isfrist'] . '_' . $where2['device_type'];
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

        if ($agent_id) {
            foreach ($res as $k => $v) {
                $res[$k]['first_send'] = 0;
            }
        }
        //这里根据国家显示不同的语言
        foreach ($res as $k => $v) {
            $res[$k]['yuenan_icon'] = $v['USD'] * $this->head['rate'];
        }
        $this->returnSuccess($res);
    }
    public function control_get_vip_charge()
    {

        $device_type = $this->head['deviceType'];
        $agent_ids = get(['int' => ['agent_id']]);
        $agent_id = $agent_ids['agent_id'];
        $where = ['type' => 1];
        if ($device_type == 'android') {
            $where2['device_type'] = '2';
        } else {
            $where2['device_type'] = '1';
        }
        // 查询充值参数
        $res = T('recharge')
            // ->field('yuenan_icon,applepayId,intro,USD,viptype,symbol')
            ->where($where)
            ->where('(device_type=' . $where2['device_type'] . ' or device_type=3)')
            ->get_all();

        // $from = "VND";
        // $to = "USD";
        $applepayId = "";
        // 越南币与usd转换
        foreach ($res as $key => $value) {
            // $data = file_get_contents("http://www.baidu.com/s?wd={$from}%20{$to}&rsv_spt={$value['yuenan_icon']}");
            // preg_match("/<div>1\D*=(\d*\.\d*)\D*<\/div>/",$data, $converted);
            // // print_r($converted);die;
            // $converted = preg_replace("/[^0-9.]/", "", $converted[1]);
            // $res[$key]['USD'] = $converted * $value['yuenan_icon'];
            $applepayId .= "'" . $value['applepayId'] . "',";
        }
        $applepayId = rtrim($applepayId, ",");
        $isinvite = 0;
        // 查询用户充值订单
        // if (!empty($agent_id)) {
        //     $user = T('order')->field('create_syntony')->whereIn('create_syntony', $applepayId)->where(['users_id' => $agent_id])
        //         ->where(['pay_status' => 1])->get_all();
        // } else {
        //     $user = T('order')->field('create_syntony')->whereIn('create_syntony', $applepayId)->where(['users_id' => $this->users_id])
        //         ->where(['pay_status' => 1])->get_all();
        // }
        // $invite = T('recharge')->where(['invite' => 1])->where($where)->find();
        // $default_isfirst = 0;
        // $invites['create_syntony'] = $invite['applepayId'];
        // if (in_array($invites, $user)) {
        //     $default_isfirst = 1;
        // }

        $this->returnSuccess($res);
    }

    public function control_order_log()
    {
        // $data =$this->request->param();
        $data = get(['string' => ['sign_data', 'applepayId', 'order_num', 'pay_type']]);

        $data['users_id'] = $this->get_userid(1);
        if ($this->head['deviceType'] == "android") {
            $data['tran_time'] = date('Y-m-d H:i:s', $this->head['timestamp'] / 1000);
        } else {
            $data['tran_time'] = date('Y-m-d H:i:s', $this->head['timestamp']);
        }

        // d($data,1);
        $ss = T('order_log')->add($data);
        Out::jout('记录成功');
    }
}
