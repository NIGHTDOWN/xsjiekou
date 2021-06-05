<?php




namespace ng169\control;
use ng169\control\general;
use ng169\TPL;
use ng169\tool\Cookie as YCookie;
use ng169\tool\Out as YOut;
checktop();

im(CONTROL.'public/night.php');
class adminbase extends general{

	public $tpl_path = 'tpl/admin/';

	public $pagesize = 20;
	
	public $admin_cookie_name = 'admininfo';
	public function _getarea(){
		$m = M('city', 'im');
		return $m->_getarea();
	}

	public function issuper(){
		return false;

	}
	#Initialization
	public function __construct(){
		@$this->init($this->tpl_path,$this->pagesize,$this->log_dbname);
$this->init_chattype();
		$c = D_MEDTHOD;    $a = D_FUNC;

		if($c != 'login' && !(D_MEDTHOD == 'cj' ) && (D_MEDTHOD != 'aysn') ){
			$this->checkLogin();
		}
	if(isset($m_login))unset($m_login);
	if(isset($login['password']))unset($login['password']);	
		$c=D_MEDTHOD;	$a=D_FUNC;
		$var_array = array(
			'admintpl'    => PATH_URL.$this->tpl_path,
			'realadmintpl'=> ROOT.$this->tpl_path,
			'login'       => @$login,
			'time'        => $_SERVER['REQUEST_TIME'],
			'c'           => $c,
			'a'           => $a,
			'admin'       =>parent::$wrap_admin,
			'level'=>$this->initlevel(),
			'city'        => @$city,
			'jbtype'=>explode(',',@parent::$conf['jb_type']),
			'country'=>$this->getcountryid(),
			'item'=>['未设项目','醉爱','爱看','嗨阅'],
			'c'=>$c,
			'a'=>$a,
			);
		TPL::assign($var_array);
	}
	public function get_admin_cookie(){
		return  $this->_getcookie($this->admin_cookie_name);
	}
	public function save_admin_cookie($admin_data_info){
		$this->_savecookie($this->admin_cookie_name,$admin_data_info);
	}
	public function clear_admin_cookie(){
		$this->_delcookie($this->admin_cookie_name);
	}
	public function getcountryid(){
		$country=[
		1=>'泰国',
		2=>'越南',
		3=>'印尼',
		4=>'马来西亚',
		5=>'中国',
		6=>'英国',
		
		];
		return $country;
	}
	public function checkLogin(){

		$admin_cookie = $this->get_admin_cookie();

		if(!empty($admin_cookie)){
			$admin   = T('admins');

			$db_data = $admin->join_table(array('t'=>'admins_roles','roleids','roleid'))->get_one(array('v.adminname'=> $admin_cookie['adminname']));

			if($db_data == null){
				YOut::redirect(geturl(null,'run','login','admin'));
			}
			if($admin_cookie['password'] != $db_data['password'] || @$db_data['adminstatus'] ==
				'1'){
				YOut::redirect(geturl(null,'run','login','admin'));
			} else{
				parent::$wrap_admin = $db_data;
				TPL::assign(array('login'=>array('admin'=>parent::$wrap_admin)));
				if(!M('power','am')->checkuser())error('无操作权限');
			}
		} else{
			YOut::redirect(geturl(null,'run','login','admin'), 0);
		}

	}


	public function powerlist(){
		$gid = parent::$wrap_admin['gid'];
		$gid = array('v.gid'=> $gid);

		$c     = D_MEDTHOD;    $a     = D_FUNC;

		$group = parent::model('group')->setfiledr(array('v.*','grouppower.actionid'))->
		jointable(array(
				't'=> 'grouppower',
				'gid',
				'gid'), 1);
		$groupinfo = $group->getone($gid);
		$powerinfo = explode(',', $groupinfo['actionid']);
		$powerlist = parent::model('action');
		foreach($powerinfo as $key => $id){

			if($id != null){


				$ar = array(
					'actionid'=> $id,
					'model'   => $c,
					'action'  => $a);

				$powerinfo[$key] = $powerlist->getone($ar);
			} else{
			}

		}
		$var_array = array('data'     => $groupinfo,'powerinfo'=> $powerinfo);

		TPL::assign($var_array);
		TPL::display($this->cptpl . 'groupdetail.tpl');
	}

	public function getupdir(){
		$model = T('siteinfo');
		$updir = $model->get_one(array('attributetype'=> updir));
		$this->updir = $updir['value'];

	}




	public function _initwhere($table){



		$havewhere = YRequest::getPost('condition') ? YRequest::getPost('condition') :
		YRequest::getGet('condition');


		switch($havewhere['condition']){
			case null:

			$w = $this->_getwhere();

			$table->set_where($w);

			break;

			case '0':

			$this->_delwhere();

			break;

			case '1':

			$filed_arr = $table->get_filed();


			$w         = G(array('array'=> $filed_arr))->get();

			$this->_savewhere($w);

			$table->set_where($w);


			break;

		}

		parent::$wrap_where = $w;
		$var_array  = array('where'    => $w,'whereflag'=> sizeof($w));
		TPL::assign($var_array);
		return $table;
	}
}

?>
