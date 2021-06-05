<?php

namespace ng169\control\admin;

use ng169\control\adminbase;

checktop();
class user extends adminbase{
	private $db_name = 'user';
	private $key = 'uid';
	private $gd = array('type'=>1);
	private $allkey = array(
		'int'              =>array('flag'  ,'countryid'=>1 ,'vip','viptime','likes','gender'=>1,'age'),
		'string'=>array('username'=>1 ,'headimg'=>1  ),        );
	private $attr = array(

		'string'=>array(
			'address',
			'gs',
			'jy',
			
			'zw',
			'brothday',
			'about',
			
		),        );

	public
	function control_run(){
		
		$c     = D_MEDTHOD;    $a     = D_FUNC;
		$model = T($this->db_name)->order_by(array('f'=>'uid','s'=>'down'))->set_global_where('flag!=1');
		$this->init_like(['name']);
		$model     = $this->init_where($model);
		$model     = $this->init_order($model);
		$page      = $this->make_page($model);
		$data      = $model->set_limit($this->get_page_limit())->get_all();


		$var_array = array('data'    =>$data,'page'=>$page);
		
		$this->view(null,$var_array);
	}
	public
	function control_add(){
		if($_POST){
			$get=get(['string'=>['name'=>1,'acountid'=>1,'devices'=>1,'flag'=>1,'countryid'=>1,'username'=>1,'item'=>1]]);
			/*if(T('user')->get_one(['acountid'=>$get['acountid']])){
				error('账号已经存在');
			}*/
			$get['addtime']=time();
			if(T('user')->add($get)){
				out('添加成功',geturl(null,'run','user'));
			}else{
				error('创建失败');
			}
		}
		$this->view('',$var_array);
	}
	public
	function control_show(){
		$c     = D_MEDTHOD;    $a     = D_FUNC;
		$where = G(array('int'=>array($this->key=>1)))->get();
		$where1=['v.uid'=>$where['uid']];
		$model = T($this->db_name)->join_table(array('t'=>'user_attr','uid','uid'));
		$data = $model->get_one($where1);
		if(!$data){
			YOut::page404();
		}
		if($_POST){
			$get=get(['int'=>['flag'=>1]]);	
			if(T($this->db_name)->update($get,$where)){
				M('user','im')->delusercache($where['uid']);
				out('修改成功');
			}else{
				out('修改失败');
			}
		}
		$var_array = array('data'=>$data);
		$this->view(null,$var_array);
	}
	public
	function control_del(){

		$w = array($this->key=> 1);
		$where = G(array('array'=> $w))->get();
		/*$where1 = array_merge($where,array('status'=>0));*/
		/*$where=implode(',',$where[$this->key]);
		$where=$this->key." in (".trim($where,',').")";*/
		$in = T($this->db_name)->get_all($where);
		
		

		if(sizeof($in) == 0){
			error('用户不存在');
		}
		/*T($this->db_name)->delfile($where,array('headimg'));*/
		$model = T($this->db_name)->del($where);
		/*M('log','am')->log($model, $where);*/
		out('删除成功',null,$model);
	}
	public
	function control_save(){
		if($_POST){
			$get=get(['string'=>['name'=>1,'acountid'=>1,'devices'=>1,'flag'=>1,'countryid'=>1,'username'=>1,'item'=>1]]);
			$where=get(['int'=>['uid'=>1]]);
			if(!T('user')->get_one($where)){
				Out::page404();
			}
			$get['addtime']=time();
			if(T('user')->update($get,$where)){
				out('修改成功',geturl(null,'run','user'));
			}else{
				error('创建失败');
			}
		}
		$this->view('',$var_array);
	}
	
	public function control_excel(){
		header ( "Content-type:application/vnd.ms-excel" );
		header ( "Content-Disposition:filename=csat.xls" );
		echo "<!DOCTYPE html PUBLIC '-//W3C//DTD XHTML 1.0 Transitional//EN' 'http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd'>
		<html xmlns='http://www.w3.org/1999/xhtml'>
		<head>
		<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />
		</head>
		<body>
		{$_POST['html']}
		</body>
		</html>
		";
	}
}
?>
