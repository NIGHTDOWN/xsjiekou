<?php

namespace ng169\model\index;

use ng169\tool\Request;
use ng169\Y;

checktop();

class user extends Y
{
    /**获取用户token */
    public function gettoken($uid, $deviceType = null, $reflase = 0)
    {

        if (!$uid) {
            return false;
        }

        $userTokenQuery = T("user_token")
            ->where(['user_id' => $uid, 'device_type' => $deviceType]);
        $findUserToken = $userTokenQuery->get_one();
        $currentTime = time();
        $expireTime = $currentTime + 24 * 3600 * 180;
        $token = md5(uniqid()) . md5(uniqid());
        if (empty($findUserToken) && $reflase) {
            T("user_token")->add([
                'token' => $token,
                'user_id' => $uid,
                'expire_time' => $expireTime,
                'create_time' => $currentTime,
                'device_type' => $deviceType,
            ]);
        } else {
            if ($findUserToken['expire_time'] > time() && !empty($findUserToken['token'])) {
                $token = $findUserToken['token'];
            } else {
                // T("user_token")->update([
                //     'token' => $token,
                //     'expire_time' => $expireTime,
                //     'create_time' => $currentTime,
                // ], ['user_id' => $userId, 'device_type' => $deviceType]);
                T("user_token")->update([
                    'token' => $token,
                    'expire_time' => $expireTime,
                    'create_time' => $currentTime,
                ], ['user_id' => $uid]);
            }
        }

        return $token;
    }
    public function checktoken($uid, $token)
    {
        if (!$uid) {
            return false;
        }

        if (!$token) {
            return false;
        }
        // if (empty($this->_token)) {
        //     return;
        // }

        $tokens = T('user_token')->set_field('id')
            ->where(['token' => $token, 'user_id' => $uid])
            ->get_one();
        // d($token);
        if ($tokens) {
            $user = T('third_party_user')->set_field('id,status,nickname,more,remainder,invite_id,avater,sex,username,borth,version,locklang')->get_one(['id' => $uid]);
            $user['uid'] = $user['id'];
            return $user;
        }

        return false;
    }
    public function setparent($uid, $pid)
    {
        return T('third_party_user')->update(['invite_id' => $pid], ['id' => $uid]);
    }

