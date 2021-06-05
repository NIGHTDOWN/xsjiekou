<?php


namespace ng169\control\admin;

use ng169\control\adminbase;
use ng169\tool\Out as YOut;
use ng169\tool\Request as YRequest;

checktop();

class admins extends adminbase{
	private $db_name = 'admins';
	private $key = 'adminid';
	private $allkey = array('int'      =>array('flag',
			'super',
			'qq',
			'mobile','roleids'
		),
		'string'=>array('address'    ,
			'adminname'=>1,
			'password' =>5,
			'alias'        ,
			'tel'            ,
			'remark'      ,
			'weixin'      ,'email'));
	public function control_run(){

      
		$c         = D_MEDTHOD;    $a         = D_FUNC;

		$model     = T($this->db_name)->join_table(array('t'=>'admins_roles','roleids','roleid'));

		$model     = $this->init_where($model);


		$model     = $this->init_order($model);


		$page      = $this->make_page($model);


		$data      = $model->set_limit($this->get_page_limit())->get_all();

      


		$var_array = array($c    =>$data,'page'=>$page);
		$this->view(null,$var_array);
	}
	public function control_add_view(){
		
		$this->view();
	}
	public function control_show(){
		
		$c     = D_MEDTHOD;    $a     = D_FUNC;
		$where = G(array('int'=>array($this->key=>1)))->get();
		$mod = T($this->db_name);
		$data= $mod->get_one($where);
		if(!$data){
			YOut::page404();
		}
		M('log','am')->log($data,$where);
		$var_array = array($c=>$data);
		$this->view(null,$var_array);

	}



	public function control_add(){
		if(!$_POST){
			YOut::redirect(geturl(null,'add_view'));
		}
    	
		
		$mod    = T($this->db_name);
		$insert = G($this->allkey)->get();
		if($mod->check_exist(array('adminname'=>$insert['adminname']))){
			out('账号已经存在',NULL,0);
		}
		if($insert['password'] == ''){
			out('密码不能为空',null,0);
		}
		$t    = time();
		$more = array('regtime'=>$t,'regip'  =>YRequest::getip(),'creatid'=>parent::$wrap_admin['adminid'],'addtime'=>$t);
		$insert = array_merge($insert,$more);
		
		$flag   = $mod->add($insert);
		M('log','am')->log($flag,null,$insert);
		if($flag){
			out('添加成功');
		}else{
			out('添加失败',null,0);
		}
	}

	public function control_save(){
		
		$mod   = T($this->db_name);
		$where = G(array('int'=>array($this->key=>1)))->get();
		unset($this->allkey['string']['password']);
		$insert = G($this->allkey)->get();
		$flag   = $mod->updata(array('v'=>$insert,'w'=>$where));
		M('log','am')->log($flag,null,$insert);
		if($flag){
			out('保存成功');
		}else{
			out('保存失败',null,0);
		}

	}
	public function control_cgthispwd(){
		if($_POST){
			/**/
		$mod   = T($this->db_name);
	
		$pw = array('string'=>array('password'=>5));
		$oldpw = array('string'=>array('old'=>5));
		$oldpw=get($oldpw);
		$pw=get($pw);
		/*d(parent::$wrap_admin);*/
		/*d($oldpw);
		d(parent::$wrap_admin,1);*/
		if($oldpw['old']==parent::$wrap_admin['password']){
			$flag=$mod->update($pw,array('adminid'=>parent::$wrap_admin['adminid']));
		}else{
			error('修改失败');
		}
		if($flag){
			out('修改成功');
		}else{
			error('修改失败');
		}
		}
		$this->view();

	}
	public function control_del(){
		
       
		$w = array($this->key=> 1);
        
		$where = G(array('array'=> $w))->get();
       
		if(sizeof($where) == 0){
			$where = G(array('int'=> $w))->get();
			if(Y::$warp_admin['adminid']==$where['adminid'])error('不能删除自己的账号');
		}
		$model = T($this->db_name)->del($where);
		M('log','am')->log($model, $where);
		out('删除成功',null,$model);
	}
	public function control_cgpwd(){
		
		$c     = D_MEDTHOD;    $a     = D_FUNC;
		$mod   = T($this->db_name);


		$where = G(array('int'=>array($this->key=>1)))->get();
		if(!$_POST){
			$this->view(null,array($c=>$where));
			return 0;
		}


		$pw = array('string'=>array('password'=>5));
		$insert = G($pw)->get();



		$flag   = $mod->updata(array('v'=>$insert,'w'=>$where));

	


		if($flag){
			out('修改成功');
		}else{
			out('修改失败',null,0);
		}
	}

}

?>
