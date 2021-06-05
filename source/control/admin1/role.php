<?php
namespace ng169\control\admin;

use ng169\control\adminbase;
checktop();
class role extends adminbase{
	public function control_add(){

		if($_POST){
			$get=get(array('string'=>array('rolename'=>1,'parentid','orders','creatid'),'array'=>array('profiles')));
			$get['profiles']=implode(',',(M('power','am')->check($get['profiles'])));
			$get['addtime']=$_SERVER['REQUEST_TIME'];
			$get['creatid']=parent::$wrap_admin['adminid'];
			$p=T('admins_roles')->get_one(array('roleid'=>$get['parentid']));
			if($p && $get['parentid']){
				$get['depath']=$p['depath']+1;
			}else{
				$get['depath']=0;
			}
			$id=get(array('int'=>array('roleid')));
			if($id['roleid']){
				if($id['roleid']==$get['parentid'])error('上级角色不能是本身,角色更新失败');
				$f=T('admins_roles')->update($get,$id);
				
				upcache();
				if($f)out('角色更新成功');
				error('角色更新失败');
			}else{
				$f=T('admins_roles')->add($get);
				upcache();
				if($f)out('角色创建成功');
				error('角色创建失败');
			}
			
		
		}
		$list=M('power','am')->listall('admin');
		$this->view(null,array('action'=>$list));
	}
	public function control_run(){
		$c=D_MEDTHOD;	$a=D_FUNC;
		$model=T('admins_roles');
		$data= $model->get_child('roleid');
		$var_array=array($c=>$data);
		$this->view(null,$var_array);
	}
	public function control_show(){
		$get=get(array('int'=>array('roleid'=>1)));
		$list=M('power','am')->listall('admin');
		
		$profiles=T('admins_roles')->get_one($get);
		$userpower=explode(',',$profiles['profiles']);
		$var_array=array('data'=>$profiles,'action'=>$list,'userpower'=>$userpower);
		$this->view(null,$var_array);
	}

 public function control_del(){
		/*
		$list=M('power','am')->listall('admin');
		
		$profiles=T('admins_roles')->get_one($get);
		$userpower=explode(',',$profiles['profiles']);
		$var_array=array('data'=>$profiles,'action'=>$list,'userpower'=>$userpower);*/
		$where=get(array('int'=>array('roleid'=>1)));
//		T('admins_roles')->delfile($where,array('drawimg','thumbimg'));
       
		$model = T('admins_roles')->del($where,'in');
		/*  M('log','am')->log($model, $where);*/
		upcache();
		out('删除成功',null,$model);
	}
 
	//显示租


	//显示当前可选权限
	//添加组；
	//删除组



}

?>