<?php

namespace ng169\tool;
class Process{
	private $sleeptime=60;
	public function start(){
		ignore_user_abort(); 
		set_time_limit(0); 
		list($status, $looplock)=Y::$cache->get('looplockprocess');
		if($looplock>=1){
			YLog::txt(date('y-m-d H:i:s').'开启后台进程');
			return false;
		}
		if($looplock==-1){
			Y::$cache->set('looplockprocess',0);
			YLog::txt(date('y-m-d H:i:s').'强制退出后台线程');
			die();
		}
		do{
			$this->loopdo();
	
		}while(true);

	}
	private function loopdo(){
		list($status, $looplock)=Y::$cache->get('looplockprocess');
		if($looplock==-1){
			YLog::txt(date('y-m-d H:i:s').'强制退出后台线程');
			Y::$cache->set('looplockprocess',0);
			die();
		}
		Y::$cache->set('looplockprocess',$looplock+1);
		$time=time();
		$timearray=array($time-(Y::$conf['pd_timeout']*3600+Y::$conf['rob_time']*3600),($time-Y::$conf['pd_timeout']*3600+Y::$conf['parent_showpay']*3600));
		$where=array('type'=>0,'addtime'=>$timearray,'flag'=>0,'out.alloc'=>2,'grab'=>0);
		$list=T('pay')->join_table(array('t'=>'out','oid','oid'),1)->join_table(array('t'=>'user','fromuid','uid'),1)->set_where($where);
		/*$page=$this->make_page($list);*/
		$list= $list->set_limit(100)->get_all();
		if(count($list)==0){
			YLog::txt('无订单');
		}
		foreach($list as $l){
			$this->_sendsms($l['mobile'],array('username'=>$l['realname'],'payid'=>$l['payid']));
			T('pay')->update(array('type'=>1),array('type'=>0,'payid'=>$l['payid']));
		}
		sleep($this->sleeptime);
	}
	private function _sendsms($phone,$array){
		if(Y::$conf['smsflag'] == 0){
			YLog::txt('短信通知接口关闭');
			return 0;
		}
		$msg = M('template','im')->getmsg('sms_xs_wdk',$array);
       
		$smspai = Y::import('SMS','tool');
		$key    = $smspai->send($phone,$msg['content']);
		if($key[0] == 100){
			YLog::txt($phone.$msg['content'].'发送成功');
		}else{
			YLog::txt('发送失败'.$key[0]);
		}
	}
	
}

?>