    public function newuser($openid, $nickname, $icon, $sex, $login_type, $deviceToken, $type, $channel_id)
    {
        if (!$openid) {
            return false;
        }
        $ip = Request::getip();
        $currentTime = time();
        $invite_coin = self::$newconf['task'];
        $head = Y::$wrap_head;

        $user_id = T("third_party_user")->add([
            'openid' => $openid,
            'third_party' => $head['Devicetype'],
            'last_login_ip' => $ip,
            'last_login_time' => $currentTime,
            'create_time' => $currentTime,
            'login_times' => 1,
            'status' => 1,
            'nickname' => $nickname,
            //用户名称需要随机保证唯一性
            // 'username' => $nickname,
            'avater' => htmlspecialchars_decode($icon),
            'sex' => $sex,
            'login_type' => $login_type,
            'deviceToken' => $head['Devicetoken'],
            'plat' => $head['Devicetype'],
            'invite_id' => $channel_id,
        ]);

        if (!$user_id) {
            return false;
        }
        $username = "ls_user_" . $user_id;
        T('third_party_user')->update(['username' => $username], ['id' => $user_id]);
        // M('census', 'im')->Invitationreward($deviceToken, $user_id, $channel_id, $nickname);

        M('census', 'im')->_reg(); //注册统计
        if ($channel_id) {

            M('census', 'im')->inviterecharge($user_id, $channel_id, $invite_coin['invite_coin']); //邀请统计

            M('census', 'im')->_invitation(M('census', 'im')->formshare($channel_id)); //邀请统计

            M('census', 'im')->task_reward_count($channel_id, $invite_coin['invite_coin'], $invite_coin['invite_task']);
            M('coin', 'im')->change($channel_id, $invite_coin['invite_coin']);
        }
        $token = $this->gettoken($user_id, $head['devicetype']);
        return [$user_id, $token];
    }
    public function createuser($username, $nickname, $pwd, $devicetype = null)
    {

        $ip = Request::getip();
        $currentTime = time();
        $invite_coin = self::$newconf['task'];
        $head = Y::$wrap_head;
        if (!$devicetype) {

            $devicetype = $head['Devicetype'];
        }
        $user_id = T("third_party_user")->addid([
            'username' => $username,
            'third_party' =>  $devicetype,
            'last_login_ip' => $ip,
            'last_login_time' => $currentTime,
            'create_time' => $currentTime,
            'login_times' => 1,
            'status' => 1,
            'nickname' => $nickname,
            'password' => $pwd,
            // 'sex' => $sex,
            'login_type' => 0,
            'deviceToken' => $head['Devicetoken'],
            'plat' =>  $devicetype,
            'invite_id' => 0,
        ]);

        if (!$user_id) {
            return false;
        }
        // M('census', 'im')->Invitationreward($deviceToken, $user_id, $channel_id, $nickname);

        M('census', 'im')->_reg(); //注册统计
        $channel_id = 0;
        if ($channel_id) {

            M('census', 'im')->inviterecharge($user_id, $channel_id, $invite_coin['invite_coin']); //邀请统计

            M('census', 'im')->_invitation(M('census', 'im')->formshare($channel_id)); //邀请统计

            M('census', 'im')->task_reward_count($channel_id, $invite_coin['invite_coin'], $invite_coin['invite_task']);
            M('coin', 'im')->change($channel_id, $invite_coin['invite_coin']);
        }
        $token = $this->gettoken($user_id, $devicetype, 1);
        return [$user_id, $token];
    }
    public function ulog($u_id = '', $price = '', $trade_num = '', $order_num = '', $pay_time = '', $pay_type = '', $create_syntony = '', $pay_syntony = '')
    {
        $plat = @Y::$wrap_head['Devicetype'];
        $local_time = date('Y-m-d H:i:s', time());
        $pay_status = 1;
        $data = [
            'u_id' => $u_id,
            'action' => $plat,
            'price' => $price,
            'trade_num' => $trade_num,
            'order_num' => $order_num,
            'pay_status' => $pay_status,
            'pay_time' => $pay_time,
            'local_time' => $local_time,
            'pay_type' => $pay_type,
            'fact_price' => $price,
            'create_syntony' => $create_syntony,
            'pay_syntony' => $pay_syntony,
            'plat' => $plat,
            'bank_code' => '',
            'account_payment' => '',
        ];
        return T('u_action_log')->add($data);
    }
    public function add_discuss($data = '')
    {
        $arr['users_id'] = $data['users_id'];
        $arr['star'] = $data['star'];
        $arr['content'] = $data['content'];
        $arr['plat'] = $data['plat'];
        $arr['discuss_time'] = date('Y-m-d H:i:s', time());
        $user = T('third_party_user')->set_field('nickname')->where(['id' => $arr['users_id']])->get_one();
        if (!$user) {
            return false;
        }
        $arr['nick_name'] = $user['nickname'];

        if (isset($data['book_id']) && $data['book_id']) {
            $arr['book_id'] = $data['book_id'];
            $book = T('book')->set_field('other_name,replynum')->where(['book_id' => $arr['book_id']])->get_one();
            if (!$book) {
                return false;
            }
            $arr['bookname'] = $book['other_name'];
            $res = T('discuss')->add($arr);
            $replynum['replynum'] = $book['replynum'] + 1;
            T('book')->update($replynum, ['book_id' => $arr['book_id']]);
        } elseif (isset($data['cartoon_id']) && $data['cartoon_id']) {

            $arr['cartoon_id'] = $data['cartoon_id'];
            $book = T('cartoon')->set_field('other_name,replynum')->where(['cartoon_id' => $arr['cartoon_id']])->get_one();

            if (!$book) {
                return false;
            }
            $arr['cartoonname'] = $book['other_name'];
            $res = T('discuss')->add($arr);
            $replynum['replynum'] = $book['replynum'] + 1;
            T('cartoon')->update($replynum, ['cartoon_id' => $arr['cartoon_id']]);
        }
        if ($res) {
            return true;
        } else {
            return false;
        }
    }
    public function user_read_history($users_id = '')
    {
        $data = T('user_history')->field('Distinct book_id,other_name,bpic,desc,isfree,type')->set_where(["users_id" => $users_id])->set_limit('10')->get_all();
        foreach ($data as $key => $value) {
            $data[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
        }

        return $data;
    }
    public function getchild($uid, $page = 0)
    {
        if (!$uid) return [];
        $data = T('invite')->field('Distinct inviteid,uid,flag')->set_where(["uid" => $uid])->set_limit([$page, '10'])->get_all();
        // foreach ($data as $key => $value) {
        //     $data[$key]['desc'] = str_replace("&quot;", "\"", $value['desc']);
        // }
        if (!$data) return [];
        if (!sizeof($data)) {
            return [];
        }
        $uids = array_column($data, 'inviteid');
        $uidss = array_column($data, 'inviteid', 'inviteid');
        $user = T('third_party_user')->set_field('nickname,avater,id')->whereIn('id', $uids)->get_all();
        foreach ($user as $key => $value) {
            # code...
            $user[$key]['flag'] = $uidss[$value['id']];
        }
        return $user;
    }
    public function get_charge_record($page, $users_id)
    {
        $list = T('charge')
            ->field('users_id,charge_icon,send_coin,local_time,charge_type')
            ->where(['users_id' => $users_id])
            ->order('local_time desc')
            ->set_limit([$page, 10]);
        $arr = $list->get_all();
        foreach ($arr as $key => $value) {
            $dates = $value['local_time'];
            $times = date('Y-m-d H:i:s', strtotime("$dates -1 hour"));
            $charge_time = substr($times, 0, -3);
            $time = strtotime($charge_time);
            $arr[$key]['charge_time'] = date('d-m-Y H:i', $time);
            if ($value['charge_type'] == 1) {
                $arr[$key]['charge_type'] = "เติมเงิน";
            } else {
                $arr[$key]['charge_type'] = "ระบบส่ง";
            }
        }
        if ($arr) {
            return $arr;
        } else {
            return false;
        }
    }
    public function closervip($uid)
    {
        if (!$uid) {
            return false;
        }
        return T('third_party_user')->update(['vip_end_time' => '0', 'isvip' => 0, 'open_vip_time' => date('Y-m-d H:i:s')], ['id' => $uid]);
    }
    public function checkvip($uid)
    {
        if (!$uid) {
            return false;
        }

        $user = T('third_party_user')->get_one(['id' => $uid]);
        if (!$user) {
            return false;
        }

        if (!$user['isvip']) {
            return false;
        }

        if (time() > strtotime($user['vip_end_time'])) {
            T('third_party_user')->update(['isvip' => 0], ['id' => $uid]);
            return false;
        }
        return true;
    }
    /**
     * 登入
     * 返回user
     */
    public function login($username, $pwd_md5, $devicetype)
    {
        if (!$username) return false;
        if (!$pwd_md5) return false;
        $w['username'] = $username;
        // $w['password'] = $pwd;
        $user  = T("third_party_user")
            ->where($w)
            ->get_one();
        if ($user['password'] != $pwd_md5) {
            // $user = false;
            return false;
        }
        $ip = Request::getip();
        if ($user) {
            //获取最新token
            $token = M('user', 'im')->gettoken($user['id'], $devicetype, 1);
            $userData = [
                'last_login_ip' => $ip,
                // 'third_party' => $this->head['Devicetype'],
                // 'deviceToken' => $this->head['Devicetoken'],
                // 'last_login_time' => $currentTime,
            ];

            T('third_party_user')->update($userData, ['id' => $user['id']]);
            // M('census', 'im')->dayregcount();//每日注册量统计
        } else {
            return false;
        }
        $user['uid'] = $user['id'];
        $user['remainder'] = round($user['remainder'], 2);
        $user['token'] = $token;
        $user['devicetype'] = $devicetype;
        return $user;
    }
}
