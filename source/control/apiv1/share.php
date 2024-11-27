<?php
namespace ng169\control\api;
use ng169\control\apiv1base;
use ng169\tool\Url as YUrl;
use ng169\service\Output;
use ng169\tool\Out;
use ng169\lib\Log;
use ng169\Y;
checktop();
class share extends apiv1base{

	protected $noNeedLogin = ['*'];
	
	



	public function control_info(){
		//获取分享语
		$get=get(['int'=>['id'=>1,'type'=>1]]);
		$shareinfo=T('n_share')->get_one(['book_id'=>$get['id'],'type'=>$get['type']]);
		if(!$shareinfo){
			if($get['type']==1){
				$w = ['book_id' => $get['id']];
        $list = T('book')->field('other_name,bpic,`desc`')->where($w)->find();
			}else{
				$w = ['cartoon_id' => $get['id']];
				$list = T('cartoon')->field('other_name,bpic,`desc`,hits,collect')->where($w)->find();
			}			
			$shareinfo['shareimg']=$list['bpic'];
            $shareinfo['sharetitle']=$list['other_name'];
            $shareinfo['sharecontent']=$list['desc'];
		}
	}
	public function control_clikk(){
		//点击埋点
		$get=get(['int'=>['secid'=>1,'type'=>1,'id'=>1]]);
		
	}
}
?>
