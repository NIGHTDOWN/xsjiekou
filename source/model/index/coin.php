<?php

namespace ng169\model\index;

use ng169\Y;

checktop();
//统计埋点
class coin extends Y
{
    //奖励书币,$type奖励类型
    public function add($uid, $coin, $type)
    {

        if (!$this->change($uid, $coin)) {
            return false;
        }
        $w = ['users_id' => $uid];
        $coin_detail = T('users_icon_detail')->set_field($type)->where($w)->get_one();
        if ($coin_detail) {

            T('users_icon_detail')->update([$type => $coin_detail[$type] + $coin], $w);
        } else {
            $icon_detail[$type] = $coin;
            $icon_detail['users_id'] = $uid;
            T('users_icon_detail')->add($icon_detail);
        }
        return true;
    }
    //充值
    public function cz($uid, $coin, $money)
    {

        $w = ['id' => $uid];
        $p = T('third_party_user')->get_one($w);
        $now = $p['remainder'] + $coin;
        $now2 = $p['charge_all'] + $money;
        if ($now < 0) {
            //金币不足失败
            return false;
        }
        if ($p) {
            T('third_party_user')->update(['remainder' => $now, 'charge_all' => $now2], $w); //奖励书币
            $type = 'charge_icon';
            $w = ['users_id' => $uid];
            $coin_detail = T('users_icon_detail')->set_field($type)->where($w)->get_one();
            if ($coin_detail) {

                T('users_icon_detail')->update([$type => $coin_detail[$type] + $coin], $w);
            } else {
                $icon_detail[$type] = $coin;
                $icon_detail['users_id'] = $uid;
                T('users_icon_detail')->add($icon_detail);
            }
            return true;
        }
        return false;
    }
    //下属充值奖励
    public function divided($uid, $pid, $coin)
    {
        // $w = ['id' => $pid];
        // $remains = T('third_party_user')->field('remainder')->where($w)->find();//找到领导金币
        // if ($remains) {
        //     $remain['remainder'] = $remains['remainder'] + $recharge['dummy_icon'] / 10;
        //     T('third_party_user')->update($remain, $w);
        // }
        $coins = $coin / 10;
        $this->change($pid, $coins);
        //领导奖励金额增加
        //奖励记录汇总
        //单个子用户奖励金额
    }
    public function change($uid, $coin)
    {
        $w = ['id' => $uid];
        $p = T('third_party_user')->get_one($w);
        $now = $p['remainder'] + $coin;
        if ($now < 0) {
            //金币不足失败
            return false;
        }

        if ($p) {
            return T('third_party_user')->update(['remainder' => $now], $w); //奖励书币
        }
        return false;
    }
    //解锁小说
    public function unlocktxt($uid, $bookid, $sectionid, $fee, $plat, $isauto = false)
    {
        if (!$uid) {
            return false;
        }

        if (!$bookid) {
            return false;
        }

        if (!$sectionid) {
            return false;
        }

        if (!$fee) {
            return false;
        }

        $expends = T('expend')->where(['users_id' => $uid, 'expend_type' => 1, 'book_id' => $bookid, 'section_id' => $sectionid])->get_one();

        if (!$expends) {
            $user = T('third_party_user')->set_field('nickname,remainder,isvip,vip_end_time')->where(['id' => $uid])->get_one();
            if ($user['isvip'] && $user['vip_end_time'] > time()) {
                return false;
            }
            $bw = ['book_id' => $bookid];
            $sw = ['section_id' => $sectionid];
            $book = T('book')->set_field('other_name,isfree,money')->where($bw)->get_one();

            if (!$book) {
                return false;
            }
            //解锁的时候需要根据语言获取对应章节id
            $lang = T('book')->set_field('lang')->set_where(['book_id' => $bookid])->get_one();
            $tpsec = M('book', 'im')->gettpsec(1, $lang['lang']);
            $section = T($tpsec)->set_field('title,secnum')->where($sw)->get_one();

            if (!$section) {
                return false;
            }
            $fee = $this->bookcalculate($section['secnum'], $book['money']);

            if (!$this->change($uid, -$fee)) {
                return false;
            }

            $arr['bother_name'] = $book['other_name'];
            $arr['nick_name'] = $user['nickname'];
            $arr['section_title'] = $section['title'];
            $arr['section_id'] = $sectionid;
            $arr['book_id'] = $bookid;
            $arr['users_id'] = $uid;
            $arr['expend_time'] = date('Y-m-d H:i:s', time());
            $arr['addtime'] = time();
            $arr['expend_red'] = $fee;
            $arr['expend_type'] = 1;
            $arr['ispay'] = 1;
            $arr['plat'] = $plat;
            $arr['isauto'] = $isauto;
            $arr['remainder'] = $user['remainder'] - $fee;
            T('expend')->add($arr);

            M('census', 'im')->txtunlock_count($fee);
            M('census', 'im')->_aword($fee);
            M('bookcensus', 'im')->unlock($uid, 1, $bookid, $sectionid, $fee);
            $devicetype = $plat;
            // 不同平台区分记录
            if ($devicetype == 'iphone') {
                M('census', 'im')->itxtunlock_fee_count($fee);
            } else {
                M('census', 'im')->txtunlock_fee_count($fee);
            }
            return true;
        }
        return true;
    }
    //解锁漫画
    public function unlockcartoon($uid, $bookid, $sectionid, $fee, $plat, $isauto = false)
    {
        if (!$uid) {
            return false;
        }

        if (!$bookid) {
            return false;
        }

        if (!$sectionid) {
            return false;
        }

        if (!$fee) {
            return false;
        }

        $expends = T('expend')->where(['users_id' => $uid, 'expend_type' => 2, 'book_id' => $bookid, 'section_id' => $sectionid])->get_one();

        if (!$expends) {
            $user = T('third_party_user')->set_field('nickname,remainder')->where(['id' => $uid])->get_one();
            $bw = ['cartoon_id' => $bookid];
            $sw = ['cart_section_id' => $sectionid];
            $book = T('cartoon')->set_field('other_name')->where($bw)->get_one();
            if (!$book) {
                return false;
            }
            $lang = T('cartoon')->set_field('lang')->set_where(['cartoon_id' => $bookid])->get_one();
            $tpsec = M('book', 'im')->gettpsec(2, $lang['lang']);
            $section = T($tpsec)->set_field('title')->where($sw)->get_one();
            if (!$section) {
                return false;
            }
            if (!$this->change($uid, -$fee)) {
                return false;
            }

            $arr['bother_name'] = $book['other_name'];
            $arr['nick_name'] = $user['nickname'];
            $arr['section_title'] = $section['title'];
            $arr['section_id'] = $sectionid;
            $arr['book_id'] = $bookid;
            $arr['users_id'] = $uid;
            $arr['expend_time'] = date('Y-m-d H:i:s', time());
            $arr['expend_red'] = $fee;
            $arr['expend_type'] = 2;
            $arr['ispay'] = 1;
            $arr['addtime'] = time();
            $arr['plat'] = $plat;
            $arr['isauto'] = $isauto;
            $arr['remainder'] = $user['remainder'] - $fee;

            T('expend')->add($arr);

            M('census', 'im')->txtunlock_count($fee);
            M('bookcensus', 'im')->unlock($uid, 2, $bookid, $sectionid, $fee);

            M('census', 'im')->_aword($fee);
            $devicetype = $plat;
            // 不同平台区分记录
            if ($devicetype == 'iphone') {
                M('census', 'im')->itxtunlock_fee_count($fee);
            } else {
                M('census', 'im')->txtunlock_fee_count($fee);
            }
            return true;
        }
        return true;
    }
    /**类型，字数，价格 */
    public function bookcalculate($num, $price)
    {
        $coin = $num / 1000 * $price / 0.0125;
        return round($coin, 1);
    }
    /**
     * 章节消费记录
     */
    public function expand_his($uid, $page)
    {
        if (!$uid) return false;
        $list = T('expend')->field('users_id,section_id,expend_red,expend_time,cart_section_id,cother_name,bother_name,section_title,cart_section_title,expend_type,addtime')
            ->where(["users_id" => $uid])->order('addtime desc')->set_limit([$page, 20])->get_all();
        return $list;
    }

    public function charge($uid, $page)
    {
        if (!$uid) return false;
        $list = T('charge')->field('users_id,charge_icon,send_coin,addtime,charge_type')
            ->where(["users_id" => $uid])->order('addtime desc')->set_limit([$page, 20])->get_all();
        return $list;
    }
    public function record($uid, $page)
    {
        if (!$uid) return false;
        $list = T('task_reward_count')->field('users_id,treward_coin,task_time,task_type,addtime')
            ->where(["users_id" => $uid])->order('task_time desc')->set_limit([$page, 20])->get_all();
        return $list;
    }
}
