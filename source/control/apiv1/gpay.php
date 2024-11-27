<?php
namespace ng169\control\apiv1;

use ng169\control\apiv1base;
use ng169\tool\Out;

checktop();
class gpay extends apiv1base
{
    protected $noNeedLogin = [''];

    public function control_pay()
    {

        $data = get(['string' => ['purtoken' => 1, 'applepayId' => 1, 'type', 'section_id', 'book_id', 'order_num' => 1, 'thirdpayid' => 1]]);
        $order_num = $data['order_num'];
        $check = true;
        if ($check) {
            $tradenum = $data['thirdpayid'];
            $syntony = $data['applepayId'];
            $order_num = $order_num;
            $ptime = date('Y-m-d H:i:s', time());
           // T('')->noautocommit();
            try {
                //code...
                M('order', 'im')->deal($order_num, $tradenum, $syntony, $ptime);
              //  T('')->commit(true);
            } catch (\Throwable $th) {
              //  T('')->roll(true);
                //throw $th;
            }

        }
        $user_re = T('third_party_user')->field('remainder')->where(['id' => $this->users_id])->find();
        $result['remainder'] = $user_re['remainder'];
        $this->returnSuccess($result);

    }
    public function control_sub()
    {
        $get = get(['string' => ['ordernum' => 1, 'purtoken' => 1, 'product_id' => 1, 'thirdpayid' => 1]]);
        $uid = $this->get_userid();
        //$in = M('pay', 'im')->checksuborder($uid, $get['ordernum']);
        $tmp = $get;
        $tmp['uid'] = $uid;
        // M('queue', 'im')->point($get['ordernum'], 1, $tmp);
        //$data = M('google', 'im')->check($get['product_id'], $get['purtoken'], 1);
        $data = true;
        if ($data) {
            //支付成功
            // if ($get['ordernum'] != $data['ourorderid']) {
            //     $data['status'] = 0;
            //     Out::jerror('订单校对失败', $data, '10246');
            // }
            //        支付完成的回调
          //  T('')->noautocommit();
            try {
                M('pay', 'im')->backvip($this->get_userid(), $get['ordernum'], $get['thirdpayid'], time(), $get['product_id']);
                M('order', 'im')->s2s($get['ordernum']);
            } catch (\Throwable $th) {
                //M('queue', 'im')->finsh($get['ordernum']);
               // T('')->roll(true);
                // Out::jerror('google支付失败', null, '10215');
                //throw $th;
            }
        }
        $user = T('third_party_user')->set_field('vip_end_time,isvip')->get_one(['id' => $this->get_userid()]);
        $user['vip_end_time'] = date('d/m/Y H:i:s', strtotime($user['vip_end_time']));
        Out::jout($user);
        // else {
        //     //支付失败
        //     // M('queue', 'im')->close($get['ordernum']);
        //     Out::jerror('google支付失败', null, '10215');
        // }
    }

}
