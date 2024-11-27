<?php

namespace ng169\control\api;

use ng169\control\apiv1base;
use ng169\lib\Log;
use ng169\tool\Out;

checktop();

class gsub extends apiv1base
{

    protected $noNeedLogin = ['callback'];
    public function control_pay()
    {
        $get = get(['string' => ['ordernum' => 1, 'purtoken' => 1, 'product_id' => 1]]);
        $uid = $this->get_userid();
        $in = M('pay', 'im')->checksuborder($uid, $get['ordernum']);
        $tmp=$get;
        $tmp['uid']=$uid;
        M('queue', 'im')->point($get['ordernum'], 1, $tmp);
        $data = M('google', 'im')->check($get['product_id'], $get['purtoken'], 1);
        if ($data && $data['status'] == 1) {
            //支付成功
            if ($get['ordernum'] != $data['ourorderid']) {
                $data['status'] = 0;
                Out::jerror('订单校对失败', $data, '10246');
            }
            //        支付完成的回调
            M('pay', 'im')->backvip($this->get_userid(), $data['ourorderid'], $data['thirdpayid'], $data['paytime'], $get['product_id']);
            M('order', 'im')->s2s($get['ordernum']);
            M('queue', 'im')->finsh($get['ordernum']);
            $user = T('third_party_user')->set_field('vip_end_time,isvip')->get_one(['id' => $this->get_userid()]);
            $user['vip_end_time'] = date('d/m/Y H:i:s', strtotime($user['vip_end_time']));
            Out::jout($user);
        } else {
            //支付失败
            // M('queue', 'im')->close($get['ordernum']);
            Out::jerror('google支付失败', null, '10215');
        }
    }
    public function control_callback()
    {

        $gets = file_get_contents("php://input");
        //Log::txt(($gets), 'google.txt');
        $get = json_decode($gets, 1);
        $base64 = $get['message']['data'];
        if (!$base64) {
            Log::txt('获取信息失败', 'google.txt');
            Out::jerror('获取信息失败', null, '10160');
        }
        $data = json_decode(base64_decode($base64), 1);
        if (!$data) {
            Log::txt('解码失败', 'google.txt');
            Out::jerror('解码失败', null, '10161');
        }

        $receipt = $data['subscriptionNotification']['purchaseToken'];
        $thirdpayid = $data['subscriptionNotification']['subscriptionId'];
        /*version    string    此通知的版本。最初，此值将为“1.0”。此版本与其他版本字段不同。
        notificationType    int
        通知的类型。它可以具有以下值：

        (1) SUBSCRIPTION_RECOVERED - 从帐号保留状态恢复了订阅。
        (2) SUBSCRIPTION_RENEWED - 续订了处于活动状态的订阅。
        (3) SUBSCRIPTION_CANCELED - 自愿或非自愿地取消了订阅。如果是自愿取消，在用户取消时发送。
        (4) SUBSCRIP￼￼TION_PURCHASED - 购买了新的订阅。
        (5) SUBSCRIPTION_ON_HOLD - 订阅已进入帐号保留状态（如已启用）。
        (6) SUBSCRIPTION_IN_GRACE_PERIOD - 订阅已进入宽限期（如已启用）。
        (7) SUBSCRIPTION_RESTARTED - 用户已通过“Play”>“帐号”>“订阅”重新激活其订阅（需要选择使用订阅恢复功能）。
        (8) SUBSCRIPTION_PRICE_CHANGE_CONFIRMED - 用户已成功确认订阅价格变动。
        (9) SUBSCRIPTION_DEFERRED - 订阅的续订时间点已延期。
        (10) SUBSCRIPTION_PAUSED - 订阅已暂停。
        (11) SUBSCRIPTION_PAUSE_SCHEDULE_CHANGED - 订阅暂停计划已更改。
        (12) SUBSCRIPTION_REVOKED - 用户在有效时间结束前已撤消订阅。
        (13) SUBSCRIPTION_EXPIRED - 订阅已过期。
        purchaseToken    string    购买订阅时向用户设备提供的令牌。
        subscriptionId    string    所购买订阅的 ID（例如“monthly001”）。*/

        $packname = $data['packageName'];
        if (M('google', 'im')->getpackagename() != $packname) {
            Log::txt('包名效验失败', 'google.txt');
            Out::jerror('包名效验失败', null, '10162');
        }

        $notificationType = $data['subscriptionNotification']['notificationType'];
        if ($notificationType) {
            Log::txt($gets, 'googles.txt');

            //Out::jerror('谷歌支付状态无效',null,'10163');
        } else {
            Log::txt('垃圾数据', 'google.txt');
            Out::jerror('谷歌支付状态无效', null, '10164');
        }
        $pid = $thirdpayid;

        $data = M('google', 'im')->subcheck($pid, $receipt, 1);

        if ($notificationType == 3 && $data['ourorderid']) {
            //取消逻辑
            //$this->closevip($data['ourorderid']);

            Out::jout('取消订阅关闭VIP');
        }

        if ($data['status'] != 1) {
            Log::txt('谷歌支付状态无效', 'google.txt');
            Out::jerror('谷歌支付状态无效', null, '10163');
        }

        //判断支付ID是否已经验证过了
        $transaction_id = $data['thirdpayid'];
        //M('apple','im')->checkthirdid($transaction_id);
        M('pay', 'im')->checkthirdid($transaction_id);
        $w['order_num'] = $data['ourorderid'];
        $w['trade_num'] = $transaction_id;

        $order = T('order')->get_one($w);

        if ($order) {
            Log::txt('订单已处理', 'google.txt');
            Out::jerror('订单已处理', null, '10266');
        }
        $old = \explode('.', $w['trade_num']);

        $uid = M('pay', 'im')->serverbackvip($w['order_num'], $old[0] . '.' . $old[1], $transaction_id, $data['productid']);
        //M('order','im')->s2s($w['ordernum']);
        Out::jout(T('third_party_user')->set_field('vip_end_time,isvip')->get_one(['id' => $uid]));
        // $w2['ordernum']=$data['ourorderid'];
        // $order=T('viporder')->get_one($w2);
        // if(!$order){
        //     Log::txt('订单不存在','google.txt');
        //     Out::jerror('订单不存在',null,'10164');
        // }
        // if($transaction_id==$order['thirdpayid']){
        //     Log::txt('客户端已处理订单','google.txt');
        //     Out::jerror('客户端已处理订单',null,'10165');
        // }
        // if($order['paystatus']==0){
        //     /*Log::txt('订单状态无效','google.txt');
        //     Out::jerror('订单状态无效',null,'10167');*/

        //     //初次购买回调；客户端丢单，服务器执行开通vip
        //     // M('order','am')->backvip($data['ourorderid'],$data['thirdpayid'],$data['paytime']);
        //     // Out::jout(T('user')->set_field('viptime')->get_one(['uid'=>$order['uid']]));
        // }
        // //判断是否续期
        // /*$uid=$this->get_userid();*/
        // //找到老订单信息
        // $old=$order;
        // /*if(!$old){
        //     Out::jerror('未找到旧订单',null,'10131');
        // }*/
        // $old['ordertype']=1;
        // $old['oldthirdpayid']=$order['thirdpayid'];
        // $old['thirdpayid']=$transaction_id;

        // $old['paytime']=time();
        // $old['addtime']=$old['paytime'];
        // $old['paystatus']=1;

        // $sid=explode('.',$data['productid']);
        // $sid=$sid[2];
        //创建订单，续期
        // $product=T('coin_set')->get_one(['sid'=>$sid,'stype'=>1,'flag'=>0]);
        // if(!$product){
        //     Out::jerror('未找到商品或已下架',null,'10132');
        // }
        // $old['cash']=$product['prices'];
        // $old['sid']=$product['sid'];
        // $old['viplong']=$product['dhcoin'];
        // unset($old['oid']);
        // M('order','am')->serverbackvip(T('viporder')->add($old));
        // Out::jout(T('user')->set_field('vip,viptime')->get_one(['uid'=>$old['uid']]));
    }
    public function closevip($ordernum)
    {
        $order = T('order')->set_field('users_id')->get_one(['order_num' => $ordernum]);
        if (!$order) {
            return false;
        }

        return T('third_party_user')->update(['vip_end_time' => '0', 'isvip' => 0, 'open_vip_time' => date('Y-m-d H:i:s')], ['id' => $order['users_id']]);
	}
	
}
