<?php


namespace  ng169\hook;
use ng169\Y;
use ng169\TPL;

checktop();

function get_ads($id,$catid=null,$arealevel=null){
	
	if(!$id)return false;
	$mod=T('ad')->join_table(array('t'=>'ads','adsid','adsid'));
	$where['flag']=0;
	if(is_int($id)){
		$where['adsid']=$id;
	}else{
		$where['identify']=$id;
	}
	if($catid){
		$where['catid']=$catid;
	}
	if($arealevel){
		$where['arealevel']=intval($arealevel);
		switch($arealevel){
			case 1:
			$where['province']=Y::$wrap_city['province'];
			break;
			case 2:
			$where['cityid']=Y::$wrap_city['cityid'];
			break;

		}
	}
	$ads=$mod->get_one($where);
	
	if($ads){
		$w['stime']=time();
		$w['etime']=time();
		$w['adsid']=$ads['adsid'];
		$w['aflag']=0;
		$w2['stime']=time();
		/*$w2['etime']=0;*/
		$w2['adsid']=$ads['adsid'];
		$w2['aflag']=0;
		/*$w3['stime']=0;*/
		$w3['etime']=time();
		$w3['adsid']=$ads['adsid'];
		$w3['aflag']=0;
		/*$w4['stime']=0;
		$w4['etime']=0;
		$w4['adsid']=$ads['adsid'];
		$w4['aflag']=0;*/
		$op=['stime'=>'>=','etime'=>'<='];
		$max=$ads['num']?$ads['num']:10;
		$info=T('ad')->join_table(array('t'=>'ads','adsid','adsid'),1)->join_table(array('t'=>'url_encode','urlid','urlid'),1)->set_limit($max)->set_where($w,$op)->get_all();
		
		$info2=T('ad')->join_table(array('t'=>'ads','adsid','adsid'),1)->join_table(array('t'=>'url_encode','urlid','urlid'),1)->set_limit($max)->set_where($w2,$op)->get_all();
			
		$info3=T('ad')->join_table(array('t'=>'ads','adsid','adsid'),1)->join_table(array('t'=>'url_encode','urlid','urlid'),1)->set_limit($max)->set_where($w3,$op)->get_all();
	
		/*$info4=T('ad')->join_table(array('t'=>'ads','adsid','adsid'),1)->join_table(array('t'=>'url_encode','urlid','urlid'),1)->set_limit(array(0,$max))->get_all($w4)*/;
		
		$info=array_merge($info,$info2,$info3);
		
		if($info && sizeof($info)>0){
			return $info;
		}return false;
	}return false;
}
?>
