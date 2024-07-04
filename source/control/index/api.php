<?php
namespace ng169\control\index;
use ng169\control\indexbase;
use ng169\tool\Url as YUrl;
use ng169\service\Output;
use ng169\cache\Rediscache;
use ng169\Y;
checktop();
class api extends indexbase{

	protected $noNeedLogin = ['*'];
	public function control_run(){
		
d( getallheaders());
	}
	public function control_token(){
		$get=get(array('int'=>array('username')),array('uid'=>'用户名'));
		$tk=time().random_int(10000,99999);
		$tk=md5($tk);
		$insert=['token'=>$tk,'username'=>$get['username']];
		$uid=T('user')->add($insert);
		$ret=['uid'=>$uid,'token'=>$tk];
		echo json_encode($ret);
	}
	public function control_test(){
		//发消息
		//		\ng169\lib\Socket::phpsend('192.168.6.69',"4444",'sdsasdsasadasdasdsadsdsadasdasdasd');
		$redis=\ng169\cache\Rediscache::getRedis();
		
		$i=30000;
		while($i){
			$i--;
			$index=':sid_5';			
			$servinfo=$redis->get($index);
			
			if(!$servinfo){
				$servinfo=T('sockserver')->get_one(['sid'=>'5','flag'=>1]);
				if($servinfo){
					/*	$servinfo==T('sockserver')->get_one(['sid'=>$tousers['sid'],'flag'=>1]);*/
					//				入缓存；
					$redis->set($index,$servinfo);
				}else{
					return false;
				}
			}
			//转发到对应server执行发送操作
			$msg=[];
			$data=['action'=>'relay','cid'=>'6251','data'=>$msg];
			/*d($servinfo);*/
			\ng169\lib\Socket::phpsend($servinfo['ip'],7777,$data);
			/*return 1;*/
		}
	}
	public function control_createchatid(){
		$id=get(['int'=>['fid','tid']]);
		$time=time();
		$chatid=T('chat')->add(['usernum'=>2,'createtime'=>$time]);
		T('chat_user')->add(['uid'=>$id['tid'],'chatid'=>$chatid,'jointime'=>$time]);
		T('chat_user')->add(['uid'=>$id['fid'],'chatid'=>$chatid,'jointime'=>$time]);
		echo json_encode(['chatid'=>$chatid]);
	}
	
}
?>
