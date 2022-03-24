<?php


namespace ng169\control\index;

use ng169\control\indexbase;
use ng169\tool\Out;
use ng169\lib\Log;

checktop();

class pay extends indexbase
{

    protected $noNeedLogin = ['addmerber'];

    public function control_run()
    {
        $get = get(['int' => ['bookid', 'sid', 'type', 'from']]);
        $res = M('order', 'im')->get_charge($this->get_userid());
        $data = T('third_party_user')->set_field('golden_bean,remainder')->set_where(['id' => $this->get_userid()])->get_one();
        $this->view(null, ['data' => $res, 'wallet' => $data, 'get' => $get]);
    }
    public function control_alipay()
    {
        
        // $get = get(['string' => ['orders' => 1, 'title' => 1, 'desc' => 1, 'pay_money' => 1, 'callurl', 'mid']]);
        $get = get(['string' => ['payid'], 'int' => ['bookid', 'sid', 'type', 'from']]);
   
        $callbackurl = geturl('','callback', 'pay');
        $payinfo = M('adapaytest', 'im')->create($callbackurl, $this->get_userid(), $get['payid'],  $get['type'], $get['bookid'], $get['sid'], $get['from']);
        Out::jout($payinfo);
    }
    public function control_callback()
    {

        $get = get(['string' => ['data', 'sign', 'type']]);
        Log::txt('支付' . json_encode($_POST), DATA . '/log/adapay.txt');
        // if (!json_encode($get['data'])) {
        $get['data'] = str_replace("\\", "", $get['data']);
        // }

        $bool = M('adapaytest', 'im')->verifySign($get['data'], $get['sign']);
        $bool = true;
        if ($bool && $get['type'] == 'payment.succeeded') {
            //成功改变状态
            $orderinfo = json_decode($get['data'], 1);
            $ret = M('adapay', 'im')->sureOrder($orderinfo['order_no']);
            // d($ret, 1);
            //这里回调
            $url = new \ng169\tool\Curl();
            $post['paystatus'] = $ret['paystatus'];
            $post['orders_id'] = $ret['orders_id'];
            $post['thirdpayid'] = $ret['thirdpayid'];
            $post['money'] = $ret['pay_money'];
            $post['attr'] = $ret['attr'];
            $post['order_no'] = M('adapay', 'im')->getpre() . $ret['payid'];
            $ret = $url->post($ret['callurl'], $post);
            Log::txt('验签成功' . json_encode($post), DATA . '/log/paysucceeded.txt');
            Out::jout($post);
        } else {
            //验签失败
            // d(($get['data']));
            // d(json_decode($get['data']));
            Log::txt('验签失败', DATA . '/log/payfail.txt');
            Log::txt($get['data'], DATA . '/log/payfail.txt');
            Log::txt(json_decode($get['data']), DATA . '/log/payfail.txt');
            d('验签失败', 1);
        }
    }
    /**
     * 添加对私收款
     */
    public function control_addmerber()
    {
        $payobj = M('adapaytest', 'im');
        $bool = $payobj->try_add_merber();
        if ($bool) {
            Out::jout($bool);
        } else {
            Out::jerror(__('订单创建失败'));
        }
        // Out::jout($bool ? $bool : '失败');
        // $type = 2;
        // $get = get(['string' => ['name', 'tel', 'usernum', 'bankname', 'cardid', 'bankprovice', 'bankcity']]);
        // $name = $get['name'];
        // $tel = $get['tel'];
        // $usernum = $get['usernum'];
        // $cardid = $get['cardid'];
        // $bankname = $get['bankname'];
        // $bankprovice = $get['bankprovice'];
        // $bankcity = $get['bankcity'];
        // $bool = $payobj->addrecvuser($type, $name, $tel, $usernum, $bankname, $cardid, $bankprovice, $bankcity);

    }
}
