<?php

namespace ng169\model\index;

use ng169\tool\Curl;
use ng169\tool\Out;
use ng169\Y;

checktop();

class fb extends Y
{

    private $fbconf = '';
    private $fbobj = '';
    private function init()
    {
        //加载facebook类库
        im(API . '/Facebook/autoload.php');
        //开启session
        if (!session_id()) {
            session_start();
        }
        $this->fbconf = include CONF . 'facebook.inc.php';
        $this->fbobj = new \Facebook\Facebook([
            'app_id' => $this->fbconf['main']['app_id'], // Replace {app-id} with your app id
            'app_secret' => $this->fbconf['main']['app_secret'],
            'default_graph_version' => 'v3.2',
        ]);

    }

    //获取facebook链接
    public function geturl()
    {
        $this->init();
        $helper = $this->fbobj->getRedirectLoginHelper();
        $permissions = ['email']; // Optional permissions
        $loginUrl = $helper->getLoginUrl($this->fbconf['main']['LoginUrl'], $permissions);
        return $loginUrl;
    }
    //获取facebook token
    public function getfbtoken()
    {
        $this->init();
        $helper = $this->fbobj->getRedirectLoginHelper();

        try {

            $accessToken = $helper->getAccessToken();

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {

            Out::jerror('Graph returned an error: ' . $e->getMessage());
        } catch (\Facebook\Exceptions\FacebookSDKException $e) {

            Out::jerror('Facebook SDK returned an error: ' . $e->getMessage());
        }

        if (!isset($accessToken)) {
            if ($helper->getError()) {

                $str = '';
                $str .= "Error: " . $helper->getError() . "\n";
                $str .= "Error Code: " . $helper->getErrorCode() . "\n";
                $str .= "Error Reason: " . $helper->getErrorReason() . "\n";
                $str .= "Error Description: " . $helper->getErrorDescription() . "\n";
                Out::jerror($str);
            } else {

                Out::jerror('Bad request');
            }
            exit;
        }

        return $accessToken->getValue();

    }
    //获取facebook 用户信息
    public function getinfo($token)
    {
        $this->init();

        try {
            // Returns a `Facebook\FacebookResponse` object

            $response = $this->fbobj->get('/me?fields=id,name,email,age_range,first_name,last_name,birthday,link,gender,locale,picture,timezone,updated_time,verified', $token);

        } catch (\Facebook\Exceptions\FacebookResponseException $e) {
            Out::jerror('Graph returned an error: ' . $e->getMessage());

        } catch (\Facebook\Exceptions\FacebookSDKException $e) {
            Out::jerror('Graph returned an error: ' . $e->getMessage());

        }
        $userinfo = $response->getGraphUser(); //用户资料

        $fbuid = $userinfo['id']; //用户id
        return $userinfo;
        /*d($userinfo);*/
        /*if(!M('user','im')->fbisuser($fbuid)){
    //注册用户
    M('user','im')->newfbuser($fbuid,$userinfo);
    }*/
    }
    /**
     *
     * @param undefined $advertiser_id 用户识别ID
     * @param undefined $money 金额
     * @param undefined $currency 货币类型
     * @param undefined $desc  备注
     *
     * @return
     */
    //$this->call($user_re['deviceToken'],$recharge['yuenan_icon'],'IDR',$this->users_id,$data['order_num']);
    public  function s2s($advertiser_id, $money, $currency, $users_id, $order_num, $paytype)
    {
        return false;
        // Log::init([
        //     'type'  =>  'File',
        //     'path'  =>  APP_PATH.'../log/'
        // ]);
        // $res = ['status'=>1];
        $appid = '642299609555471';
        $token = '642299609555471|WdhhMo-H2M7qD58XKLy4hLR65xE';
        $advertiser_id = $advertiser_id; //用户识别ID
        switch ($paytype) {
			case '4':
			$paystring='googlePlay';
                break;
			case '5':
			$paystring='applePay';
                break;
			default:
			$paystring='otherPay';
                break;
        }

        $param = [
            '_eventName' => 'fb_mobile_purchase', //根据事件定义：购买为fb_mobile_purchase
            // 'fb_content_type'=>$desc,//APP内用户ID，订单ID，支付方式
            '_valueToSum' => $money, //"金额"
            'fb_currency' => $currency, //货币代码ISO
            "userId" => $users_id, //APP内用户ID
            "payid" => $order_num, //订单ID
            "channel" => $paystring, //"googlePlay"    //支付方式
        ];
        $url = 'https://graph.facebook.com/v4.0/' . $appid . '/activities?access_token=' . $token;
        $curl = new Curl();
        $event = json_encode($param);
        $data = [
            'advertiser_id' => $advertiser_id
            , 'advertiser_tracking_enabled' => 1
            , 'application_tracking_enabled' => 1
            , 'custom_events' => '[' . $event . ']'
            /*,debug=>all*/
            , 'event' => 'CUSTOM_APP_EVENTS', 'format' => 'json', 'pretty' => 0
            , 'suppress_http_code' => 1
            , 'ransport' => 'cors',
        ];
		$ret = $curl->post($url, $data);
		return $ret;
       // Log::record(json_encode($ret), 'errors');
    }

}
