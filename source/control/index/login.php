<?php

namespace ng169\control\index;

use ng169\control\indexbase;
use ng169\tool\Image as YImage;
use ng169\tool\Request;
use ng169\tool\Code;
use ng169\tool\Cookie as YCookie;
use ng169\tool\Out;
use ng169\Y;

checktop();
class login extends indexbase
{
	private $mod = null;
	public function control_run()
	{
		if ($_POST) {
			check_verifycode(intval($_POST['code']), 0);
			$get = get(['string' => ['username' => 1]]);
			$get2 = get(['string' => ['password' => 'md5']]);
			$user = M('user', 'im')->login($get['username'], $get2['password'], 'wap');
			if ($user) {
				$this->savecookie($user);
				Out::redirect(geturl(null, null, 'me'));
			} else {
				Out::out(__('登入失败'), null, 0);
			}
		} else {
			$this->view();
		}
	}
	public function control_reg()
	{
		if ($_POST) {
			check_verifycode(intval($_POST['code']), 0);
			$get = get(['string' => ['username' => 1]]);
			$get2 = get(['string' => ['password' => 'md5']]);
			$in = T('third_party_user')->set_field('id')->set_where(['username' => $get['username']])->get_one();
			if ($in) {
				Out::out(__('用户名已存在'), null, 0);
			}
			$data = M('user', 'im')->createuser($get['username'], $get['username'], $get2['password'], 'wap');
			
			// $user = M('user', 'im')->login($get['username'], $get2['password'], 'wap');
			if ($data) {
				$user = T('third_party_user')->set_where(['id' => $data[0]])->get_one();
				$user['token'] = $data[1];
				$this->savecookie($user);
				Out::redirect(geturl(null, null, 'me'));
			} else {
				Out::out(__('注册失败'), null, 0);
			}
		} else {
			$this->view();
		}
	}


	public function control_logout()
	{
		if (!empty(parent::$wrap_user)) {
			// $this->log('1', parent::$wrap_user['userid']);
		}
		Y::loadTool('cookie');
		YCookie::del('userinfo');
		Out::redirect(geturl(null, null, 'me', 'index'), 0);
	}
	public function control_forget()
	{

		if ($_POST) {

			$username = get(array('string' => array('username' => 1)));
			$username = $username['username'];
			/*	$to = G(array('string' => array('mobile'=> 'ismobile')))->get();*/
			/*if(!$this->isexistuser($to['mobile']))error('帐号已经被使用');*/
			$smspai = Y::import('SMS', 'tool');
			$m      = M('tmpcode', 'am');
			$code   = $m->make($username);

			if ($code) {
				$msg = M('template', 'im')->getmsg('sms_code', array('code' => '' . $code . ''));

				$key = $smspai->send($username, $msg['content']);
				if ($key['code'] == 2) {
					out('发送成功');
				} else {
					error('发送失败');
				}
			} else {
				error($m->geterror());
			}
		}
		$this->vlog();
		$this->view();
	}
	public function control_cgpwd()
	{
		$info = get(array('string' => array('username' => 1, 'code')));
		if ($_POST) {
			// check_verifycode(intval($_POST['yzm']),0);
			$long = 7200;
			$where = array('who' => $info['username'], 'code' => $info['code']);
			$codeobj = T('tmpcode');
			$i = $codeobj->order_by(array('f' => array('addtime'), 'down'))->get_one($where);

			if ($i) {
				if (($i['addtime'] + $long) <= (time())) {

					error('链接失效,请重新获取', geturl(null, 'login'), 1);
				}
			} else {
				error('链接无效', geturl(null, 'login'), 1);
			}
		} else {
			error('链接错误', geturl(null, 'login'), 1);
		}
		if ($_POST) {
			$insert = get(array('string' => array('new_password' => 'md5')));
			$u = array('mobile' => $info['username']);
			$insert1['password'] = $insert['new_password'];
			$flag = T('user')->update($insert1, $u);
			$codeobj->del($where);


			if ($flag) {
				msg('修改密码成功', geturl());
			} else {
				error('修改密码失败', geturl());
			}
		}
		$this->vlog();
		$this->view(null, $info);
	}
	public function control_verify()
	{
		$get = get(array('int' => array('w', 'h')));
		/*Y::loadTool('image');*/
		if (isset($_GET['w']) && isset($_GET['h'])) {

			YImage::verify(null, $get['w'], $get['h']);
		} else {
			YImage::verify();
		}
	}
	public function control_verify2()
	{
		$get = get(array('int' => array('w', 'h'), 'string' => ['code' => 1]));
		/*Y::loadTool('image');*/
		/*if(isset($_GET['w'])&&isset($_GET['h'] )){
		
			YImage::verify2(null,$get['w'],$get['h']);
		}*/
		if (!$get['code']) error('缺少参数');

		$code = Code::encode($get['code'] . date('YmdMhi'), '789456234' . date('YmdMhi'));
		$code = substr($code, 1, 4);

		YImage::verify2($code);
	}
	public function control_qr()
	{
		$get = get(array('string' => array('url' => 1)));

		M('qr', 'im')->get($get['url']);
	}
}
