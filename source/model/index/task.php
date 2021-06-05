<?php

namespace ng169\model\index;

use ng169\tool\Out;
use ng169\Y;

checktop();

class task extends Y
{
    public function share_record($users_id = '')
    {
        $share_count = T('user_share_record')->where(['users_id' => $users_id, 'share_date' => date('Y-m-d')])->get_one();
        if ($share_count) {
            return 1;
        }
        return 0;
    }

    // 观看广告 任务栏
    public function read_advert($users_id = '')
    {

        $share_count = T('user_read_advert')->set_field('nums')->where(['users_id' => $users_id, 'watch_time' => date('Y-m-d')])->get_one();
        // if ($share_count['num'] >= 5) {
        //     return $share_count['num'];
        // }

        return $share_count['nums'];
        // return 0;
    }

    // 每日阅读 任务栏
    public function day_read($users_id = '')
    {
        $share_count = T('user_day_read')->where(['users_id' => $users_id, 'read_day' => date('Y-m-d')])->get_count();
        if ($share_count) {
            return $share_count;
        }

        return 0;
    }

    // 问卷答题 任务栏
    public function reply($users_id = '')
    {
        $share_count = T('reply')->where(['users_id' => $users_id])->get_one();
        if ($share_count) {
            return 1;
        }
        return 0;
    }
    public function _sign_day($uid, $day, $num, $m, $bqflag)
    {
        // $w = ['users_id' => $uid];
        // $sing = T('mall_sign')->where($w)->get_one();
        // $conf = T('sign')->get_one(['sign_day' => $day]);
        // if (!$conf) {
        //     return false;
        // }
        // $m = $conf['sign_icon'] * $conf['multiple'];
        // $insert = [
        //     'users_id' => $uid,
        //     'sign_coin' => $m,
        //     'num' => $day,
        //     'addtime' => date('Y-m-d'),
        // ];
        // if ($sing) {

        //     T('mall_sign')->update($insert, $w);
        // } else {

        //     T('mall_sign')->add($insert);
        // }
        $w['sign_coin'] = $m;
        $w['num'] = $num;
        $w['addtime'] = time();
        $w['date'] = date('Ym');
        $w['signday'] = $day;
        $w['users_id'] = $uid;
        $w['bqflag'] = $bqflag;
        T('mall_sign')->add($w);
        M('census', 'im')->task_reward_count($uid, $m, 6);
        M('coin', 'im')->add($uid, $m, 'sign_icon');
        return true;
    }
    //签到
    public function sign($uid, $signday)
    {
        if ($signday < 1) {
            Out::jerror('日期错误', null, '110212');
        }

        if ($signday > date('d', strtotime(date('Y-') . (date('m') + 1) . '-01') - 1)) {
            Out::jerror('日期错误', null, '110212');
        }
        $day = date('d');

        $w = ['users_id' => $uid, 'signday' => $signday, 'date' => date('Ym')];
        $sign = T('sign')->get_one(['sign_day' => $signday]);
        $coin = $sign['sign_icon'];
        $user = T('mall_sign')->where($w)->get_one();
        if ($user) {
            Out::jerror('已签到', null, '100212');
        }
        if ($day == $signday) {
            //今日签到（判断上一天是否当月，是否连续）
            $lat = (date("d") - 1);
            $w2 = ['users_id' => $uid, 'signday' => $lat, 'date' => date('Ym')];
            $user2 = T('mall_sign')->where($w2)->get_one();
            if ($user2) {
                //连续签到
                $signnum = $user2['num'] + 1;
                if ($signnum > 5) {
                    $coin = $sign['sign_icon'] * $sign['sign_multiple'];
                    $this->_sign_day($uid, $signday, $user2['num'] + 1, $coin, 0);
                } else {
                    $this->_sign_day($uid, $signday, $user2['num'] + 1, $sign['sign_icon'], 0);
                }
            } else {
                //非连续签到
                $this->_sign_day($uid, $signday, 1, $sign['sign_icon'], 0);
            }
        }
        if ($day > $signday) {
            //补签无双倍（次数减少）
            $bqnum = 5;
            //判断补签次数
            $w3 = ['users_id' => $uid, 'bqflag' => 1, 'date' => date('Ym')];
            $bqsignnum = T('mall_sign')->where($w3)->get_count();
            if ($bqsignnum >= $bqnum) {
                Out::jerror('补签次数不足', null, '100214');
            }
            $lat = ($signday - 1);
            $w2 = ['users_id' => $uid, 'signday' => $lat, 'date' => date('Ym')];
            $user2 = T('mall_sign')->where($w2)->get_one();

            if ($user2) {
                $signnum = $user2['num'] + 1;
                if ($signnum > 5) {
                    $coin = $sign['sign_icon'] * $sign['sign_multiple'];
                    $this->_sign_day($uid, $signday, $user2['num'] + 1, $coin, 1);
                } else {
                    $this->_sign_day($uid, $signday, $user2['num'] + 1, $sign['sign_icon'], 1);
                }


                // $this->_sign_day($uid, $signday, $user2['num'] + 1, $sign['sign_icon'], 1);
            } else {
                $this->_sign_day($uid, $signday, 1, $sign['sign_icon'], 1);
            }
        }

        if ($day < $signday) {
            //补签
            Out::jerror('还未到签到日期', null, '100213');
        }
        return $coin;
        //补签（补签数目）、连续签到双倍、下一月连续请0

        // if (!$user) {

        //     return $this->_sign_day($uid, 1);

        // } else {
        //     if ($user['addtime'] == date('Y-m-d')) {
        //         Out::jerror('今日已经签到', null, '100112');
        //     } else {
        //         $utime = strtotime($user['addtime']); 
        //         $today = strtotime('today');
        //         $xc = $today - $utime;
        //         if ($xc <= 0) {
        //             Out::jerror('签到时间错误', null, '100212');
        //         }
        //         if ($xc > 86400) {
        //             return  $this->_sign_day($uid, 1);
        //         } else {
        //             if ($user['num'] == 7) {
        //                 return $this->_sign_day($uid, 1);
        //             } else {
        //                 return $this->_sign_day($uid, $user['num'] + 1);
        //             }
        //         }
        //     }



        return false;
    }
    // 获取问卷调查
    public function get_question()
    {
        $question = T('question')->get_all();
        // $answer = T('answer')->field('answer_id,answer_title,answer_option,question_id')->select()->toArray();
        foreach ($question as $key => $value) {

            if ($value['question_type'] == 1) {
                $answer = T('answer')->field('answer_id,answer_title,answer_option,question_id')->where(['question_id' => $value['question_id']])->get_all();
                $question[$key]['answer'] = $answer;
            }
        }
        return $question;
    }
}
