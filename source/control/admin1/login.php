<?php



namespace ng169\control\admin;

use ng169\control\adminbase;
use ng169\Y;
use ng169\tool\Request as YRequest;
use ng169\tool\Out as YOut;
checktop();
class login extends adminbase
{
	
    private $db_name='admins';
    private $mod;
    private function  load_db(){
        $this->mod=T($this->db_name);
    }
    
	
	public function control_run()
	{
	
        $this->view();
	}
	
	public function control_login()
	{
    
        $this->load_db();        
        $recv_obj = G(array('string' => array('username' => 1, 'password' => 1), 'int' =>
                array('checkcode' => 1), ));
        $recv = $recv_obj->get();

        /*check_verifycode($recv['checkcode'],0);*/
          
        $url = geturl(null,'run','frame','admin');
	
        $ret=M('login','am')->login($recv);
		
		
        if($ret['flag']){
//        	M('iplog','am')->log(parent::$wrap_admin['adminid'],$_SERVER['REMOTE_ADDR'],1);
            $updata=array('logintimes'=>parent::$wrap_admin['logintimes']+1,'logintime'=>time(),'loginip'=>YRequest::getip());
            $where=array('adminname'=>$recv['username']);
            T($this->db_name)->updata(array('v'=>$updata,'w'=>$where),false);
            $this->save_admin_cookie(parent::$wrap_admin);
        }
      
        out($ret['mark'],$url,$ret['flag']);
	}
	
	public function control_logout()
	{
        $this->clear_admin_cookie();
       
		YOut::redirect(geturl(null,'run','login','admin'), 0);
	}


}

?>
