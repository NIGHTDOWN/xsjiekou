<?php

namespace ng169\control\apiv1;

use ng169\control\apiv1base;
use ng169\tool\Out;

checktop();
class google extends apiv1base
{
    protected $noNeedLogin = [''];

    public function control_pay()
    {

        $data = get(['string' => ['purtoken' => 1, 'applepayId' => 1, 'type', 'section_id', 'book_id', 'order_num' => 1]]);
        $order_num = $data['order_num'];
        M('queue', 'im')->point($order_num, 0, $data);
        $check = M('google', 'im')->check($data['applepayId'], $data['purtoken'], 0);

        if ($check) {
            $tradenum = $check['thirdpayid'];
            $syntony = $check['productid'] ? $check['productid'] : $data['applepayId'];
            $order_num = $check['ourorderid'] ? $check['ourorderid'] : $data['order_num']; //不存在就选客户端传上来的
            $ptime = date('Y-m-d H:i:s', time());
            M('order', 'im')->deal($order_num, $tradenum, $syntony, $ptime);

            $user_re = T('third_party_user')->field('remainder')->where(['id' => $this->users_id])->find();
            $result['remainder'] = $user_re['remainder'];
            // $result['product_id'] = $data['applepayId'];
            // $result['recharge_price'] = $recharge['recharge_price'];
            // unset($result['data']);
            // $this->log('支付请求逻辑完成'.$data['order_num']);
            M('queue', 'im')->finsh($order_num);
            $this->returnSuccess($result);
        } else {
            // $purpose_data = $result['data']['receipt']['in_app'][0];
            $purpose_data = [];
            M('order', 'im')->fail($order_num, $purpose_data['orderId'], $purpose_data['purchase_date'], $purpose_data['product_id'], $purpose_data['orderId']);
            //$this->log('支付请求逻辑失败1'.$data['order_num']);
            Out::jerror('支付失败', null, '100139');
        }
        //$this->log('支付请求逻辑失败2'.$data['order_num']);
    }
    public function control_payv3()
    {

        $data = get(['string' => ['purtoken' => 1, 'applepayId' => 1, 'type', 'section_id', 'book_id', 'order_num' => 1]]);
        $order_num = $data['order_num'];
        M('queue', 'im')->point($order_num, 0, $data);
        $check = M('google', 'im')->checkv3($data['applepayId'], $data['purtoken'], 0);

        if ($check) {
            $tradenum = $check['thirdpayid'];
            $syntony = $check['productid'] ? $check['productid'] : $data['applepayId'];
            if ($check['ourorderid'] != $data['order_num']) {
                Out::jerror('支付失败,恶意订单', null, '100189');
            }
            $order_num = $check['ourorderid'] ? $check['ourorderid'] : $data['order_num']; //不存在就选客户端传上来的

            $ptime = date('Y-m-d H:i:s', time());
            M('order', 'im')->deal($order_num, $tradenum, $syntony, $ptime);

            $user_re = T('third_party_user')->field('remainder')->where(['id' => $this->users_id])->find();
            $result['remainder'] = $user_re['remainder'];
            // $result['product_id'] = $data['applepayId'];
            // $result['recharge_price'] = $recharge['recharge_price'];
            // unset($result['data']);
            // $this->log('支付请求逻辑完成'.$data['order_num']);
            M('queue', 'im')->finsh($order_num);
            $this->returnSuccess($result);
        } else {
            // $purpose_data = $result['data']['receipt']['in_app'][0];
            $purpose_data = [];
            M('order', 'im')->fail($order_num, $purpose_data['orderId'], $purpose_data['purchase_date'], $purpose_data['product_id'], $purpose_data['orderId']);
            //$this->log('支付请求逻辑失败1'.$data['order_num']);
            Out::jerror('支付失败', null, '100139');
        }
        //$this->log('支付请求逻辑失败2'.$data['order_num']);
    }
    // 苹果代充支付回调
    public function control_agentPay()
    {
        //苹果内购的验证收据
        $data = get(['string' => ['purchase' => 1, 'signature' => 1, 'type', 'section_id', 'book_id' => 1, 'order_num' => 1]]);
        //$receipt_data = $data['receipt_data'];
        $order_num = $data['order_num'];
        M('queue', 'im')->point($order_num, 0, $data);
        $check = M('pay', 'im')->googlechek($data['purchase'], $data['signature']);
        // 验证支付状态
        // $result=validate_apple_pay($receipt_data);
        if ($check) {
            $tradenum = $check['thirdpayid'];
            $syntony = $check['productid'];
            $order_num = $check['ourorderid'];
            $ptime = date('Y-m-d H:i:s', time());
            M('order', 'im')->deal($order_num, $tradenum, $syntony, $ptime);
            $user_re = T('third_party_user')->field('remainder')->where(['id' => $this->uid])->find();
            $result['remainder'] = $user_re['remainder'];
            // $result['product_id'] = $appId;
            // $result['recharge_price'] = $recharge['recharge_price'];
            //unset($result['data']);
            M('queue', 'im')->finsh($order_num);
            $this->returnSuccess($result);
        } else {
            // $purpose_data = $result['data']['receipt']['in_app'][0];

            $purpose_data = [];
            M('order', 'im')->fail($order_num, $purpose_data['orderId'], $purpose_data['purchase_date'], $purpose_data['product_id'], $purpose_data['orderId']);

            // M('order', 'im')->fail($order_num, $purpose_data['orderId'], $purpose_data['purchase_date'], $purpose_data['product_id'], $purpose_data['orderId']);
            Out::jerror('支付失败', null, '100139');
        }
    }
}
