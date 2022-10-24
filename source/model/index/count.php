<?php

namespace ng169\model\index;

use ng169\Y;

checktop();
//统计埋点
class count extends Y
{
    public $uid = 0;
    public function recharge($money, $type)
    {
        return false;
        $where = ['dates' => date('Ymd', time())];
        $have = T('n_count')->get_one($where);
        $pre = 'yfk';
        if ($type == 'android') {
            $key = "a" . $pre;
        }
        if ($type == 'iphone') {
            $key = "i" . $pre;
        }
        if (!$have) {
            $where['zcz'] = $money;
            $where[$key] = 1;
            $where['addtime'] = time();
            T('n_count')->add($where);
        } else {
            $u['zcz'] = $have['zcz'] + $money;
            $u[$key] = $have[$key] + 1;
            T('n_count')->update($u, $where);
        }
    }
    //充值统计
    public function dayrecharge($user, $money, $paytype, $type, $isbacll = false)
    {
        $is = false;
        $day = 86400;
        $time = time();
        $stime = $user['create_time'];
        $w['dates'] = date('Ymd', $stime);
        // $type = Y::$wrap_head['devicetype'];
        $_datac = T('n_day_recharge')->where($w)->get_one();
        if (!$_datac) {
            $w['addtime'] = $stime;
            T('n_day_recharge')->add($w);
            $_datac = T('n_day_recharge')->where($w)->get_one();
        }
        $w2['dates'] = date('Ymd');
        $_data = T('n_day_recharge')->where($w2)->get_one();
        if (!$_data) {
            $w2['addtime'] = $time;
            T('n_day_recharge')->add($w2);
            $_data = T('n_day_recharge')->where($w2)->get_one();
        }
        if ($type == 'android') {
            $key = "a";
        }
        if ($type == 'iphone') {
            $key = "i";
        }
        if ($type == 'wap') {
            $key = "w";
        }
        if ($time >= $stime && $time <= ($stime + 30 * $day)) {

            $update[$key . '30cz'] = $_datac[$key . '30cz'] + $money;
            $update[$key . 'nzcz'] = $_datac[$key . 'nzcz'] + $money; //总充值
        }
        if ($time >= $stime && $time <= ($stime + 7 * $day)) {
            $update[$key . '7cz'] = $_datac[$key . '7cz'] + $money;
        }
        if ($time >= $stime && $time <= ($stime + $day)) {
            $update[$key . '1cz'] = $_datac[$key . '1cz'] + $money;
            $is = true;
        }
        $update[$key . 'nzcz'] = $_datac[$key . 'nzcz'] + $money; //总充值
        $update2[$key . 'cz'] = $_data[$key . 'cz'] + $money;
        $update2[$key . 'cgdd'] = $_data[$key . 'cgdd'] + 1;
        if ($isbacll) {
            //充值订单数量加一
            $update2[$key . 'zdd'] = $_data[$key . 'zdd'] + 1;
        }
        if ($user['isnew']) {
            //$this->_newrecharge();
            $update[$key . 'xyhfcz'] = $_datac[$key . 'xyhfcz'] + 1;
            //变成老用户
            T('third_party_user')->update(['isnew' => 0], ['id' => $user['id']]);
            $this->countnew($money, 1, $key);
        } else {
            $update[$key . 'xyhecz'] = $_datac[$key . 'xyhecz'] + 1;
            $this->countnew($money, 0, $key);
            //$this->_oldrecharge();
        }
        //用户注册时间
        T('n_day_recharge')->update($update, ['dates' => $w['dates']]);
        //今日付费总人数
        $iss = false;
        $ww = ['dates' => date('Ymd') . '2', 'uid' => $user['id']];

        if (!T('n_dotask')->get_one($ww)) {

            $iss = T('n_dotask')->add($ww);
            //每日付费人数统计点
            $wher = ['dates' => date('Ymd')];
            $info = T('n_day_task')->get_one($wher);
            if ($info) {
                T('n_day_task')->update(['ffrs' => 1 + $info['ffrs']], $wher);
            } else {
                $wher['ffrs'] = 1;
                $wher['addtime'] = time();
                T('n_day_task')->add($wher);
            }
        }
        //今天的时间
        // if($iss){
        //     $update2['ffrs'] = $_data['ffrs'] + 1;
        // }
        T('n_day_recharge')->update($update2, ['dates' => $w2['dates']]);
        $this->uid = $user['id'];
        if ($user['isnew']) {
            // $this->_newrecharge();
        } else {
            // $this->_oldrecharge();
        }
        return $is;
    }
    //下单统计
    public function countorder()
    {

        $type = getdevicetype(Y::$wrap_head);
        // $w['uid'] = $uid;
        $w['dates'] = date('Ymd');
        $pre = 'zdd';
        $key = "i" . $pre;
        if ($type == 'android') {
            $key = "a" . $pre;
        }
        if ($type == 'iphone') {
            $key = "i" . $pre;
        }
        if (!isset($key)) {
            return false;
        }
        $where = ['dates' => date('Ymd', time())];
        $have = T('n_day_recharge')->get_one($where);
        if (!$have) {
            $where[$key] = 1;
            $where['addtime'] = time();
            T('n_day_recharge')->add($where);
        } else {
            T('n_day_recharge')->update("`$key`=`$key`+1", $where);
        }
        return true;
    }
    public function countnew($money, $isnewuser, $key)
    {
        $w2['dates'] = date('Ymd');
        $_data = T('n_count')->where($w2)->get_one();
        if (!$_data) {
            $w2['addtime'] = time();
            T('n_count')->add($w2);
            $_data = T('n_count')->where($w2)->get_one();
        }
        $up['zcz'] = $_data['zcz'] + $money;
        if ($isnewuser) {

            $up[$key . 'xyhcz'] = $_data[$key . 'xyhcz'] + 1;
        }
        $up[$key . 'yfk'] = $_data[$key . 'yfk'] + 1;
        T('n_count')->update($up, ['dates' => date('Ymd')]);
    }
    public function taskcount()
    {
    }
}
