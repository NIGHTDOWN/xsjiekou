<?php

namespace ng169\model\index;

use ng169\lib\Log;
use ng169\tool\Out;
use ng169\Y;

checktop();
class PayType
{
    // 支付宝内购
    const alipay = 'alipay';
    // 支付宝小程序
    const alipay_lite = 'alipay_lite';
    // 支付宝网页支付
    const alipay_wap = 'alipay_wap';
    // 微信小程序
    const wx_lite = 'wx_lite';
}
class adapaytest extends Y
{
    private $pre = 'bc_';
    //分账成员id前缀
    private $mid_pre = 'bc';
    private $adapay_appid = 'app_79599ae6-11d6-40d9-b822-144ed60417a2';
    private $callurl = '';

    private $banks;
    private  $provinceids;
    public function getpre()
    {
        return $this->pre;
    }
    // 初始化
    private function init()
    {
        // $this->callurl =  Url::getadd() . '/api/pay/callback';
        $this->callurl = '/api/pay/callback';

        $this->banks = include('adapay_bank.php');
        $this->provinceids = include('adapay_province.php');
        im(API . '/adapay/AdapaySdk/init.php');
        \AdaPay\AdaPay::init(CONF . '/ada.json', "live", false);
    }
    private function createOurOrder($orderids, $title, $desc, $cost, $call, $pay_type)
    {
        $get = get(['string' => 'attr']);
        $insert = [
            'addtime' => time(),
            'orders_id' => $orderids,
            'title' => $title,
            'pay_money' => $cost * 100,
            'desc' => $desc,
            'callurl' => $call,
            'pay_type' => $pay_type,
            'attr' => $get['attr']
        ];
        $payid = T('pay')->add($insert);
        if ($payid) {
            return $payid;
        }
        return false;
    }

    public function create($callbackurl,$uid, $pid, $bookid = 0, $booktype = 0, $sid = 0, $active_id = 1)
    {
        $this->init();
        # 初始化支付类
        $this->callurl=$callbackurl;
        $paytype = 'alipay';
        $pay_type_id = 8;
        $payment = new \AdaPaySdk\Payment();
        
        // $payid = $this->createOurOrder($orderids, $title, $desc, $cost, $call, $paytype);
        $ourorder = M('order', 'im')->create($uid,  $pid, $pay_type_id, $bookid, $booktype, $sid, $active_id);
        if (!$ourorder) {
            return false;
        }
        $order_no = $ourorder['order_num'];
        $cost = number_format($ourorder['price'], 2);
        $cost = '0.01';
        $mid = T('paymerber')->order_by(['s' => 'down', 'f' => 'mid'])->field('mid')->set_where(['flag' => 1])->get_one();
        
        if (!$mid) {
            $mid = $this->try_add_merber();
            if (!$mid) {
                Out::jerror(__('支付成员创建失败'));
            }
        } else {
            $mid = $mid['mid'];
        }
        // $payid = $orderids;
        # 支付设置
        $payment_params = array(
            'app_id' => $this->adapay_appid,
            'order_no' => $this->pre . $order_no,
            'pay_channel' => $paytype,
            'time_expire' => date("YmdHis", time() + 86400),
            'pay_amt' => $cost,
            'goods_title' => $cost . __('套餐'),
            'goods_desc' => $cost . __('套餐'),
            'description' => $cost . __('套餐'),
            'notify_url' => $this->callurl,
            'div_members' => $this->getdiv($cost, $mid),
            'fee_mode' => 'I',
            // 'currency' => 'usd'
        );

        // currency這個參數好像無效;
        # 发起支付
        
        $payment->create($payment_params);
        $ret = $this->getret($payment->result);
       
        if ($ret) {
            //更新数据库
            T('order')->update(['pay_syntony' => $ret['party_order_id']], ['order_id' =>  $ourorder['id']]);
        }
        return $ret;
    }
    public function addmerber($member_id, $tel_no)
    {

        $this->init();
        $member = new \AdaPaySdk\Member();

        $member_params = array(
            # app_id
            'app_id' => $this->adapay_appid,
            # 用户id
            'member_id' => $member_id,
            # 用户地址
            'location' => '广东省广州市白云区币云跳动',
            # 用户邮箱
            'email' => $member_id . '@bytd.link',
            # 性别
            'gender' => 'MALE',
            # 用户手机号
            'tel_no' => $tel_no,
            # 用户昵称
            'nickname' => $member_id,
        );
        # 创建
        $member->create($member_params);

        if ($member->isError()) {

            return false;
        } else {
            return true;
        }
    }

