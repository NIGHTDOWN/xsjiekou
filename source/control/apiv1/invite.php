<?php
namespace ng169\control\api;


use ng169\control\apiv1base;
use ng169\tool\Out;
checktop();

require API . 'facebookbm/vendor/autoload.php';

class invite extends apiv1base
{

    protected $noNeedLogin = ['ranking'];

    public function control_index()
    {        
        $info=T('n_user_invite')->set_field('num,coin')->get_one(['uid'=>$this->get_userid()]);
        if(!$info){
			Out::jout(['num'=>0,'coin'=>0]);
		}
        Out::jout($info);
    }
    public function control_ranking()
    {

        $info=T('n_user_invite')->set_field('nickname,avater,num,coin')->order_by('coin desc')->set_limit(5)->get_all();
        if(sizeof($info)==0){
            $info=T('user_invite')->set_field('sum(icon) as coin,count(u_id) as num, nickname,avater')->group_by('u_id')->order_by('coin desc')->set_limit(5)->join_table(['t'=>'third_party_user','u_id','id'])->get_all();
        }
        Out::jout($info);
    }    
    public function control_friend()
    {
        $get=get(['int'=>['page']]);
        $page=$get['page'];
        $info=T('n_invite')->join_table(['t'=>'third_party_user','uid','id'])
        ->set_where(['pid'=>$this->get_userid()])
        ->set_field('nickname,avater,uptime,coin')->order_by('uptime desc')->set_limit([$page['page'],10])->get_all();
        Out::jout($info);
    } 
    public function control_reward_record()
    {
        $get=get(['int'=>['page']]);
        $page=$get['page'];
        $info=T('invite_list')
        ->set_where(['pid'=>$this->get_userid()])
        ->set_field('uidname,addtime,coin,type')->order_by('addtime desc')->set_limit([$page['page'],10])->get_all();
        foreach($info as $k=>$val){
           // 12/3/2022 18:23
             $info[$k]['addtime']=date('m/d/Y H:i');
         }
        Out::jout($info);
    } 
}
