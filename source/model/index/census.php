<?php

namespace ng169\model\index;

use ng169\tool\Request;
use ng169\Y;
use ng169\service\Input;

checktop();
//统计埋点
class census extends Y
{
    //注册埋点
    public function dayregcount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('data_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['reg_num'] = 1;
            T('data_count')->add($where);
        } else {
            T('data_count')->update(['reg_num' => $have['reg_num'] + 1], $where);
        }
    }
    //活跃量
    public function dayactivitycount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('data_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['invite_user'] = 1;
            T('data_count')->add($where);
        } else {
            T('data_count')->update(['invite_user' => $have['invite_user'] + 1], $where);
        }
    }
    //分享统计
    public function daysahrecount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('data_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['share_user'] = 1;
            T('data_count')->add($where);
        } else {
            T('data_count')->update(['share_user' => $have['share_user'] + 1], $where);
        }
    }
    //iphone分享统计
    public function idaysahrecount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['i_share_user'] = 1;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['i_share_user' => $have['i_share_user'] + 1], $where);
        }
    }
    //安卓注册量
    public function dayaregcount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('data_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['android_reg_num'] = 1;
            T('data_count')->add($where);
        } else {
            T('data_count')->update(['android_reg_num' => $have['android_reg_num'] + 1], $where);
        }
    }
    //苹果日安装量
    public function idayinstallcount($plat)
    {
        $time = date('Y-m-d', time());
        $where = ['dates' => $time, 'plat_id' => $plat];
        $have = T('plat_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $_count['install_date'] = $time;
            $_count['reg_num'] = 1;
            $_count['install_num'] = 1;
            $_count['plat_id'] = $plat;
            // $where['android_reg_num'] = 1;
            T('plat_count')->add($_count);
        } else {
            $_count['reg_num'] = $have['reg_num'] + 1;
            $_count['install_num'] = $have['install_num'] + 1;
            T('plat_count')->update($_count, $where);
        }
    }
    //加入小説書記統計
    public function collectcounts($book_id)
    {
        if (!$book_id) return false;
        $where = ['book_id' => $book_id];

        T('book')->update(['collect' => 'collect+1', 'rack' => 'rack+1'], $where, 0);
    }
    //小说支付统计
    public function bpaycounts($book_id, $money)
    {
        $where = ['book_id' => $book_id];
        $have = T('book_other')->get_one($where);
        if (!$have) {

            $where['i_recharge'] = $money;

            T('book_other')->add($where);
        } else {
            $w['i_recharge'] = $have['i_recharge'] + $money;

            T('book_other')->update($w, $where);
        }
    }
    //漫画支付统计
    public function cpaycounts($book_id, $money)
    {
        $where = ['cartoon_id' => $book_id];
        $have = T('cartoon_other')->get_one($where);
        if (!$have) {

            $where['i_recharge'] = $money;

            T('cartoon_other')->add($where);
        } else {
            $w['i_recharge'] = $have['i_recharge'] + $money;

            T('cartoon_other')->update($w, $where);
        }
    }
    //代充记录
    public function agentpaylog($payuid, $uid, $money, $coin)
    {
        $agent['users_id'] = $payuid;
        $agent['u_id'] = $uid;
        $agent['return_coin'] = $coin;
        $agent['agent_time'] = date('Y-m-d H:i:s', time());
        $agent['agent_date'] = date('Y-m-d', time());
        $agent['plat'] = $this->head['devicetype'];
        // $agent['price'] = $money;

        T('user_agent')->add($agent);
        //代充返利
        M('coin', 'im')->change($payuid, $coin);
        $agent_task = Y::$newconf['task'];
        $this->task_reward_count($payuid, $coin, $agent_task['agent_task']);
        return true;
    }
    //人气埋点
    public function hitcounts($book_id)
    {
        $where = ['book_id' => $book_id];

        T('book')->update('hits=hits+1,`read`=`read`+1', $where, 0);
    }
    public function cartoonhitcounts($book_id)
    {
        $where = ['cartoon_id' => $book_id];

        T('cartoon')->update('hits=hits+1,`read`=`read`+1', $where, 0);
    }
    public function cartooncollectcounts($book_id)
    {
        $where = ['cartoon_id' => $book_id];
        if ($book_id) {
            T('cartoon')->update(['collect' => 'collect+1', 'rack' => 'rack+1'], $where, 0);
        }
    }
    //免費小説統計
    public function freecollectcounts($book_id)
    {
        return false;
        $where = ['book_id' => $book_id];
        $have = T('book')->set_field('isfree')->get_one($where);
        if (!$have) {
            return false;
        }
        if ($have['isfree'] != 0) {
            return false;
        }

        $where['dates'] = date('Y-m-d', time());

        $have = T('free_bcount')->set_field('book_id')->get_one($where);

        if ($have) {
            T('free_bcount')->update(['day_racks' => $have['day_racks'] + 1], $where);
        } else {
            $this->free_bcount($have['book_id'], $have['other_name'], '', '', 1, 2);
            //  $this->free_bcount($have['book_id'], $have['other_name'], '', '', 1, 1);
        }
    }

    //點擊率
    public function freehitcounts($book_id)
    {
        return false;
        $where = ['book_id' => $book_id];
        $have = T('book')->set_field('isfree')->get_one($where);
        if (!$have) {
            return false;
        }
        if ($have['isfree'] != 0) {
            return false;
        }
        $where['dates'] = date('Y-m-d', time());
        $have = T('free_bcount')->get_one($where);
        if ($have && $have['isfree'] == 0) {
            if ($have) {
                T('free_bcount')->update(['day_racks' => $have['day_hits'] + 1], $where);
            } else {
                $this->free_bcount($have['book_id'], $have['other_name'], '', 1, '', 1);
            }
        }
    }
    public function cartoonfreehitcounts($book_id)
    {
        return false;
        $where = ['cartoon_id' => $book_id];
        $have = T('cartoon')->set_field('isfree')->get_one($where);
        if (!$have) {
            return false;
        }
        if ($have['isfree'] != 0) {
            return false;
        }
        $where['dates'] = date('Y-m-d', time());
        $have = T('free_ccount')->join_table(['t' => 'cartoon', 'cartoon_id', 'cartoon_id'])->set_field('v.*,cartoon.other_name,cartoon.isfree')->get_one($where);
        if ($have && $have['isfree'] == 0) {
            if ($have) {
                T('free_ccount')->update(['day_racks' => $have['day_hits'] + 1], $where);
            } else {
                $this->free_bcount($have['cartoon_id'], $have['other_name'], '', 1, '', 2);
            }
        }
    }
    //閲讀量
    public function freereadcounts($book_id)
    {
        return;
        $where = ['book_id' => $book_id];

        $have = T('book')->set_field('isfree')->get_one($where);
        if (!$have) {
            return false;
        }
        if ($have['isfree'] != 0) {
            return false;
        }

        $where['dates'] = date('Y-m-d', time());
        $have = T('free_bcount')->join_table(['t' => 'book', 'book_id', 'book_id'])->set_field('v.*,book.other_name,book.isfree')->get_one($where);
        if ($have && $have['isfree'] == 0) {
            if ($have) {
                T('free_bcount')->update(['day_racks' => $have['day_reads'] + 1], $where);
            } else {
                $this->free_bcount($have['book_id'], $have['other_name'], 1, '', '', 1);
                //  $this->free_bcount($have['book_id'], $have['other_name'], '', '', 1, 1);
            }
        }
    }
    public function cartoonfreereadcounts($book_id)
    {
        return;
        $where = ['cartoon_id' => $book_id];
        $have = T('cartoon')->set_field('isfree')->get_one($where);
        if (!$have) {
            return false;
        }
        if ($have['isfree'] != 0) {
            return false;
        }
        $where['dates'] = date('Y-m-d', time());
        $have = T('free_ccount')->join_table(['t' => 'cartoon', 'cartoon_id', 'cartoon_id'])->set_field('v.*,cartoon.other_name,cartoon.isfree')->get_one($where);
        if ($have && $have['isfree'] == 0) {
            if ($have) {
                T('free_ccount')->update(['day_racks' => $have['day_reads'] + 1], $where);
            } else {
                $this->free_bcount($have['cartoon_id'], $have['other_name'], 1, '', '', 2);
            }
        }
        T('cartoon')->update(['read' => 'read+1'], ['cartoon_id' => $book_id]);
    }
    //免费小说收藏统计
    public function cartoonfreecollectcounts($book_id)
    {
        $where = ['cartoon_id' => $book_id];
        $have = T('cartoon')->set_field('isfree')->get_one($where);
        if (!$have) {
            return false;
        }
        if ($have['isfree'] != 0) {
            return false;
        }
        $where['dates'] = date('Y-m-d', time());

        $have = T('free_ccount')->get_one($where);

        if ($have) {
            T('free_ccount')->update(['day_racks' => $have['day_racks'] + 1], $where);
        } else {
            $this->free_bcount($have['cartoon_id'], $have['other_name'], '', '', 1, 1);
        }
    }
    public function free_bcount($book_id = '', $other_name = '', $day_reads = '', $day_hits = '', $day_racks = '', $type = '')
    {
        if ($type == 1) {
            $free_bcount = [
                'book_id' => $book_id,
                'other_name' => $other_name,
                'day_reads' => $day_reads,
                'day_hits' => $day_hits,
                'day_racks' => $day_racks,
                'dates' => date('Y-m-d'),
            ];
            T('free_bcount')->add($free_bcount);
        } else {
            $free_ccount = [
                'cartoon_id' => $book_id,
                'other_name' => $other_name,
                'day_reads' => $day_reads,
                'day_hits' => $day_hits,
                'day_racks' => $day_racks,
                'dates' => date('Y-m-d'),
            ];
            T('free_ccount')->add($free_ccount);
        }
    }
    //安装统计
    public function installcounts()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('data_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['install_num'] = 1;
            T('data_count')->add($where);
        } else {
            T('data_count')->update(['install_num' => $have['install_num'] + 1], $where);
        }
    }
    //邀请奖励记录
    public function inviterecharge($uid, $pid, $aword)
    {
        $insert['uid'] = $uid;
        $insert['pid'] = $pid;
        $data = T('n_invite')->get_one($insert);
        if (!$data) {
            $insert['coin'] = $aword;
            $insert['maketime'] = time();
            $insert['uptime'] = time();
            T('n_invite')->add($insert);
        } else {
            $w = $insert;
            $insert['coin'] = $aword + $data['coin'];
            // $insert['maketime']= time();
            $insert['uptime'] = time();
            T('n_invite')->update($insert, $w);
        }
        unset($w['pid']);
        $w['uid'] = $pid;
        $data = T('n_user_invite')->get_one($w);
        if ($data) {
            //更新
            $w2['coin'] = $aword + $data['coin'];
            $w2['num'] = 1 + $data['num'];

            T('n_user_invite')->update($w2, $w);
        } else {
            //添加
            $user = T('third_party_user')->set_field('avater,nickname')->get_one(['id' => $pid]);
            $w['addtime'] = time();
            // $w['uid'] = $pid;
            $w['num'] = 1;
            $w['coin'] = $aword;
            $w['avater'] = $user['avater'];
            $w['nickname'] = $user['nickname'];
            T('n_user_invite')->add($w);
        }
        T('invite_list')->add(['uid' => $uid, 'pid' => $pid, 'addtime' => time(), 'coin' => $aword, 'type' => 0]);
    }
    //充值统计
    public function irecharge($user, $money, $paytype, $type)
    {
        $is = false;
        $day = 86400;
        $time = time();
        // $user=T()->set_field()->get_one(['id'=>]);
        $stime = $user['create_time'];
        //$etime = $user['create_time'] +  $day;
        $w = ['dates' => date('Ymd', $stime)];
        // $type = getdevicetype(Y::$wrap_head);;

        $_datac = T('n_day_recharge')->where($w)->find();
        if (!$_datac) {
            T('n_day_recharge')->add($w);
            $_datac = T('n_day_recharge')->where($w)->find();
        }
        if ($type == 'android') {
            $key = "a";
        } else {
            $key = "i";
        }
        if ($type == 'wap') {
            $key = "i";
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
        $update2[$key . 'cz'] = $_datac[$key . 'cz'] + $money;
        $update2[$key . 'cgdd'] = $_datac[$key . 'cgdd'] + $money;
        T('n_day_recharge')->update($update, $w);
        T('n_day_recharge')->update($update2, ['dates' => date('Ymd')]);
        return $is;

        ////////////////////////////////////////
        $_datac = T('data_count')->field('i_day_charge,i_week_charge,i_month_charge,i_all_charge')->where($w)->find();
        $charge_time = T('order')->where(['users_id' => $user['id']])->where(['pay_status' => 1])->get_count();
        if ($time >= $stime && $time <= ($stime + 30 * $day)) {
            $d_rec['i_month_charge'] = $_datac['i_month_charge'] + $money;
            if ($time <= ($stime + 7 * $day)) {
                $d_rec['i_week_charge'] = $_datac['i_week_charge'] + $money;
            }
            if ($time <= ($stime + $day)) {
                //一天内
                $d_rec['i_day_charge'] = $_datac['i_day_charge'] + $money;
                $is = true;
            }
            $d_rec['i_all_charge'] = $_datac['i_all_charge'] + $money;
            if ($charge_time == 1) {
                $d_rec['i_new_people_charge'] = $_datac['i_new_people_charge'] + 1;
                $tmp = T('an_iphone_count')->get_one($w);
                if ($tmp) {
                    T('an_iphone_count')->update(['i_new_people_charge' => $tmp['i_new_people_charge'] + 1], $w);
                }
            } else {
                $d_rec['i_two_charge'] = $_datac['i_two_charge'] + 1;
            }
            T('data_count')->update($d_rec, $w);
        } else {
            $d_rec['i_all_charge'] = $_datac['i_all_charge'] + $money;
            T('data_count')->update($d_rec, $w);
        }
        $w = ['dates' => date('Y-m-d', $time)];
        $tmp = T('data_count')->get_one($w);
        if ($tmp) {
            T('data_count')->update(['i_recharge' => $tmp['i_recharge'] + $money, 'success' => 1 + $tmp['success']], $w);
        }
        $tmp = T('an_iphone_count')->get_one($w);
        if ($tmp) {
            T('an_iphone_count')->update(['recharge' => $tmp['recharge'] + $money, 'i_pay_num' => 1 + $tmp['i_pay_num']], $w);
        }
        return true;
    }
    public function installcount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['a_install_num'] = 1;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['a_install_num' => $have['a_install_num'] + 1], $where);
        }
    }
    public function ordercount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('data_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['orders'] = 1;
            T('data_count')->add($where);
        } else {
            T('data_count')->update(['orders' => $have['orders'] + 1], $where);
        }
    }
    public function iinstallcount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['i_install_num'] = 1;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['i_install_num' => $have['i_install_num'] + 1], $where);
        }
    }
    //iphone表下安卓注册量
    public function idayaregcount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['android_reg_num'] = 1;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['android_reg_num' => $have['android_reg_num'] + 1], $where);
        }
    }
    //每日iphone注册量
    public function dayiregcount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('data_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['iphone_reg_num'] = 1;
            T('data_count')->add($where);
        } else {
            T('data_count')->update(['iphone_reg_num' => $have['iphone_reg_num'] + 1], $where);
        }
    }
    //iphone表下每日iphone注册量
    public function idayiregcount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['iphone_reg_num'] = 1;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['iphone_reg_num' => $have['iphone_reg_num'] + 1], $where);
        }
    }
    //iPhone活跃量
    public function idayactivitycount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {

            $where['i_invite_user'] = 1;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['i_invite_user' => $have['i_invite_user'] + 1], $where);
        }
    }
    //iPhone下安卓活跃量
    public function idayaactivitycount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {

            $where['a_invite_user'] = 1;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['a_invite_user' => $have['a_invite_user'] + 1], $where);
        }
    }

    public function idaysharecount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['a_share_user'] = 1;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['a_share_user' => $have['a_share_user'] + 1], $where);
        }
    }
    //分享统计
    public function sharecount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('data_count')->get_one($where);
        if (!$have) {

            $where['share_all'] = 1;
            T('data_count')->add($where);
        } else {
            T('data_count')->update(['share_all' => $have['share_all'] + 1], $where);
        }
    }
    //邀请统计
    public function yqcount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('data_count')->get_one($where);
        if (!$have) {

            $where['invite_all'] = 1;
            T('data_count')->add($where);
        } else {
            T('data_count')->update(['invite_all' => $have['invite_all'] + 1], $where);
        }
    }
    //邀请统计
    public function iyqcount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {

            $where['i_invite_all'] = 1;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['i_invite_all' => $have['i_invite_all'] + 1], $where);
        }
    }
    //分享统计
    public function isharecount()
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {

            $where['i_share_all'] = 1;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['i_share_all' => $have['i_share_all'] + 1], $where);
        }
    }
    public function answercount($answerid)
    {
        $where = ['answer_id' => $answerid];
        $have = T('answer')->get_one($where);
        if (!$have) {

            $where['select_nums'] = 1;
            T('answer')->add($where);
        } else {
            T('answer')->update(['select_nums' => $have['select_nums'] + 1], $where);
        }
    }
    /**邀请奖励 */
    public function Invitationreward($deviceToken, $user_id, $invite_code, $nickname = null)
    {
        //判断设备邀请记录是否已经存在
        $hardware = T('user_invite')->set_field('u_id')->where(['hardware_id' => $deviceToken])->where(['status' => 1])->get_one();
        if ($hardware) {
            T('third_party_user')->update(['invite_id' => $invite_code], ['id' => $user_id]);
        } else {
            $invite_coin = self::$newconf['task'];
            $user_mess['users_id'] = $user_id;
            $user_mess['icon'] = $invite_coin['invite_coin'];
            $user_mess['status'] = 1;
            $user_mess['nick_name'] = $nickname;
            $user_mess['hardware_id'] = $deviceToken;
            $user_mess['invite_code'] = $invite_code;
            $user_mess['u_id'] = $invite_code;
            $user_mess['invite_time'] = date('Y-m-d H:i:s', time());
            $user_mess['type'] = 1;
            // 奖励数值统计总表
            T('user_invite')->add($user_mess); //邀请记录
            // T('task_reward_count')->insert($tcount);//任务奖励记录
            $this->task_reward_count($invite_code, $invite_coin['invite_coin'], $invite_coin['invite_task']);
            // M('census', 'im')->dayactivitycount();
            // M('census', 'im')->idayactivitycount();
            M('coin', 'im')->add($invite_code, $user_mess['icon'], 'invite_icon');
            T('third_party_user')->update(['invite_id' => $invite_code], ['id' => $user_id]);
        }
    }
    public function task_reward_count($uid, $coin, $type)
    {

        $tcount['users_id'] = $uid;
        $tcount['task_time'] = date('Y-m-d H:i:s', time());
        $tcount['addtime'] = time();
        $tcount['task_type'] = $type;
        $tcount['treward_coin'] = $coin;
        $this->_task($uid, $type, $coin);
        T('task_reward_count')->add($tcount); //任务奖励记录

    }

    //打赏统计
    public function reward_count($coin)
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('data_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['reward'] = $coin;
            T('data_count')->add($where);
        } else {
            T('data_count')->update(['reward' => $have['reward'] + $coin], $where);
        }
    }
    //签到统计
    public function sign_count($coin)
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('data_count')->get_one($where);
        if (!$have) {
            $where['sign'] = $coin;
            $where['sign_num'] = 1;
            T('data_count')->add($where);
        } else {
            $u['sign'] = $have['sign'] + $coin;
            $u['sign_num'] = $have['sign_num'] + 1;
            T('data_count')->update($u, $where);
        }
    }
    public function isign_count($coin)
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {
            $where['sign'] = $coin;
            $where['sign_num'] = 1;
            T('an_iphone_count')->add($where);
        } else {
            $u['sign'] = $have['sign'] + $coin;
            $u['sign_num'] = $have['sign_num'] + 1;
            T('an_iphone_count')->update($u, $where);
        }
    }
    public function ireward_count($coin)
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['reward'] = $coin;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['reward' => $have['reward'] + $coin], $where);
        }
    }
    //小说解锁统计
    public function txtunlock_count($coin)
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('data_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['expend'] = $coin;
            T('data_count')->add($where);
        } else {
            T('data_count')->update(['expend' => $have['expend'] + $coin], $where);
        }
    }
    //解锁花费统计
    public function txtunlock_fee_count($coin)
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['expend_num'] = $coin;
            $where['a_expend'] = $coin;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['expend_num' => $have['expend_num'] + $coin, 'a_expend' => $have['a_expend'] + $coin], $where);
        }
    }
    public function itxtunlock_fee_count($coin)
    {
        $where = ['dates' => date('Y-m-d', time())];
        $have = T('an_iphone_count')->get_one($where);
        if (!$have) {
            // $rec['dates'] = date('Y-m-d',time());
            $where['expend_num'] = $coin;
            $where['i_expend'] = $coin;
            T('an_iphone_count')->add($where);
        } else {
            T('an_iphone_count')->update(['expend_num' => $have['expend_num'] + $coin, 'i_expend' => $have['i_expend'] + $coin], $where);
        }
    }
    //更新书架阅读时间
    public function uprackreadtime($uid, $bookid, $type, $section_id)
    {
        return false;
        if (!$uid) {
            return false;
        }

        if (!$bookid) {
            return false;
        }

        if (!$type) {
            return false;
        }

        return T('racks')->update(['read_time' => date('Y-m-d H:i:s', time()), 'section_id' => $section_id], [
            'users_id' => $uid,
            'book_id' => $bookid,
            'type' => $type,
        ]);
    }

    //全新埋点
    public function _sign($coin)
    {
        $where = ['dates' => date('Ymd', time())];
        $have = T('n_count')->get_one($where);
        if (!$have) {
            $where['qdsb'] = $coin;
            $where['qdrs'] = 1;
            $where['addtime'] = time();
            T('n_count')->add($where);
        } else {
            $u['qdsb'] = $have['qdsb'] + $coin;
            $u['qdrs'] = $have['qdrs'] + 1;

            T('n_count')->update($u, $where);
        }
    }
    public function daytaskcount($key, $coin = null)
    {
        $where = ['dates' => date('Ymd', time())];
        $have = T('n_day_task')->get_one($where);
        if (!$have) {
            if ($coin) {
                $where[$key] = $coin;
            } else {
                $where[$key] = 1;
            }

            $where['addtime'] = time();
            T('n_day_task')->add($where);
        } else {
            if ($coin) {
                $u[$key] = $have[$key] + $coin;
            } else {
                $u[$key] = $have[$key] + 1;
            }

            // $u['zrs'] = $have['zrs'] + 1;
            // $u['zjb'] = $have['zjb'] + 1;
            T('n_day_task')->update($u, $where);
        }
    }
    public function _recharge($money, $type)
    {
        return false;
        $where = ['dates' => date('Ymd', time())];
        $have = T('n_count')->get_one($where);
        $pre = 'yfk';
        if ($type == 'android') {
            $key = "a" . $pre;
        } else {
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
    public function _aword($money)
    {

        $where = ['dates' => date('Ymd', time())];
        $have = T('n_count')->get_one($where);

        $type = getdevicetype(Y::$wrap_head);
        $pre = 'xhsb';
        if ($type == 'android') {
            $key = "a" . $pre;
        } else {
            $key = "i" . $pre;
        }

        if (!$have) {
            $where['zds'] = $money;
            $where['zxh'] = $money;
            $where[$key] = $money;
            // $where['qdrs'] = 1;
            $where['addtime'] = time();

            T('n_count')->add($where);
        } else {
            $u['zds'] = $have['zds'] + $money;
            $u['zxh'] = $have['zxh'] + $money;
            $u[$key] = $have[$key] + $money;
            // $u['qdrs'] = $have['qdrs'] + 1;
            T('n_count')->update($u, $where);
        }
    }
    public function _install()
    {

        $type = getdevicetype(Y::$wrap_head);;
        $w['device_type'] = getdevicetype(Y::$wrap_head);;
        $w['device_token'] = Y::$wrap_head['idfa'] ? Y::$wrap_head['idfa'] : Request::getip();
        //设备类型不存在，表示接口直接请求，不算入设备统计
        if (!$type) return false;
        if (T('user_install')->get_one($w)) {
            return false;
        }
        try {
            //code...

            $w['dates'] = date('Y-m-d');
            // $w['addtime'] = time();

            T('user_install')->add($w);
            if ($type == 'android') {
                $key = "aazl";
            } else {
                $key = "iazl";
            }
            if (!isset($key)) {
                return false;
            }
            $where = ['dates' => date('Ymd', time())];
            $have = T('n_count')->get_one($where);
            if (!$have) {
                $where[$key] = 1;
                $where['addtime'] = time();
                T('n_count')->add($where);
            } else {
                $u[$key] = $have[$key] + 1;

                T('n_count')->update($u, $where);
            }
            return true;
        } catch (\Throwable $th) {
            //throw $th;
            //并发错误
            // d($th);
            return false;
        }
    }
    //记录日活，版本活跃等
    public function logcount($uid)
    {
        $head = Y::$wrap_head;

        // d($head);
        $insert['uid'] = $uid;
        $insert['idfa'] = $head['idfa'];
        $devices = getdevicetype($head);

        if (!$uid && !$devices) {
            //如果用户id 跟 idfa都是空；说明是模拟 提交，也可能是恶意提交
            //不计入统计数据
            $this->_errorlog();
            return false;
        }

        //$this->_errorlog();
        $insert['devicetype'] = $devices;
        $insert['d'] = date('Ymd');
        if (!$insert['idfa']) {
            unset($insert['idfa']);
            $insert['ip'] = Request::getip();
        }

        //天
        if (!T('count_log')->set_filed('id')->get_one($insert)) {

            try {
                //code...
                $this->_dayacount($uid);
                $this->_versionacount($uid);
                $this->_citycount();
            } catch (\Throwable $th) {
                //throw $th;
            }
        }
        $insert['h'] = date('H');
        //时间
        if (T('count_log')->set_filed('id')->get_one($insert)) {
            return false;
        }

        //日活
        //版本活跃
        //小时活跃
        try {
            //code...
            // $this->_dayacount($uid);
            //添加记录
            $insert['ip'] = Request::getip();
            $insert['appversion'] = $head['version'];
            $insert['action'] = D_MEDTHOD . D_FUNC;
            $insert['addtime'] = time();
            T('count_log')->add($insert);


            $this->_timeacount($uid);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }
    private function _errorlog()
    {
        $head = Y::$wrap_head;
        $insert['addtime'] = time();
        $insert['head'] = json_encode($head);
        $insert['ip'] = Request::getip();
        T('count_error')->add($insert);
    }
    private function _dayacount($uid)
    {
        $w['date'] = date('Ymd');
        if ($uid) {
            //登入状态
            $w['type'] = 0;
        } else {
            //未登入状态
            $w['type'] = 1;
        }
        $in = T('count_day')->set_field('num')->get_one($w);
        if ($in) {
            $in['num']++;
            T('count_day')->update($in, $w);
        } else {
            $w['num'] = 1;
            T('count_day')->add($w);
        }
    }
    private function _timeacount($uid)
    {
        $w['date'] = date('Ymd');
        if ($uid) {
            //登入状态
            $w['type'] = 0;
        } else {
            //未登入状态
            $w['type'] = 1;
        }
        $s = 't' . date('H');
        $in = T('count_time')->set_field($s)->get_one($w);
        if ($in) {
            $in[$s]++;
            T('count_time')->update($in, $w);
        } else {
            $w[$s] = 1;
            T('count_time')->add($w);
        }
    }
    private function _versionacount($uid)
    {
        $head = Y::$wrap_head;
        $w['date'] = date('Ymd');
        $w['version'] = $head['version'];

        // if ($uid) {
        //     //登入状态
        //     $w['type'] = 0;
        // } else {
        //     //未登入状态
        //     $w['type'] = 1;
        // }
        $in = T('count_version')->set_field('num')->get_one($w);
        if ($in) {
            $in['num']++;
            T('count_version')->update($in, $w);
        } else {
            $w['num'] = 1;
            T('count_version')->add($w);
        }
    }
    private function _citycount()
    {
        $head = Y::$wrap_head;
        $w['date'] = date('Ymd');
        $w['city'] = $head['lang'];
        $in = T('count_city')->set_field('num')->get_one($w);
        if ($in) {
            $in['num']++;
            T('count_city')->update($in, $w);
        } else {
            $w['num'] = 1;
            T('count_city')->add($w);
        }
    }
    public function _reg()
    {
        $type = getdevicetype(Y::$wrap_head);;

        $pre = 'reg';
        if ($type == 'android') {
            $key = "a" . $pre;
        } else {
            $key = "i" . $pre;
        }
        if (!isset($key)) {
            return false;
        }
        $where = ['dates' => date('Ymd', time())];
        $have = T('n_count')->get_one($where);
        if (!$have) {
            // $where['zds'] = $money;
            // $where['zxh'] = $money;
            $where[$key] = 1;
            $where['addtime'] = time();
            T('n_count')->add($where);
        } else {
            $u[$key] = $have[$key] + 1;
            //$u['zxh'] = $have['zxh'] + $money;

            // $u['qdrs'] = $have['qdrs'] + 1;
            T('n_count')->update($u, $where);
        }
        return true;
    }
    public function _invitation($bool = true)
    {

        $type = getdevicetype(Y::$wrap_head);;
        if (!$bool) {
            $pre = 'yqyh';
        } else {
            $pre = 'fxyh';
        }

        if ($type == 'android') {
            $key = "a" . $pre;
        } else {
            $key = "i" . $pre;
        }
        if (!isset($key)) {
            return false;
        }
        $where = ['dates' => date('Ymd', time())];
        $have = T('n_count')->get_one($where);
        if (!$have) {
            // $where['zds'] = $money;
            // $where['zxh'] = $money;
            $where[$key] = 1;
            $where['addtime'] = time();
            T('n_count')->add($where);
        } else {
            $u[$key] = $have[$key] + 1;
            //$u['zxh'] = $have['zxh'] + $money;

            // $u['qdrs'] = $have['qdrs'] + 1;
            T('n_count')->update($u, $where);
        }
        return true;
    }
    public function _startinvitation($uid)
    {
        if (!$uid) return false;
        $w['dates'] = date('Ymd') . '1';
        $w['uid'] = $uid;
        $in = T('n_dotask')->get_one($w);
        if ($in) {
            return false;
        }
        T('n_dotask')->add($w);
        $type = getdevicetype(Y::$wrap_head);;
        $pre = 'fqyq';

        if ($type == 'android') {
            $key = "a" . $pre;
        } else {
            $key = "i" . $pre;
        }
        if (!isset($key)) {
            return false;
        }
        $where = ['dates' => date('Ymd', time())];
        $have = T('n_count')->get_one($where);
        if (!$have) {
            // $where['zds'] = $money;
            // $where['zxh'] = $money;
            $where[$key] = 1;
            $where['addtime'] = time();
            T('n_count')->add($where);
        } else {
            $u[$key] = $have[$key] + 1;
            //$u['zxh'] = $have['zxh'] + $money;
            // $u['qdrs'] = $have['qdrs'] + 1;
            T('n_count')->update($u, $where);
        }
        $w2['dates'] = date('Ymd');
        // $w['uid']=$uid;
        $in = T('n_day_task')->get_one($w2);
        if ($in) {
            $u2['fqyqzs'] = $in['fqyqzs'] + 1;
            T('n_day_task')->update($u2, $w2);
        } else {
            $w2['fqyqzs'] = 1;
            $w2['addtime'] = time();
            T('n_day_task')->add($w2);
        }
        return true;
    }
    public function _share()
    {
        $type = getdevicetype(Y::$wrap_head);;
        $pre = 'fqfx';
        if ($type == 'android') {
            $key = "a" . $pre;
        } else {
            $key = "i" . $pre;
        }
        if (!isset($key)) {
            return false;
        }
        $where = ['dates' => date('Ymd', time())];
        $have = T('n_count')->get_one($where);
        if (!$have) {
            // $where['zds'] = $money;
            // $where['zxh'] = $money;
            $where[$key] = 1;
            $where['addtime'] = time();
            T('n_count')->add($where);
        } else {
            $u[$key] = $have[$key] + 1;
            //$u['zxh'] = $have['zxh'] + $money;
            // $u['qdrs'] = $have['qdrs'] + 1;
            T('n_count')->update($u, $where);
        }
        return true;
    }
    public function _newrecharge()
    {
        $type = getdevicetype(Y::$wrap_head);;
        $pre = 'xyhfcz';
        $pre2 = 'xyhfcz';
        if ($type == 'android') {
            $key = "a" . $pre;
            $k2 = "a" . $pre2;
        } else {
            $key = "i" . $pre;
            $k2 = "i" . $pre2;
        }
        if (!isset($key)) {
            return false;
        }
        $where = ['dates' => date('Ymd', time())];
        $have = T('n_count')->get_one($where);
        if (!$have) {
            // $where['zds'] = $money;
            // $where['zxh'] = $money;
            $where[$key] = 1;
            $where['addtime'] = time();
            T('n_count')->add($where);
        } else {
            $u[$key] = $have[$key] + 1;
            //$u['zxh'] = $have['zxh'] + $money;
            // $u['qdrs'] = $have['qdrs'] + 1;
            T('n_count')->update($u, $where);
        }
        $have = T('n_day_recharge')->get_one($where);
        if (!$have) {
            // $where['zds'] = $money;
            // $where['zxh'] = $money;
            $where[$k2] = 1;
            $where['addtime'] = time();
            T('n_day_recharge')->add($where);
        } else {
            $u[$k2] = $have[$k2] + 1;
            //$u['zxh'] = $have['zxh'] + $money;
            // $u['qdrs'] = $have['qdrs'] + 1;
            T('n_day_recharge')->update($u, $where);
        }

        return true;
    }
    public function _oldrecharge()
    {
        $type = getdevicetype(Y::$wrap_head);;
        $pre = 'xyhfcz';
        $pre2 = 'xyhecz';
        if ($type == 'android') {
            $key = "a" . $pre;
            $k2 = "a" . $pre2;
        } else {
            $key = "i" . $pre;
            $k2 = "i" . $pre2;
        }
        if (!isset($key)) {
            return false;
        }
        $where = ['dates' => date('Ymd', time())];

        $have = T('n_day_recharge')->get_one($where);
        if (!$have) {
            // $where['zds'] = $money;
            // $where['zxh'] = $money;
            $where[$k2] = 1;
            $where['addtime'] = time();
            T('n_day_recharge')->add($where);
        } else {
            $u[$k2] = $have[$k2] + 1;
            T('n_day_recharge')->update($u, $where);
        }
        return true;
    }
    public function shareclick($uid, $bookid, $type, $nap)
    {

        // if (!$uid) {
        //     //次数加1
        //     return false;
        // }
        if (!$nap) {
            $nap = time();
        }
        $i['addtime'] = $nap;
        $i['uid'] = $uid;
        $i['bookid'] = $bookid;
        $i['type'] = $type;
        $in = T('n_shareclick')->set_field('num')->get_one($i);


        if ($in) {
            // T('n_shareclick')->update(['num' => $in['num'] + 1], $i);
        } else {
            $i['ip'] = Request::getip();
            $i['dates'] = date('Ymd', $nap);
            $head = input::getheader();
            $i['info'] = json_encode($head);
            $i['sharetype'] = $this->getsharetype($head);
            // $i['info'] = $this->getsharetype($head);
            try {
                //code...
                T('n_shareclick')->add($i);
            } catch (\Throwable $th) {
                //throw $th;
                return false;
            }
        }

        return true;
    }
    private function getsharetype($head)
    {
        $agent = strtolower($head['user-agent']);
        // return $agent;
        // $agent="facebookexternalhit\/1.1 (+http:\/\/www.facebook.com\/externalhit_uatext.php)";
        if (strstr($agent, "facebook")) {
            return 1;
        }
        if (strstr($agent, "twitter")) {
            return 2;
        }
        if (strstr($agent, "whatsapp")) {
            return 3;
        }
        // if (stripos($agent, "twitter") == 1) {
        return 0;
        // }
    }
    //是否来源分享
    public function formshare($uid)
    {
        if (!$uid) {
            return false;
        }
        // $i['dates']=date('Ymd',time());
        $i['ip'] = Request::getip();
        $i['uid'] = $uid;
        return T('n_shareclick')->get_one($i);
    }
    public function _activity($uid)
    {
        return false;
        $type = getdevicetype(Y::$wrap_head);;
        $w['uid'] = $uid;
        $w['dates'] = date('Ymd');
        if (T('n_activity')->get_one($w)) {
            return false;
        }
        //$w['addtime']=time();
        T('n_activity')->add($w);
        $pre = 'hyzh';
        if ($type == 'android') {
            $key = "a" . $pre;
        } else {
            $key = "i" . $pre;
        }
        if (!isset($key)) {
            return false;
        }
        $where = ['dates' => date('Ymd', time())];
        $have = T('n_count')->get_one($where);
        if (!$have) {
            // $where['zds'] = $money;
            // $where['zxh'] = $money;
            $where[$key] = 1;
            $where['addtime'] = time();
            T('n_count')->add($where);
        } else {
            $u[$key] = $have[$key] + 1;
            //$u['zxh'] = $have['zxh'] + $money;
            // $u['qdrs'] = $have['qdrs'] + 1;
            T('n_count')->update($u, $where);
        }
        return true;
    }
    public function _dayorder()
    {
        $type = getdevicetype(Y::$wrap_head);;
        // $w['uid'] = $uid;
        // $w['dates'] = date('Ymd');
        $pre = 'zdd';
        if ($type == 'android') {
            $key = "a" . $pre;
        } else {
            $key = "i" . $pre;
        }
        if ($type == 'wap') {
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
            $u[$key] = $have[$key] + 1;
            T('n_day_recharge')->update($u, $where);
        }
        return true;
    }
    public function _task($uid, $type, $coin)
    {

        // $conf=Y::$newconf;
        // 'day_share' => 1,
        // 'day_read'  => 2,
        // 'invite_task' => 3,
        // 'agent_task'  =>  4,
        // 'day_advert'  =>  5,
        // 'sign_task'  => 6,
        // 'vip_open'    => 7,
        // 'question_task'    => 8,
        $readad = false;
        switch ($type) {
            case '6':
                $k1 = 'qdsr';
                $k2 = 'qdjbs';
                break;
            case '3':
                $k1 = 'yqcgs';
                $k2 = 'yqjlzjb';
                break;
            case '1':
                $k1 = 'fqfxs';
                $k2 = 'fxjlzjb';
                break;
            case '5':
                $k1 = 'kspcgs'; //成功次数
                $k2 = 'kspjljb';

                $w2['uid'] = $uid;
                $w2['dates'] = '-' . date('Ymd');
                if (!T('n_dotask')->get_one($w2)) {
                    // $is = true;
                    $readad = T('n_dotask')->add($w2);
                }
                break;
            case '2':
                $k1 = 'ydrs';
                $k2 = 'ydjbs';
                break;
            case '8':
                $k1 = 'wjdtrs';
                $k2 = 'wjdtjb';
                break;
            default:
                return false;
                break;
        }
        $where['dates'] = date('Ymd');
        $w2['dates'] = date('Ymd');
        $w2['uid'] = $uid;
        $is = false;
        if (!T('n_dotask')->get_one($w2)) {
            // $is = true;
            $is = T('n_dotask')->add($w2);
        }
        $have = T('n_day_task')->get_one($where);
        if (!$have) {
            $where[$k1] = 1;
            $where[$k2] = $coin;
            $where['zjb'] = $coin;
            if ($is) {
                $where['zrs'] = 1;
            }
            if ($readad) {
                $where['kspzrs'] = 1;
            }
            $where['addtime'] = time();
            T('n_day_task')->add($where);
        } else {
            $u[$k1] = $have[$k1] + 1;
            $u[$k2] = $have[$k2] + $coin;
            if ($is) {
                $u['zrs'] = 1 + $have['zrs'];
            }
            if ($readad) {

                $u['kspzrs'] = 1 + $have['kspzrs'];
            }
            $u['zjb'] = $coin + $have['zjb'];
            T('n_day_task')->update($u, $where);
        }
    }

    public function _taskvideo()
    {
        $k1 = 'kspzrs';
        $where['dates'] = date('Ymd');
        $have = T('n_day_task')->get_one($where);
        if (!$have) {
            $where[$k1] = 1;

            $where['addtime'] = time();
            T('n_day_task')->add($where);
        } else {
            $u[$k1] = $have[$k1] + 1;
            // $u[$k2] = $have[$k2] + $coin;
            T('n_day_task')->update($u, $where);
        }
    }
    public function _appusernum($uid)
    {
        $w2['dates'] = date('Ymd');
        $w2['uid'] = $uid;
        $is = false;

        if (!T('n_loginuser')->get_one($w2)) {
            $is = true;
            if (T('n_loginuser')->add($w2)) {
                $k1 = 'apprs';
                $where['dates'] = $w2['dates'];
                $have = T('n_day_task')->get_one($where);
                $this->log($have);
                if (!$have) {
                    $where[$k1] = 1;

                    $where['addtime'] = time();
                    T('n_day_task')->add($where);
                } else {
                    $u[$k1] = $have[$k1] + 1;
                    T('n_day_task')->update($u, $where);
                }
                // $this->log('写入');
            }
        } else {
            return false;
        }
    }
}
