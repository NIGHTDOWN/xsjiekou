<?php


namespace ng169\control\admin;

use ng169\control\adminbase;
use ng169\TPL;

checktop();
class backstagemenu extends adminbase{
	private $db_name="backstage_menu";
	private $key="catid";
	/*public function __construct()
	{
	parent::__construct();
	}*/
	public function control_run(){
		$c=D_MEDTHOD;	$a=D_FUNC;
		$model = T($this->db_name);
		$data= $model->order_by(array('f'=>array('orders'),'s'=>'up'))->get_child('catid');
		$var_array = array($c=> $data);
		$this->view(null,$var_array);
    
	}
	public function control_edit(){
		$where=G(array('int'=>array('catid'=>1)))->get();
		$model = M($this->db_name,'am');
		$p = $model->getone($where);
		$var_array = array('menu' => $p);
		$this->view(null,$var_array);
	}
	
	public function control_add(){
		$select=G(array('int'=>array('parentid')))->get();
      
		$var_array=array('select'=>$select);
		TPL::assign($var_array);
		if(!$_POST){
			$this->view(null);
			return ;
		}
		$table = T($this->db_name);
		
		$insert=array('string'=>array('catname'=>1,'url','mod','action','orders'),'int'=>array('parentid','flag'));
		$recvar = G($insert)->get();
  
		$status = M($this->db_name,'am')->save($recvar);

		if($status){
			upcache($this->db_name);
			out('添加成功',null,1);

		}
		else{
			out('添加失败',null,0);
		}
	}
	
   
	public function control_del(){
		$w = array($this->key => 1);
		$v= G(array('array' => $w))->get();
        
		$where=array($this->key=>$v[$this->key]);
		if(sizeof($where) == 0){
			$where = G(array('int' => $w))->get();
		}
		$where[$this->key]=T($this->db_name)->get_all_tree_id($where[$this->key],$this->key);
     
		$model = T($this->db_name)->del($where,'in');
		upcache();
      
		out('删除成功',null,$model);
	}      
	public function control_initpower(){
	 	\ng169\tool\Init::mod('admin',CONF.'/admin.inc.php');
	}
}

?>
