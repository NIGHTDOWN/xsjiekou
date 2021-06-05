<?php


namespace ng169\control\admin;

use ng169\control\adminbase;
use ng169\Y;
use ng169\tool\Url as  YUrl;;
checktop();

class siteset extends adminbase{
	private $mod = null;

	public function control_sock(){
	
	$url=geturl(null,'opensock','aysn');
	
	YAsyn::start($url);
	out('执行成功');
	}
	public function control_closesock(){
	im(LIB.'/class.socket.php');
	$bool=socketClass::phpsend('127.0.0.1',DB_SOCKPOST,array('action'=>'close','msg'    =>'closesock'));
	out('已经关闭WEBSOCK');
	}
	public function control_tz(){

	/*im(LIB.'/class.socket.php');
	$bool=socketClass::phpsend(getip(),DB_SOCKPOST,array('action'=>'wxpay','orderid'    =>'ng16918042314552019342'));
	error('发送通知');*/
	}
	public
	function control_clearcache(){
		\ng169\TPL::clearComplied();
		out('清除成功',geturl(null,'run','frame','admin') , 1,1);
	}
	public
	function control_updatecache(){
		upcache();
		out('清除成功',geturl(null,'run','frame','admin') , 1,1);
	}
	public
	function control_smsset(){
		
		$model     = M('options','am');
		$data      = $model->get_smsset();
		$var_array = array('options'=> $data);
		$this->view(null,$var_array);
	}
public
	function control_withdraw(){
		
		$model     = M('options','am');
		$data      = $model->get_smsset();
		$var_array = array('options'=> $data);
		$this->view(null,$var_array);
	}
	public
    function control_smsset_getnum()
    {
		$this->issuper();
        $get = G(array('string' => array('smsname',
                    'smspassword','smssender' ,'api')))->get();

        $smspai = Y::import('SMS','tool');
       
        $key    = $smspai->getinfo($get); 
       
        if ($key['code'] == 2) {
            out($key['num']);
        }else {
            out($key['msg'],null,0);
        }
    }
    public
    function control_smsset_try()
    {
        if ($_POST) {
            $get = G(array('string' => array('smsname','smspassword','smssender','smstophone' ,'api')))->get();
            $to = G(array('string' => array('smstophone'=> 1)))->get();
            $smspai = Y::import('SMS','tool');
            $m      = M('tmpcode', 'am');
            $code   = $m->make($to['smstophone']);
          
            if ($code) {
				$msg = M('template','im')->getmsg('sms_test',array('code'=>''.$code.''));
				
                $key = $smspai->send($to['smstophone'],$msg['content'],$get['smssender'],$get['smspassword']);
                 
                if ($key['code'] == 2) {
                    out('发送成功');
                }else {
                    out($key['msg'],null,0);
                }
            } else {
                error($m->geterror());
            }

        }
    }
	public
	function control_mailset(){
		
		$model     = M('options','am');
		$data      = $model->get_mailset();
		$var_array = array('options'=> $data);
		$this->view(null,$var_array);
	}
	public
	function control_mailset_try(){
		
		if($_POST){
			$get = G(array('string' => array('sendpassword','port','sendmail',
						'sendtype','smtp','sendname','ssl')));
			$mail = $get->get();
			$to   = G(array('string' => array('test_email'=> 1)))->get();
			$m = M('send', 'im');
			$s = $m->sendmail(array('from'=> Y::$conf['site_name'],'to'  => $to['test_email']),
				'mail_test', '', $mail);
			
			if($s){
				out('发送成功',null,1);
			}
		}

	}
	public
	function control_seo(){
		
		$model     = M('options','am');
		$data      = $model->get_seoset();
		$var_array = array('options'=> $data);
		$this->view('siteset_index',$var_array);
	}
	
	public
	function control_upset(){
		
		$model     = M('options','am');
		$data      = $model->get_upset();
		$var_array = array('options'=> $data);
		$this->view(null,$var_array);
	}
	public
	function control_run(){		
		$model     = M('options','am');
		$data      = $model->get_siteinfo();
		$var_array = array('options'=> $data);
		$this->view(null,$var_array);
	}
	public
	function control_order(){		
		$model     = M('options','am');
		$data      = $model->get_order();
		$m1 = T('options')->get_all(array('identify'=>
        'coin','flag'    =>0));
   /*     $m4 = T('options')->get_all(array('identify'=>
        'm4','flag'    =>0));
        $m3 = T('options')->get_all(array('identify'=>
        'm3','flag'    =>0));
        $m2 = T('options')->get_all(array('identify'=>
        'm2','flag'    =>0));*/
		$var_array = array('options'=> $data,'m1'=>$m1);
		$this->view('',$var_array);
	}
	public
	function control_save(){
		
		$model   = M('options','am');
		$bool    = $model->save();        
		$rewrite = intval(getGET('rewrite'));
		if($rewrite != Y::$conf['rewrite'] && isset($_POST['rewrite'])){
			if($rewrite){
				YUrl::load_static();
			}else{
				YUrl::unload_static();
			}
		}
		if($bool){
			out('保存成功',null,1,1);
		}else{
			out('保存失败',null,0,1);
		}
	}
}
?>
