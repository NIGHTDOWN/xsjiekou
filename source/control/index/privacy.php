<?php
namespace ng169\control\index;
use ng169\control\indexbase;
use ng169\tool\Url as YUrl;
use ng169\service\Output;
use ng169\tool\Out;
use ng169\Y;
checktop();
class privacy extends indexbase{

	protected $noNeedLogin = ['*'];
	//获取分类
	public function control_index(){
		$this->view('index');
	}public function control_tool(){
		$this->view('tool');
	}
	public function control_help(){
		$this->view('help');
	}
	public function control_helps(){
		$this->view('helps');
	}
	public function control_charge(){
		$this->view('charge');
	}
	public function control_icharge(){
		$this->view('icharge');
	}
	public function control_momo(){
		$this->view('momo');
	}
	public function control_announcement(){
		$this->view('announcement');
	}
	public function control_ten(){
		$this->view('ten');
	}
	public function control_copyright(){
		$this->view('copyright');
	}

	public function control_writer(){
		$this->view('writer');
	}
}
?>
