<?php

namespace ng169\control;

use ng169\control\general;
use ng169\lib\Lang;
use ng169\service\Input;
use ng169\tool\Out;
use ng169\tool\Request;
use ng169\TPL;
use ng169\Y;

checktop();
im(CONTROL . 'public/night.php');
class apibase extends general
{
    private $pagesize = 15;
    public $tpl_path = 'tpl/api/';
    public $head = '';
    public $redis = '';
    public $countryid = '';
    public $conftype = '';
    protected $noNeedLogin = []; //默认全部要登入
    protected $allowedDeviceTypes = ['wxapp', 'wap', 'iphone', 'android'];
    public $uid = '';
    public $users_id;
    public $token = '';
    protected $DeviceTypesSecret = [
        'wxapp' => ['key' => '4c917b5d90a5732cf34e7e5545138f9c', 'secret' => 'dbc0fc07525b37772d47303c1b3d7d98'],
        'wap' => ['key' => 'd621b33de3cfa050c7bb8614d6ad50ea', 'secret' => '8a8b79104e3a3695c8b0e06db8a9e5b0'],
        'iphone' => ['key' => '755975d21db2ada29e3b279897caffb9', 'secret' => '47281f0bf8bcf5f62cb5190d9e004d7f'],
        'android' => ['key' => '9b4af02fddc12d2a38e2deae747beff0', 'secret' => '35ffc40f96f9e129f59c63ca6732578b'],
    ];
    public function _getuserid()
    {
      
        $userid = parent::$wrap_user['uid'];
        if ($userid == null) {
        }
        return $userid;
    }
    public function __construct()
    {
       
        $c = D_MEDTHOD;
        $a = D_FUNC;
        $this->init_head();
        //$this->_checkSign();//验证签名

        //$this->redis = Rediscache::getRedis();
        if (!in_array('*', $this->noNeedLogin)) {
            if (!in_array($a, $this->noNeedLogin)) {
                $login = $this->checkLogin();
            } else {
                $login = $this->checkLogin(false);
            }
        } else {
            $login = $this->checkLogin(false);
        }
        $this->init_country();
        $var_array = array(
            'user' => parent::$wrap_user,
            'time' => time(),
            'indextpl' => PATH_URL . $this->tpl_path,
            'realindextpl' => ROOT . $this->tpl_path,
        );
        $this->conftype = include CONF . '/type.php';

        //  $this->log('请求记录');
        TPL::assign($var_array);
    }
    /**
     * 设置或者获取当前的Header
     * @access public
     * @param string|array  $name header名称
     * @param string        $default 默认值
     * @return array
     */
    public function header($name = '', $default = null)
    {
        if (empty($this->header)) {
            $header = [];
            if (function_exists('apache_request_headers') && $result = apache_request_headers()) {
                $header = $result;
            } else {
                $server = $this->server ?: $_SERVER;
                foreach ($server as $key => $val) {
                    if (0 === strpos($key, 'HTTP_')) {
                        $key = str_replace('_', '-', strtolower(substr($key, 5)));
                        $header[$key] = $val;
                    }
                }
                if (isset($server['CONTENT_TYPE'])) {
                    $header['content-type'] = $server['CONTENT_TYPE'];
                }
                if (isset($server['CONTENT_LENGTH'])) {
                    $header['content-length'] = $server['CONTENT_LENGTH'];
                }
            }
            $this->header = array_change_key_case($header);
        }
        if (is_array($name)) {
            return $this->header = array_merge($this->header, $name);
        }
        if ('' === $name) {
            return $this->header;
        }
        $name = str_replace('_', '-', strtolower($name));
        return isset($this->header[$name]) ? $this->header[$name] : $default;
    }
    private function init_head()
    {

        //  $data = function_exists('getallheaders') ? \getallheaders() : $this->header();
        $data =  $this->header();
        //        $key=['mobileos','mobiletype','ip','appversion','language','token'];
        $key = array_keys($data);
        $ret = new Input(array('string' => $key), [], $data);
        $this->head = $ret->get();
        parent::$wrap_head = $this->head;
        foreach ($this->head as $i => $v) {
            $this->head[strtolower($i)] = $v;
        }
    }
    private function init_country()
    {


        $lang = strtolower(@$this->head['lang']);
        if (!$lang) {
            $lang = $this->head['accept-language'];

            $this->head['rate'] = 1;
            // return false;
        }

        $lang = substr($lang, 0, 2);

       
        $in = M('city',"im")->getinfo($lang);
        if (!$in) {
            $this->head['cityid'] = 0;
            $this->head['rate'] = 1;
            return false;
        }
        $this->head['cityid'] = $in['cityid'];
        $this->head['rate'] = $in['moneyrate'];
        //现阶段所有用户定位泰国数据
        // $this->head['cityid'] = 0;
        // $lang = strtolower(@$this->head['language']);
        // if ($lang) {
        //     $country = T('country')->get_one(['alias' => $lang, 'flag' => 0]);
        //     if ($country) {
        //         $this->countryid = $country['countryid'];
        //         parent::$wrap_city = $country['countryid'];
        //     }
        // }
        Lang::init($lang);
        Lang::load();
        return true;
    }
    public function returnSuccess($data)
    {
        Out::jout($data);
    }
    public function checkLogin($bool = true)
    {
        $get = get(array(['string' => ['token'], 'int' => ['uid']]));
        $token = @$this->head['token'];
        $uid = @$this->head['uid'];
        $user = M('user', 'im')->checktoken($uid, $token);

        if (!$user) {
            if ($bool) {
                Out::jerror('用户未登入', null, 100110);
            }
        } else {
            if ($user['status'] != 1) {
                Out::jerror('用户已被封禁，请联系客服', null, 100120);
            }
            //更新下用户信息
            // if (!$user['idfa'] || !$user['version']) {
            if ($user['version'] != $this->head['version'] || $user['city'] != $this->head['lang']) {
                $userData = [
                    'last_login_ip' => Request::getip(),
                    'third_party' => $this->head['devicetype'],
                    'deviceToken' => $this->head['idfa'],
                    'version' => $this->head['version'],
                    'last_login_time' => time(),
                    // 'city' =>  $this->head['lang'],
                ];
                if ($this->head['lang']) {
                    $userData['city'] = $this->head['lang'];
                }
                T('third_party_user')->update($userData, ['id' => $user['id']]);
                // }
            }
        }
        Y::$wrap_user = $user;

        $this->uid = isset($user['uid']) ? $user['uid'] : '';
        $this->users_id = isset($user['uid']) ? $user['uid'] : '';
        return $user;
    }

