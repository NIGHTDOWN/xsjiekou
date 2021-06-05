<?php


namespace ng169\control\admin;

use ng169\control\adminbase;

checktop();
class ucensus extends adminbase{
  
	
	public function control_total(){
		/*$model = T('census')->order_by(array('f'=>'indate','s'=>'down'))->join_table(['t'=>'user','acountid','acountid']);
		$model     = $this->init_where($model);
		$model     = $this->init_order($model);*/
		$gettime2=get(['string'=>['end'=>'time','start'=>'time','username','item']]);
		/*$w2='';*/
		$range=$gettime2['end']-$gettime2['start'];
		$list=[];
		if($_POST){
			if($range<=0){
				error('请选择区间范围');
			}
			if($range>G_DAY*31){
				error('筛选区间不能大于31天');
			}
			$user=$this->getuser();
		}
		$step=$range/G_DAY;
		$start=$gettime2['start'];
		for($i = 0; $i <= $step; $i++){
			$date=$start+$i*G_DAY;
			
			$where=['indate'=>date('Ymd',$date),'acountid'=>$user];
			if($gettime2['item']){
				$where['item']=$gettime2['item'];
			}
			$mod=T('census')->join_table(['t'=>'user','acountid','uid'])->set_field('user.*,v.*');
			$listtmp=$mod->set_where($where)->get_all();
			if(sizeof($listtmp)>0)	{
			foreach($listtmp as $row){
				if(!isset($list[$i]['indate'])){
			
					$list[$i]['indate']=date('Ymd',$date);}
				$list[$i]['fgnum']+=$row['fgnum'];
				$list[$i]['zsnum']+=$row['zsnum'];
				
				$list[$i]['djl']+=$row['djl'];
				$list[$i]['hf']+=$row['hf'];
				$list[$i]['az28']+=$row['az28'];
				$list[$i]['buy28']+=$row['buy28'];
				$list[$i]['buydl28']+=$row['buydl28'];
				$list[$i]['buyzh1']+=$row['buyzh1'];
				$list[$i]['buyzh7']+=$row['buyzh7'];
				$list[$i]['buyzh28']+=$row['buyzh28'];
			}
			$list[$i]['djrate']=$list[$i]['djl']/$list[$i]['zsnum']*100;
			$list[$i]['djrate']=round($list[$i]['djrate'],2);
			
			
			$list[$i]['hs1']=$list[$i]['buyzh1']/$list[$i]['hf']*100;
			$list[$i]['hs1']=round($list[$i]['hs1'],2);
			$list[$i]['hs7']=$list[$i]['buyzh7']/$list[$i]['hf']*100;
			
			$list[$i]['hs7']=round($list[$i]['hs7'],2);
			
			$list[$i]['hs28']=$list[$i]['buyzh28']/$list[$i]['hf']*100;
			$list[$i]['hs28']=round($list[$i]['hs28'],2);
			
			
		}
		}
		
		$var_array = array('data'    =>$list,'gettime'=>$gettime2);	
			
		$this->view(null,$var_array);
		
	}
	
	public function control_country(){
		/*$model = T('census')->order_by(array('f'=>'indate','s'=>'down'))->join_table(['t'=>'user','acountid','acountid']);
		$model     = $this->init_where($model);
		$model     = $this->init_order($model);*/
		$gettime2=get(['string'=>['end'=>'time','start'=>'time','countryid','username','item']]);
		/*$w2='';*/
		$range=$gettime2['end']-$gettime2['start'];
		$list=[];
		if($_POST){
			if(!$gettime2['countryid']){
				error('请选择国家');
			}
			if($range<=0){
				error('请选择区间范围');
			}
			if($range>G_DAY*31){
				error('筛选区间不能大于31天');
			}
			$user=$this->getuser();
		}
		$step=$range/G_DAY;
		$start=$gettime2['start'];
		for($i = 0; $i <= $step; $i++){
			$date=$start+$i*G_DAY;
			$where=['indate'=>date('Ymd',$date),'countryid'=>$gettime2['countryid'],'acountid'=>$user];
			if($gettime2['item']){
				$where['item']=$gettime2['item'];
			}
			$mod=T('census')->join_table(['t'=>'user','acountid','uid'])->set_field('user.*,v.*');
			$listtmp=$mod->set_where($where)->get_all();
			if(sizeof($listtmp)>0)	{
			foreach($listtmp as $row){
				if(!isset($list[$i]['indate'])){
					$list[$i]['indate']=date('Ymd',$date);}
				if(!isset($list[$i]['countryid'])){
					$list[$i]['countryid']=$row['countryid'];}
				$list[$i]['fgnum']+=$row['fgnum'];
				$list[$i]['zsnum']+=$row['zsnum'];
				$list[$i]['djrate']+=$row['djrate'];
				$list[$i]['djl']+=$row['djl'];
				$list[$i]['hf']+=$row['hf'];
				$list[$i]['az28']+=$row['az28'];
				$list[$i]['buy28']+=$row['buy28'];
				$list[$i]['buydl28']+=$row['buydl28'];
				$list[$i]['buyzh1']+=$row['buyzh1'];
				$list[$i]['buyzh7']+=$row['buyzh7'];
				$list[$i]['buyzh28']+=$row['buyzh28'];
			}
			$list[$i]['djrate']=$list[$i]['djl']/$list[$i]['zsnum']*100;
			$list[$i]['djrate']=round($list[$i]['djrate'],2);
			
			
			
			$list[$i]['hs1']=$list[$i]['buyzh1']/$list[$i]['hf']*100;
			$list[$i]['hs1']=round($list[$i]['hs1'],2);
			$list[$i]['hs7']=$list[$i]['buyzh7']/$list[$i]['hf']*100;
			
			$list[$i]['hs7']=round($list[$i]['hs7'],2);
			
			$list[$i]['hs28']=$list[$i]['buyzh28']/$list[$i]['hf']*100;
			$list[$i]['hs28']=round($list[$i]['hs28'],2);
			
			
		}
		}
		$var_array = array('data'    =>$list,'gettime'=>$gettime2);	
			
		$this->view(null,$var_array);
		
	}
	
