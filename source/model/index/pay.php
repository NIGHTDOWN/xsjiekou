<?php

namespace ng169\model\index;

use ng169\tool\Out;
use ng169\Y;

checktop();
class pay extends Y
{
    private $pwd = "8e4e838ef3644ca5a4dbea06c1cce499";
    public function acurl($receipt_data, $sandbox = 0)
    {
        // $receipt_data = "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";
        //小票信息
        // $POSTFIELDS = array("receipt-data" => $receipt_data);
        // $POSTFIELDS = json_encode($POSTFIELDS);
        //  $POSTFIELDS = '{"receipt-data":"' . $receipt_data . '"}';
        $POSTFIELDS = '{"receipt-data":"' . $receipt_data . '","password":"' . $this->pwd . '"}';
        //    print_r($POSTFIELDS);die;
        //正式购买地址 沙盒购买地址
        // $url_buy = "https://buy.itunes.apple.com/verifyReceipt";
        $url_sandbox = "https://sandbox.itunes.apple.com/verifyReceipt";
        $url = $url_sandbox;

        //简单的curl
        $ch = curl_init($url);
        // print_r($ch);die;
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);
        $result = curl_exec($ch);

        curl_close($ch);
        // print_r($result);die;
        return $result;
    }
    public function applechek($receipt_data)
    {
        /**
         * 21000 App Store不能读取你提供的JSON对象
         * 21002 receipt-data域的数据有问题
         * 21003 receipt无法通过验证
         * 21004 提供的shared secret不匹配你账号中的shared secret
         * 21005 receipt服务器当前不可用
         * 21006 receipt合法，但是订阅已过期。服务器接收到这个状态码时，receipt数据仍然会解码并一起发送
         * 21007 receipt是Sandbox receipt，但却发送至生产系统的验证服务
         * 21008 receipt是生产receipt，但却发送至Sandbox环境的验证服务
         */

        // 验证参数
        if (strlen($receipt_data) < 20) {
            $result = array(
                'status' => false,
                'message' => '非法参数',
            );
            return $result;
        }
        // 请求验证
        $html = $this->acurl($receipt_data);
        $data = json_decode($html, true);

        // 如果是沙盒数据 则验证沙盒模式
        if ($data['status'] == '21007') {
            // 请求验证
            $html = $this->acurl($receipt_data, 1);
            $data = json_decode($html, true);
            $data['sandbox'] = '1';
        }

        if (isset($_GET['debug'])) {
            exit(json_encode($data));
        }
        // d($data,1);
        // 判断是否购买成功
        if (intval($data['status']) === 0) {
            $result = array(
                'status' => true,
                'message' => '购买成功',
                'data' => $data,
            );
        } else {
            $result = array(
                'status' => false,
                'message' => '购买失败 status:' . $data['status'],
            );
        }
        return $result;
    }
    public function googlechek($inappPurchaseData, $inappDataSignature)
    {
        // $inappPurchaseData = isset($data['purchase']) ? $data['purchase'] : null ;
        // $inappDataSignature =isset($data['signature']) ? $data['signature'] : null ;
        $inappPurchaseData = htmlspecialchars_decode($inappPurchaseData);
        $googlePublicKey = 'MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAv2tNbaFGumtAe7XYpUvQ5CXJ3QKuPb0VgNoqqBdy11RjPCk3TShJeDWs84AWkAVXTxFDK8igDrFsiN0O9gq82kZDIrVClzStNPkwKPZ4TXaZBi/K8wxu+ynaHorI4GpHaY/C1eAu45RlT18ZbdLoCKhS8QTfn6hl6KUPEhZBEzIuvkyvUnFMXLUiKUmE+DxJcXNS9ft+QjWzN5riMcR73k3EmX1jmisgMJR5XjOA3BWnNNGXG9bO3U2vu1ds11ji3w9QkHl2PBqG9HFgWxLqnTMKQ+6WUe456XwDzcHeRZvwPj3kGTxG2weEoxP45bFN5LNHGmenshLTglwCbnbXrwIDAQAB';
        $publicKey = "-----BEGIN PUBLIC KEY-----" . PHP_EOL . chunk_split($googlePublicKey, 64, PHP_EOL) . "-----END PUBLIC KEY-----";
        $publicKeyHandle = openssl_get_publickey($publicKey);
        $results = openssl_verify($inappPurchaseData, base64_decode($inappDataSignature), $publicKeyHandle, OPENSSL_ALGO_SHA1);
        if ($results === 1) {
            $datas = json_decode($inappPurchaseData, true);
            return $datas;
        }
        return false;
    }
    public function checksuborder($uid, $ordernum)
    {
        $get['users_id'] = $uid;
        $get['order_num'] = $ordernum;
        $in = T('order')->get_one($get);
        if (!$in) {
            Out::jerror('订单不存在', null, 10210);
        }
        if ($in['pay_status'] == 1) {
            Out::jerror('订单已完成', null, 1);
            Out::jerror('订单已完成', null, 10220);
        }
        if ($in['pay_status'] == 2) {
            Out::jerror('订单已取消', null, 10221);
        }
        return $in;
    }
    public function queuechecksuborder($uid, $ordernum)
    {
        $get['users_id'] = $uid;
        $get['order_num'] = $ordernum;
        $in = T('order')->get_one($get);
        if (!$in) {
            return array(false, '订单不存在');
        }
        if ($in['pay_status'] == 1) {
            return array(false, '订单已完成');

        }
        if ($in['pay_status'] == 2) {
            return array(false, '订单已取消');

        }
        return array(true, '订单完成');
    }
    public function checkthirdid($thirdid)
    {
        if (!$thirdid) {
            return false;
        }
        $where = ['thirdpayid' => $thirdid];
        if (T('n_paytmp')->get_one($where)) {
            Out::jerror('第三方订单已效验过了' . $errmsg, null, '10116');
        }
        $where['time'] = time();
        return T('n_paytmp')->add($where);
    }
    public function backvip($uid, $ordernum, $transaction_id, $servertime, $productid)
    {

        $arr['trade_num'] = $transaction_id;
        $arr['pay_status'] = 1;
        $arr['pay_time'] = date('Y-m-d H:i:s');
        $arr['local_time'] = date('Y-m-d H:i:s');
        T('order')->update($arr, ['order_num' => $ordernum]);
        $order = T('order')->get_one(['order_num' => $ordernum, 'pay_status' => 1]);
        //订单状态更新
        //vip延期或者开通
        //$time=$order['viplong'];//月份
        if ($productid == 'com_vip_half_year') {
            $time = 6;
        }
        if ($productid == 'com_vip_one_year') {
            $time = 12;
        }
        //$viplevel=$order['viplevel'];
        $w = ['id' => $uid];
        $userinfo = T('third_party_user')->get_one($w);
        $update['isvip'] = 1;
        $update['open_vip_time'] = date("Y-m-d H:i:s", time());
        $vtime = strtotime($userinfo['vip_end_time']);
        if (time() >= $vtime) {
            //新的vip时间
            $viptime = strtotime("+$time month");
            $update['vip_end_time'] = date("Y-m-d H:i:s", $viptime);

        } else {
            //累加
            $viptime = strtotime("+$time month", $vtime);
            $update['vip_end_time'] = date("Y-m-d H:i:s", $viptime);
        }
        $update['viptime'] = $viptime;
        $paytype = [4 => 'android', 5 => 'iphone', 1 => 'wap'];
        m('count', 'im')->dayrecharge($userinfo, $order['fact_price'], null, $paytype[$order['pay_type']]);
        //M('order', 'im')->chargelog($uid, $cztype, $recharge['recharge_price'], 0, 0);
        $this->chargelog($ordernum);
        T('third_party_user')->update($update, $w);
        return true;

    }
    public function serverbackvip($ordernum, $oldtransaction_id, $transaction_id, $product_id)
    {
        $arr = T('order')->set_where(['trade_num' => $oldtransaction_id, 'pay_status' => 1])->get_one();
        if (!$arr) {
            Out::jerror('未找到旧订单', null, '10231');
        }
        $ordernum = $arr['order_num'];
        $uid = $arr['users_id'];
        $arr['book_name'] = 1;
        $arr['cartoon_name'] = $original_transaction_id;
        $arr['trade_num'] = $transaction_id;
        $arr['create_syntony'] = $product_id;

        $arr['trade_num'] = $transaction_id;
        $arr['pay_status'] = 1;
        $arr['order_num'] = $ordernum . date('Ymd') . intval(rand(1, 9999));
        $arr['pay_time'] = date('Y-m-d H:i:s');
        $arr['local_time'] = date('Y-m-d H:i:s');
        unset($arr['order_id']);
        T('order')->add($arr);
        //订单状态更新
        //vip延期或者开通
        //$time=$order['viplong'];//月份
        if ($product_id == 'com_vip_half_year') {
            $time = 6;
            $cztype = 4;
        }
        if ($product_id == 'com_vip_one_year') {
            $time = 12;
            $cztype = 5;
        }
        //$viplevel=$order['viplevel'];
        $w = ['id' => $uid];
        $userinfo = T('third_party_user')->get_one($w);
        $update['isvip'] = 1;
        $update['open_vip_time'] = date("Y-m-d H:i:s", time());
        if (time() >= $userinfo['viptime']) {
            //新的vip时间
            $update['viptime'] = strtotime("+$time month");
        } else {
            //累加
            $update['viptime'] = strtotime("+$time month", $userinfo['viptime']);
        }
        $update['vip_end_time'] = date("Y-m-d H:i:s", $update['viptime']);
        $update['viptime'] = $update['viptime'];
        $paytype = [4 => 'android', 5 => 'iphone', 1 => 'wap'];
        m('count', 'im')->dayrecharge($userinfo, $arr['fact_price'], null, $paytype[$arr['pay_type']], 1);
        T('third_party_user')->update($update, $w);
        //充值记录
        //M('order', 'im')->chargelog($uid, $cztype, $recharge['recharge_price'], 0, 0);
        $this->chargelog($arr['order_num']);
        M('order', 'im')->s2s($ordernum);
        return $uid;
    }
    private function chargelog($ordernum)
    {
        if (!$ordernum) {
            return false;
        }

        $arr = T('order')->set_where(['order_num' => $ordernum, 'pay_status' => 1])->get_one();
        if (!$arr) {
            return false;
        }
        $uid = $arr['users_id'];
        $product_id = $arr['create_syntony'];
        $money = $arr['fact_price'];
        if ($product_id == 'com_vip_half_year') {

            $cztype = 4;
        }
        if ($product_id == 'com_vip_one_year') {

            $cztype = 5;
        }

        M('order', 'im')->chargelog($uid, $cztype, $money, 0, 0);
    }
}
