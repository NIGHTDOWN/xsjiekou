<?php

namespace ng169\control\api;

use ng169\control\apibase;
use ng169\tool\Out;
use ng169\tool\Request;

checktop();
class login extends apibase
{

    protected $noNeedLogin = ['*'];

    public function control_run()
    {
        $get = get(['string' => [
            'uid' => 1, 'nickname', 'icon', 'version', 'sex',
            'login_type', 'sharetype', 'channel_id', 'invite_code', 'plat_id', 'deviceToken',
        ]]);
        if (\sizeof($get) <= 0) {
            Out::jerror('获取参数失败', null, '10001');
        }
        if (!$get['deviceToken']) {
            $get['deviceToken'] = 0;
        }
        $user = $findThirdPartyUser = T("third_party_user")
            ->where(['openid' => $get['uid']])
            ->get_one();
        $ip = Request::getip();
        if ($user) {
            //老用户
            if ($user['deviceToken'] != $get['deviceToken']) {
                T('user_token')->update(['token' => ''], ['user_id' => $user['id']]);
            }
            $this->uid = $user['id'];
            $this->token = M('user', 'im')->gettoken($this->uid, $get['devicetype']);
            $userData = [
                'last_login_ip' => $ip,
                'third_party' => $this->head['Devicetype'],
                'deviceToken' => $this->head['Devicetoken'],
                // 'last_login_time' => $currentTime,
            ];

            T('third_party_user')->update($userData, ['id' => $user['id']]);
            // M('census', 'im')->dayregcount();//每日注册量统计
        } else {
            if ($this->head['devicetype'] == 'iphone') {

                $inv = T('user_invite')->get_one(['ip' => Request::getip()]);
                $pid = $inv['u_id'];
            } else {
                $pid = $get['channel_id'];
            }

            $ret = M('user', 'im')->newuser($get['uid'], $get['nickname'], $get['icon'], $get['sex'], $get['login_type'], $get['deviceToken'], $get['sharetype'], $pid);

            if ($ret && is_array($ret)) {
                $this->uid = $ret[0];
                $this->token = $ret[1];
            } else {
                Out::jerror('注册失败', null, '100152');
            }
        }
        $user = T('third_party_user')
            // ->set_field('id,remainder,openid,avater,nickname,create_time')
            ->where(['id' => $this->uid])->get_one();
        $user['remainder'] = round($user['remainder'], 2);
        $user['token'] = $this->token;
        $user['uid'] = $user['id'];
        $user['vip_end_time'] = date('d/m/Y H:i:s', strtotime($user['vip_end_time']));
        Out::jout($user);
    }
    public function control_login()
    {
        $get = get(['string' => ['username' => 1]]);
        $get2 = get(['string' => ['password' => 'md5']]);
        $user  = T("third_party_user")
            ->where($get)
            ->get_one();
        if ($user['password'] != $get2['password']) {
            $user = false;
        }
        $ip = Request::getip();
        if ($user) {
            //老用户
            if ($user['deviceToken'] != $get['deviceToken']) {
                T('user_token')->update(['token' => ''], ['user_id' => $user['id'], 'device_type' => $get['devicetype']]);
            }
            $this->uid = $user['id'];
            $this->token = M('user', 'im')->gettoken($this->uid, $get['devicetype'], 1);
            $userData = [
                'last_login_ip' => $ip,
                'third_party' => $this->head['Devicetype'],
                'deviceToken' => $this->head['Devicetoken'],
                // 'last_login_time' => $currentTime,
            ];

            T('third_party_user')->update($userData, ['id' => $user['id']]);
            // M('census', 'im')->dayregcount();//每日注册量统计
        } else {
            Out::jerror('账号不存在', null, '110152');
        }
        $user = T('third_party_user')

            ->where(['id' => $this->uid])->get_one();
        $user['remainder'] = round($user['remainder'], 2);
        $user['token'] = $this->token;
        $user['uid'] = $user['id'];
        $user['vip_end_time'] = date('d/m/Y H:i:s', strtotime($user['vip_end_time']));
        Out::jout($user);
    }
    public function control_reg()
    {

        $get = get(['string' => ['nickname' => 1, 'username' => 1, 'password' => 'md5']]);
        $user = $findThirdPartyUser = T("third_party_user")
            ->where(['username' => $get['username']])
            ->get_one();
        $ip = Request::getip();
        if ($user) {
            //老用户
            Out::jerror('用户已存在', null, '90001');
        } else {
            if ($this->head['devicetype'] == 'iphone') {

                $inv = T('user_invite')->get_one(['ip' => Request::getip()]);
                $pid = $inv['u_id'];
            } else {
                $pid = $get['channel_id'];
            }

            $ret = M('user', 'im')->createuser($get['username'], $get['nickname'], $get['password']);

            if ($ret && is_array($ret)) {
                $this->uid = $ret[0];
                $this->token = $ret[1];
            } else {
                Out::jerror('注册失败', null, '100152');
            }
        }
        $user = T('third_party_user')
            // ->set_field('id,remainder,openid,avater,nickname,create_time')
            ->where(['id' => $this->uid])->get_one();
        $user['remainder'] = round($user['remainder'], 2);
        $user['token'] = $this->token;
        $user['uid'] = $user['id'];
        $user['vip_end_time'] = date('d/m/Y H:i:s', strtotime($user['vip_end_time']));
        Out::jout($user);
    }
    public function control_logout()
    {
        $userId = $this->get_userid();
        T('user_token')->update(['token' => ''], ['user_id' => $userId, 'device_type' => $this->head['devicetype']]);
        Out::jout('退出成功');
    }
    public function control_fburl()
    {
        //登入接口
        $loginUrl = M('fb', 'im')->geturl();
        Out::jout(['url' => ($loginUrl)]);
    }
}
