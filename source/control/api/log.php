<?php

namespace ng169\control\api;

use ng169\control\apibase;

checktop();
class log extends apibase
{
    protected $noNeedLogin = [];
    public function control_charge()
    {

        $pages = get(['int' => ['page']]);


        $list = T('charge')->field('users_id,charge_icon,send_coin,addtime,charge_type')
            ->where(["users_id" => $this->get_userid()])->order('addtime desc')->set_limit([$pages['page'], 20])->get_all();

        // $ret = ['list' => $list];
        $this->returnSuccess($list);
    }
    public function control_expend()
    {
        $pages = get(['int' => ['page']]);
        $list = T('expend')->field('users_id,section_id,expend_red,expend_time,cart_section_id,cother_name,bother_name,section_title,cart_section_title,expend_type,addtime')
            //->where('date_sub(curdate(), INTERVAL 7 DAY) <= date("local_time")')
            ->where(["users_id" => $this->get_userid()])->order('addtime desc')->set_limit([$pages['page'], 20])->get_all();
        
        $this->returnSuccess($list);
    }
    public function control_record()
    {
        $page = get(['int' => ['page']]);
        $list = T('task_reward_count')->field('users_id,treward_coin,task_time,task_type,addtime')
            //->where('date_sub(curdate(), INTERVAL 7 DAY) <= date("local_time")')
            ->where(["users_id" => $this->get_userid()])->order('task_time desc')->set_limit([$page['page'], 20])->get_all();

        $this->returnSuccess($list);
    }
}
