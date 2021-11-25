<?php

namespace ng169\control\index;
use ng169\control\indexbase;
use ng169\tool\Image as YImage;
use ng169\tool\Code ;
use ng169\tool\Cookie as YCookie ;
use ng169\tool\Out as YOut ;
use ng169\Y;
checktop();
class login extends indexbase{
	private $mod = null;
	public function control_run(){
		// $this->vlog();
		$this->view();
	}
	
	public function control_login(){
		if($_POST){
			check_verifycode(intval($_POST['yzm']),0);
			
			
			$server = G(array('string' => array('username' => 1, 'password' => 1)));
			$getinfo = $server->get();
			$Muser = T('user');
		
			/*$userinfo = $Muser->set_where("mobile='" .$getinfo['username']."'")->get_one();*/
			
			if(preg_match('/^\w+((-\w+)|(\.\w+))*\@[A-Za-z0-9]+((\.|-)[A-Za-z0-9]+)*\.[A-Za-z0-9]+$/', $getinfo['username'])){
				
				$userinfo = $Muser->set_where("username='" .$getinfo['username']."'")->get_one();
			}else{
				
				$userinfo = $Muser->set_where("mobile='" .$getinfo['username']."'")->get_one();
			}
			
			if(1){
				/*$userinfo=$userinfo[0];*/
				if($userinfo == null){
					$status = 0;
					$mark = '用户不存在!';
					YCookie::del('userinfo');
				} elseif($userinfo['password'] != md5($getinfo['password'])){
					$status = 0;
					$mark = '密码错误!';
					YCookie::del('userinfo');
				} elseif($userinfo['flag'] != 0){
					$status = 0;
					$mark = '帐号已经被列入黑名单!';
					YCookie::del('userinfo');
				}
				else{
					$status = 1;
					$mark = '登录成功!';
					$url = geturl(null,'run','index','user');
					$this->savecookie($userinfo); 
				}
				if($status){
					
					$info = $this->getcurrentinfo();
					$in = array('logtime' => $info['addtime'], 'logip' => $info['ip'],'logtimes'=>$userinfo['logintimes']+1);
					$Muser->updata(array('v' => $in, 'w' => array('uid'=>$userinfo['uid'])));
					
					T('loginlog')->add(array('ip'=>$info['ip'],'addtime' => $info['addtime'],'uid'=>$userinfo['uid']));
				}
		
				out($mark, $url,1);
			}
		}else{
			YOut::redirect(geturl());
		}
	}
    
	public function control_logout(){
		if(!empty(parent::$wrap_user)){
			$this->log('1', parent::$wrap_user['userid']);
		}
		Y::loadTool('cookie');
		YCookie::del('userinfo');
		YOut::redirect(geturl(null,null,'index','index'), 0);
        
	}
	public function control_forget(){
    	
		if($_POST){
			
			$username=get(array('string'=>array('username'=>1)));
			$username=$username['username'];
			/*	$to = G(array('string' => array('mobile'=> 'ismobile')))->get();*/
			/*if(!$this->isexistuser($to['mobile']))error('帐号已经被使用');*/
			$smspai = Y::import('SMS','tool');
			$m      = M('tmpcode', 'am');
			$code   = $m->make($username);
		
			if($code){
				$msg = M('template','im')->getmsg('sms_code',array('code'=>''.$code.''));
			
				$key = $smspai->send($username,$msg['content']);
				if($key['code'] == 2){
					out('发送成功');
				}else{
					error('发送失败');
				}
			} else{
				error($m->geterror());
			}
		
		}
		$this->vlog();
		$this->view();
	}
	public function control_cgpwd(){
		$info=get(array('string'=>array('username'=>1,'code')));
		if($_POST){
			// check_verifycode(intval($_POST['yzm']),0);
			$long=7200;
			$where=array('who'=>$info['username'],'code'=>$info['code']);
			$codeobj=T('tmpcode');
			$i=$codeobj->order_by(array('f'=>array('addtime'),'down'))->get_one($where);
            
			if($i){
				if(($i['addtime']+$long)<=(time())){
                    
					error('链接失效,请重新获取',geturl(null,'login'),1);
				}
			}else{
				error('链接无效',geturl(null,'login'),1);
			}
		}else{
			error('链接错误',geturl(null,'login'),1);
		}
		if($_POST){
			$insert=get(array('string'=>array('new_password'=>'md5')));
			$u=array('mobile'=>$info['username']);
			$insert1['password']=$insert['new_password'];
			$flag= T('user')->update($insert1,$u);
			$codeobj->del($where);
            
            
			if($flag){msg('修改密码成功',geturl());}else{
				error('修改密码失败',geturl());
			}
		}
		$this->vlog();
		$this->view(null,$info);
	}
	public function control_verify(){
		$get=get(array('int'=>array('w','h')));
		/*Y::loadTool('image');*/
		if(isset($_GET['w'])&&isset($_GET['h'] )){
		
			YImage::verify(null,$get['w'],$get['h']);
		}else{
			YImage::verify();
		}
		
	}
	public function control_verify2(){
		$get=get(array('int'=>array('w','h'),'string'=>['code'=>1]));
		/*Y::loadTool('image');*/
		/*if(isset($_GET['w'])&&isset($_GET['h'] )){
		
			YImage::verify2(null,$get['w'],$get['h']);
		}*/
		if(!$get['code'])error('缺少参数');
		
		$code=Code::encode($get['code'].date('YmdMhi'),'789456234'.date('YmdMhi'));
		$code=substr($code,1,4);
		
		YImage::verify2($code);
	}
	public function control_qr(){
       $get=get(array('string'=>array('url'=>1)));
       
		M('qr','im')->get($get['url']);
	}
}
?>