    protected function _checkSign()
    {

        $apiKey = $this->head['apikey'];
        $apiSign = $this->head['apisign'];
        $timestamp = $this->head['timestamp'];
        $token = $this->head['token'];
        $apiVersion = $this->head['version'];
        $deviceType = $this->head['devicetype'];
        $deviceToken = $this->head['devicetoken'];

        if (!$apiKey) {
            Out::jerror('apiKey不能空', null, '10003');
        }
        if (!$apiSign) {
            Out::jerror('apiSign不能为空', null, '10004');
        }
        if (!$timestamp) {
            Out::jerror('timestamp不能为空', null, '10005');
        }

        if (!in_array($deviceType, $this->allowedDeviceTypes)) {
            Out::jerror('deviceType不正确', null, '10006');
        }
        if ($this->DeviceTypesSecret[$deviceType]['key'] != $apiKey) {
            Out::jerror('apiKey不正确', null, '10007');
        }
        $secret = $this->DeviceTypesSecret[$deviceType]['secret'];

        //获取所有请求的参数

        $AllPar['apiKey'] = $apiKey;
        $AllPar['timestamp'] = $timestamp;
        $AllPar['deviceType'] = $deviceType;
        $AllPar['version'] = $apiVersion;
        $AllPar['tokens'] = $token;
        $AllPar['deviceToken'] = $deviceToken;
        $AllPar = (array_filter($AllPar)); //根据键对数组进行升序排序
        ksort($AllPar);
        $hash_data = '';
        foreach ($AllPar as $k => $v) {

            $hash_data .= htmlspecialchars_decode($v);
        }
        //  d($AllPar);
        // d($hash_data);
        //  d($secret);
        $_apiSign = hash_hmac('md5', $hash_data, $secret);
        if ($_apiSign != $apiSign) {
            Out::jerror('apiSign不正确', null, '10008');
        }
    }
}
