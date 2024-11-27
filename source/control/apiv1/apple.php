<?php
namespace ng169\control\apiv1;

use ng169\control\apiv1base;
use ng169\tool\Out;

checktop();
class apple extends apiv1base
{
    protected $noNeedLogin = [''];
    public function control_pay()
    {
        //苹果内购的验证收据
        $data = get(['string' => ['receipt_data' => 1, 'type', 'section_id', 'book_id' , 'order_num' => 1]]);
        $receipt_data = $_REQUEST['receipt_data'];
        $order_num = $data['order_num'];
        $w['order_num'] = $order_num;
        $w['users_id'] = $this->get_userid(1);        
        $order = T('order')->where($w)->get_one();
        // d($w,1);
        if (!$order) {
            Out::jerror('订单不存在', null, '100140');
        }       
        if ($order['status'] != 0) {
            Out::jerror('订单已经失效', null, '100141');
        }       
        $user = parent::$wrap_user;
        // 验证支付状态
        $result = M('pay', 'im')->applechek($receipt_data);
       
        
        if ($result['status']) {
           
            $value = $result['data']['receipt']['in_app'][0];
            $appId = "";
            $payid=get(['string'=>['transaction_id'=>1]]);
            $appId = $value['product_id'];

            $tradenum = $payid['transaction_id'];
            
            $syntony = $payid['transaction_id'];
            $ptime = time();
             
            $recharge = M('order', 'im')->deal($order_num, $tradenum, $syntony, $ptime);
           
            $user_re = T('third_party_user')->field('remainder')->where(['id' => $this->users_id])->find();
            $results['remainder'] = $user_re['remainder'];
            $results['product_id'] = $appId;
            $results['recharge_price'] = $recharge['recharge_price'];           
            unset($result['data']);           
            $this->returnSuccess($results);
        } else {
        	 
            // echo "2";die;
            $purpose_data = $result['data']['receipt']['in_app'][0];
            M('order', 'im')->fail($order_num, $purpose_data['transaction_id'], $purpose_data['purchase_date'], $purpose_data['product_id'], $purpose_data['transaction_id']);
            Out::jerror('支付失败', null, '100139');
        }
    }
    // 苹果代充支付回调
    public function control_agentPay()
    {
        //苹果内购的验证收据
        $data = get(['string' => ['receipt_data' => 1, 'type', 'section_id', 'book_id' , 'order_num' => 1]]);
        $receipt_data = $data['receipt_data'];
        // $type = $data['type'];
        // $section_id = $data['section_id'];
        // $book_id = $data['book_id'];
        $order_num = $data['order_num'];
        $w['order_num'] = $order_num;
        $w['proxy_id'] = $this->get_userid(1);
        $order = T('order')->where($w)->get_one();
        if (!$order) {
            Out::jerror('订单不存在', null, '100140');
        }
        // if ($order['status'] != 0) {
        //     Out::jerror('订单已经失效', null, '100141');
        // }
        // if ($order['proxy_id'] != 0) {
        //     $agent_id = $order['users_id'];
        // }
        $result = M('pay', 'im')->applechek($receipt_data);
        // 验证支付状态
        // $result=validate_apple_pay($receipt_data);
        if ($result['status']) {
            $value = $result['data']['receipt']['in_app'][0];
            // foreach ($purpose_data as $key => $value) {
            $tradenum = $value['transaction_id'];
            $syntony = $value['transaction_id'];
            $ptime = $value['purchase_date'];
            M('order', 'im')->deal($order_num, $tradenum, $syntony, $ptime);
            $user_re = T('third_party_user')->field('remainder')->where(['id' => $this->users_id])->find();
            $r['remainder'] = $user_re['remainder'];
            // $result['product_id'] = $appId;
            // $result['recharge_price'] = $recharge['recharge_price'];
            // unset($result['data']);
            $this->returnSuccess($r);
        } else {
            $purpose_data = $result['data']['receipt']['in_app'][0];
            M('order', 'im')->fail($order_num, $purpose_data['transaction_id'], $purpose_data['purchase_date'], $purpose_data['product_id'], $purpose_data['transaction_id']);
            Out::jerror('支付失败', null, '100139');
        }
    }

}