    public function addcommerber(

        $name,
        $namenum,
        $realname,
        $realnamenum,
        $realnamenumtime,
        $nametime,
        $address,
        $business_scope,
        $province,
        $city,
        $tel_no,
        $bankname,
        $cardid,
        $attach_file
    ) {
        $type = 1;
        $member_id = T('paymerber')->add(['phone' => $tel_no, 'sex' => $type]);
        $this->init();
        $corp_member = new \AdaPaySdk\CorpMember();
        $file_real_path = realpath(ROOT . $attach_file);
        $prov_code = $this->getprivinceid($province);
        $area_code = $this->getcityid($city);
        $bankid = $this->getbankid($bankname);
        $member_params = array(
            # app_id
            'app_id' =>
            $this->adapay_appid,
            # 商户用户id
            'member_id' => $member_id,
            # 订单号
            'order_no' => date("YmdHis") . rand(100000, 999999),
            # 企业名称
            'name' => $name,
            # 省份
            'prov_code' => $prov_code,
            # 地区
            'area_code' => $area_code,
            # 统一社会信用码
            'social_credit_code' => $namenum,
            'social_credit_code_expires' => $nametime,
            # 经营范围
            'business_scope' => $business_scope,
            # 法人姓名
            'legal_person' => $realname,
            # 法人身份证号码
            'legal_cert_id' => $realnamenum,
            # 法人身份证有效期
            'legal_cert_id_expires' => $realnamenumtime,
            # 法人手机号
            'legal_mp' => $tel_no,
            # 企业地址
            'address' => $address,
            # 邮编
            'zip_code' => '',
            # 企业电话
            'telphone' => $tel_no,
            # 企业邮箱
            'email' =>
            $member_id . '@bytd.link',
            # 上传附件
            'attach_file' => new \CURLFile($file_real_path),
            # 银行代码
            'bank_code' => $bankid,
            # 银行账户类型
            'bank_acct_type' => '1',
            'card_name' => $name,
            'card_no' => $cardid,
            'notify_url' => ''
        );
        # 创建企业用户
        $corp_member->create($member_params);
        # 对创建企业用户结果进行处理
        if ($corp_member->isError()) {
            //失败处理
            return false;
        } else {
            //成功处理
            return $member_id;
        }
    }
    private function getbankid($bankname)
    {


        $rex = '/,(\d{8})=>.*' . $bankname . '.*/';
        preg_match_all($rex, $this->banks, $matches);

        if ($matches[1][0]) {
            return $matches[1][0];
        }
    }
    public function addpaycard($merberid, $type, $name, $usernum, $bankname, $cardnum, $tel, $province, $city)
    {
        $this->init();
        $account = new \AdaPaySdk\SettleAccount();
        $prov_code = $this->getprivinceid($province);
        $area_code = $this->getcityid($city);
        $bankid = $this->getbankid($bankname);
        $account_params = array(
            'app_id' =>
            $this->adapay_appid,
            'member_id' => $merberid,
            'channel' => 'bank_account',
            'account_info' => [
                'card_id' => $cardnum,
                'card_name' => $name,
                'cert_id' => $usernum,
                'cert_type' => '00',
                'tel_no' =>  $tel,
                'bank_code' =>  $bankid,
                'bank_name' => $bankname,
                'bank_acct_type' => $type == 1 ? 1 : 2,
                'prov_code' => $prov_code,
                'area_code' => $area_code,
            ]
        );
        # 创建结算账户
        $account->create($account_params);
        # 对创建结算账户结果进行处理
        if ($account->isError()) {
            //失败处理
            // var_dump($account->result);
            return false;
        } else {
            //成功处理
            $ret = $account->result;
            return $ret['id'];
        }
    }
    private function getprivinceid($name)
    {
        $rex = '/value:(\d{4}),' . $name . '/';
        preg_match_all($rex, $this->provinceids, $matches);
        if ($matches[1][0]) {
            return $matches[1][0];
        }
    }
    private function getcityid($name)
    {
        $rex = '/value:(\d{4}),' . $name . '/';
        preg_match_all($rex, $this->provinceids, $matches);

        if ($matches[1][1] && sizeof($matches[1]) >= 2) {
            return $matches[1][1];
        }
        if ($matches[1][0]) {
            return $matches[1][0];
        }
    }
    //重新添加分账账号
    public function try_add_merber()
    {
        $name = '杨志伟';
        $tel = '13112234215';
        $usernum = '44028119911220131X';
        $cardid = '6214832053271966';
        $bankname = '招商银行';
        $bankprovice = '广东省';
        $bankcity = '广州';
        $type = 2;
        $bool = $this->addrecvuser($type, $name, $tel, $usernum, $bankname, $cardid, $bankprovice, $bankcity);
        return $bool;
    }
    //添加分账账户
    public function addrecvuser($type, $name, $tel, $usernum, $bankname, $cardid, $bankprovice, $bankcity)
    {
        // $payobj = M('adapay', 'im');
        $payobj = $this;
        $mid = T('paymerber')->add(['phone' => $tel, 'sex' => $type]);
        $mmid = $this->mid_pre . $mid;
        $bool = $payobj->addmerber($mmid, $tel);

        if ($bool) {
            $accid = $payobj->addpaycard($mmid, $type, $name, $usernum, $bankname, $cardid, $tel, $bankprovice, $bankcity);

            if ($accid) {
                T('paymerber')->update(['flag' => 1], ['mid' => $mid]);
                return $mid;
            }
        }
        return false;
    }
    public function createwx($orderids, $title, $desc, $cost, $openid, $call, $mid)
    {
        $this->init();
        # 初始化支付类
        $paytype = 'wx_pub';
        $payment = new \AdaPaySdk\Payment();
        $payid = $this->createOurOrder($orderids, $title, $desc, $cost, $call, $paytype);

        # 支付设置
        $payment_params = array(
            'app_id' => $this->adapay_appid,
            'order_no' => $this->pre . $payid,
            'pay_channel' => $paytype,
            'time_expire' => date("YmdHis", time() + 86400),
            'pay_amt' => $cost,
            'goods_title' => $title,
            'goods_desc' => $desc,
            'description' => $desc,
            'notify_url' => $this->callurl,
            'div_members' => $this->getdiv($cost, $mid),
            'fee_mode' => 'I',
            'expend' => [
                "open_id" => $openid,
                "is_raw" => "1",
            ]
        );

        # 发起支付
        $payment->create($payment_params);
        $ret = $this->getret($payment->result);
        if ($ret) {
            //更新数据库
            $ret['order_no'] = $this->pre . $payid;
            T('pay')->update(['thirdpayid' => $ret['party_order_id']], ['payid' => $payid]);
        }
        return $ret;
    }
    public function getdiv($cash, $mid)
    {
        $obj = [];
        // $cash0 = 0;

        // $size = sizeof($obj);
        // $cash = round($cash - $cash0, 2);
        // if ($type) {
        //     $obj2 = ['member_id' => $this->recvid[0], 'amount' => $cash, "fee_flag" => $size ? "N" : 'Y'];
        // } else {
        //     $obj2 = ['member_id' => $this->recvid[1], 'amount' => $cash, "fee_flag" => $size ? "N" : 'Y'];
        // }
        $mmid = $this->mid_pre . $mid;
        $obj2 = ['member_id' => $mmid, 'amount' => $cash, "fee_flag" =>  'Y'];
        array_push($obj, $obj2);
        $obj = json_encode($obj);
        return $obj;
    }
    public function createwxlite($orderids, $title, $desc, $cost, $openid, $call, $mid)
    {
        $this->init();
        # 初始化支付类
        $paytype = 'wx_lite';
        $payment = new \AdaPaySdk\Payment();
        $payid = $this->createOurOrder($orderids, $title, $desc, $cost, $call, $paytype);
        # 支付设置
        $payment_params = array(
            'app_id' => $this->adapay_appid,
            'order_no' => $this->pre . $payid,
            'pay_channel' => $paytype,
            'time_expire' => date("YmdHis", time() + 86400),
            'pay_amt' => $cost,
            'goods_title' => $title,
            'goods_desc' => $desc,
            'description' => $desc,
            'notify_url' => $this->callurl,
            'div_members' => $this->getdiv($cost, $mid),
            'fee_mode' => 'I',
            'expend' => [
                "open_id" => $openid,
                "is_raw" => "1",
            ]
        );

        # 发起支付
        $payment->create($payment_params);
        $ret = $this->getret($payment->result);
        if ($ret) {
            //更新数据库
            $ret['order_no'] = $this->pre . $payid;
            T('pay')->update(['thirdpayid' => $ret['party_order_id']], ['payid' => $payid]);
        }
        return $ret;
    }
    private function getretdata($ret)
    {
        $r = [];
        if ($ret[0] == '200') {
            //成功
            $data = json_decode($ret[1], 1);
            $data = json_decode($data['data'], 1);


            return $data;
        } else {
            //失败
            // $data = json_decode($ret[1]);
            //创建订单错误，记录文本日志
            Out::jerror($ret);
            Log::txt($ret);
        }
        return false;
    }
    public function getret($ret)
    {
        $ret = $this->getretdata($ret);
        $r = [];
        if ($ret) {
            $r['party_order_id'] = $ret['id'];
            $r['money'] = $ret['pay_amt'];
            $r['pay_channel'] = $ret['pay_channel'];
            // $r['query_url'] = $ret['query_url'];
            $r['pay_url'] = $ret['expend']['pay_info'];

            return $r;
        }

        return false;
    }
    //回调验签
    public function verifySign($data, $signature)
    {
        $this->init();
        $adapay_tools = new \AdaPaySdk\AdapayTools();
        return  $adapay_tools->verifySign($data, $signature);
    }
    //主动查询支付状态
    public function query($orderid)
    {
        $this->init();
        $payment = new \AdaPaySdk\Payment();
        $payid = str_replace($this->pre, '', $orderid);
        $w = ['payid' => $payid];
        $order = T('pay')->get_one($w);
        if (!$order) {
            return false;
        }
        if ($order['paystatus'] == 1) {
            return
                true;
        }
        # 支付设置
        $payment_params = array(
            "payment_id" => $order['thirdpayid'],
        );
        # 发起支付
        $payment->query($payment_params);
        $ret = $this->getretdata($payment->result);
        if ($ret['status'] == 'succeeded') {
            $ret = $this->sureOrder($orderid);
            return true;
        }
        return false;
    }
    //确定订单完成
    public function sureOrder($orderid)
    {

        $payid = str_replace($this->pre, '', $orderid);
        $w = ['payid' => $payid];
        $order = T('pay')->get_one($w);
        if (!$order) {
            return false;
        }
        $order['pay_money'] = $order['pay_money'] / 100;
        if ($order['paystatus'] == 1) {
            return
                $order;
        }
        $f = T('pay')->update(['paystatus' => 1], $w);
        if ($f) {
            $order['paystatus'] = 1;
            return  $order;
        }
        return false;
    }
}
