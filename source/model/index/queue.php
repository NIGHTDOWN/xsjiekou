<?php

namespace ng169\model\index;

use ng169\Y;

checktop();
class queue extends Y
{
    //打点
    public function point($ordernum, $type = 0, $data)
    {
        if (!$ordernum) {
            return false;
        }
        $insert['post'] = \json_encode($data);
        $insert['order_num'] = ($ordernum);
        $insert['addtime'] = time();
        $insert['type'] = $type;
        $insert['flag'] = 0;
        return T('n_payqueue')->add($insert);
    }
    //获取未完成的
    public function getlist()
    {
        //超时五分钟未完成的订单
        $where['flag'] = 0;
        $list = T('n_payqueue')->set_where($where)->set_where('addtime<=' . (time() - 300))->get_all();
        return $list;
    }
    //完成
    public function finsh($ordernum, $desc = null)
    {
        if (!$ordernum) {
            return false;
        }
        if (\is_array($desc)) {
            $desc = \json_encode($desc);
        }
        $where = ['order_num' => $ordernum];
        $up = ['flag' => 1, 'dealtime' => time(), 'desc' => $desc];
        return T('n_payqueue')->update($up, $where);
    }
    //close
    public function close($ordernum, $reson)
    {
        if (!$ordernum) {
            return false;
        }
        if (\is_array($reson)) {
            $reson = \json_encode($reson);
        }
        $where = ['order_num' => $ordernum];
        $up = ['flag' => 2, 'desc' => $reson, 'dealtime' => time()];
        return T('n_payqueue')->update($up, $where);
    }
    public function consume($ordernum, $type, $data)
    {
        //内购 订阅
        if (!$ordernum) {
            return false;
        }
        switch ($type) {
            case '0':
                # code...内购
                $this->buy($ordernum, $data);
                break;
            case '1':
                # code...订阅
                $this->sub($ordernum, $data);
                break;

        }

    }
    private function buy($ordernum, $data)
    {
       
        $data = \json_decode($data, 1);
        $check = M('google', 'im')->check($data['applepayId'], $data['purtoken'], 0);
        if ($check) {
            $tradenum = $check['thirdpayid'];
            $syntony = $check['productid'];
            $order_num = $check['ourorderid'];
            $ptime = date('Y-m-d H:i:s', time());
            $desc = M('order', 'im')->queuedeal($order_num, $tradenum, $syntony, $ptime);
            $this->finsh($order_num, $desc);
        } else {
            $this->close($ordernum, $check);
        }
        return true;
    }
    private function sub($ordernum, $data)
    {
        $get = \json_decode($data, 1);

        list($bool,$string) = M('pay', 'im')->queuechecksuborder($get['uid'], $ordernum);
        if(!$bool){
            $this->close($ordernum, $string);  
            return false;
        }
        $data = M('google', 'im')->check($get['product_id'], $get['purtoken'], 1);
        if ($data && $data['status'] == 1) {
            //支付成功
            if ($get['ordernum'] != $data['ourorderid']) {
                $data['status'] = 0;
                //Out::jerror('', $data, '10246');
                $this->close($ordernum, '订单校对失败');
                return false;
            }
            //        支付完成的回调
            M('pay', 'im')->backvip($get['uid'], $data['ourorderid'], $data['thirdpayid'], $data['paytime'], $get['product_id']);
            M('order', 'im')->s2s($get['ordernum']);
            M('queue', 'im')->finsh($get['ordernum']);
        } else {
            //支付失败
            M('queue', 'im')->finsh($ordernum);
            // Out::jerror('google支付失败', null, '10215');
        }
        return true;
    }
    public function task()
    {
        $list = $this->getlist();
        foreach ($list as $key => $value) {
            $this->consume($value['order_num'], $value['type'], $value['post']);
            # code...
        }
        return true;
    }
}