	public function control_devices(){
		/*$model = T('census')->order_by(array('f'=>'indate','s'=>'down'))->join_table(['t'=>'user','acountid','acountid']);
		$model     = $this->init_where($model);
		$model     = $this->init_order($model);*/
		$gettime2=get(['string'=>['end'=>'time','start'=>'time','countryid','devices','username','item']]);
		/*$w2='';*/
		$range=$gettime2['end']-$gettime2['start'];
		$list=[];
		if($_POST){
			if(!$gettime2['countryid']){
				error('请选择国家');
			}
			if(!$gettime2['devices']){
				error('请选择设备');
			}
			if($range<=0){
				error('请选择区间范围');
			}
			if($range>G_DAY*31){
				error('筛选区间不能大于31天');
			}
			$uid=$this->getuser();
		}
		$step=$range/G_DAY;
		$start=$gettime2['start'];
		for($i = 0; $i <= $step; $i++){
			$date=$start+$i*G_DAY;
			$where=['indate'=>date('Ymd',$date),'countryid'=>$gettime2['countryid'],'devices'=>$gettime2['devices'],'acountid'=>$uid];
			if($gettime2['item']){
				$where['item']=$gettime2['item'];
			}
			$mod=T('census')->join_table(['t'=>'user','acountid','uid'])->set_field('user.*,v.*');
			$listtmp=$mod->set_where($where)->get_all();
			if(sizeof($listtmp)>0)	{
				
				
			foreach($listtmp as $row){
				if(!isset($list[$i]['indate'])){
					$list[$i]['indate']=date('Ymd',$date);}
				if(!isset($list[$i]['countryid'])){
					$list[$i]['countryid']=$row['countryid'];}
					if(!isset($list[$i]['devices'])){
					$list[$i]['devices']=$row['devices'];}
				$list[$i]['fgnum']+=$row['fgnum'];
				$list[$i]['zsnum']+=$row['zsnum'];
				$list[$i]['djrate']+=$row['djrate'];
				$list[$i]['djl']+=$row['djl'];
				$list[$i]['hf']+=$row['hf'];
				$list[$i]['az28']+=$row['az28'];
				$list[$i]['buy28']+=$row['buy28'];
				$list[$i]['buydl28']+=$row['buydl28'];
				$list[$i]['buyzh1']+=$row['buyzh1'];
				$list[$i]['buyzh7']+=$row['buyzh7'];
				$list[$i]['buyzh28']+=$row['buyzh28'];
			}
			$list[$i]['djrate']=$list[$i]['djl']/$list[$i]['zsnum']*100;
			$list[$i]['djrate']=round($list[$i]['djrate'],2);
			
			
			$list[$i]['hs1']=$list[$i]['buyzh1']/$list[$i]['hf']*100;
			$list[$i]['hs1']=round($list[$i]['hs1'],2);
			$list[$i]['hs7']=$list[$i]['buyzh7']/$list[$i]['hf']*100;
			
			$list[$i]['hs7']=round($list[$i]['hs7'],2);
			
			$list[$i]['hs28']=$list[$i]['buyzh28']/$list[$i]['hf']*100;
			$list[$i]['hs28']=round($list[$i]['hs28'],2);
			
			
		}}
		$var_array = array('data'    =>$list,'gettime'=>$gettime2);	
			
		$this->view(null,$var_array);
		
	}
	private function  getuser(){
		$gettime2=get(['string'=>['username']]);
		if(!$gettime2['username']){
				error('请填写归属者');
			}
			$user=T('user')->get_all(['username'=>$gettime2['username']]);
			if(sizeof($user)<=0){
				error('用户不存在');
			}
			$uid=array_column($user,'uid');
			return $uid;
	}
	
}

?>
