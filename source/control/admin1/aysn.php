<?php

namespace ng169\control\admin;

use ng169\control\adminbase;

checktop();

Y::loadTool('log');
class aysn extends adminbase{
	public function control_opensock(){
	
		if(YAsyn::issign()){
			
			ignore_user_abort();
			set_time_limit(0); 
	im(LIB.'/class.socket.php');
		$ipx='0.0.0.0';
		$ip=$_SERVER['SERVER_ADDR']?$_SERVER['SERVER_ADDR']:$ipx;
		$ip=$ip=='127.0.0.1'?$ipx:$ip;
		
		socketClass::start($ip,DB_SOCKPOST);
			out('开启成功');
		}else{
			YLog::txt('签名错误，非法访问');
			error('签名错误，非法访问',null,0);
		}
		return 0;
	}
	public function control_opensocktest(){
		/*return false;
		ignore_user_abort();
			set_time_limit(0); 
			error_reporting(E_ALL);*/
			/*d( php_ini_loaded_file() );*/
			$getpath=dirname(php_ini_loaded_file());
			
			/*Loaded Configuration File*/
			$phpbin=$getpath.'/php.exe ';
			$exefile=ROOT.'/opsock.php';
			$do=$phpbin.$exefile;
			/*$sc=shell_exec($phpbin.$exefile);*/
			Y::loadTool('file');
		$sc=YFile::writeFile("/opensock.bat",$do);
		//生成bat文件；
		
			d($sc,1);
	ini_set('display_errors', '1');
	im(LIB.'/class.socket.php');
		$ipx='0.0.0.0';
		$ipx='192.168.1.101';
		$ip=$_SERVER['SERVER_ADDR']?$_SERVER['SERVER_ADDR']:$ipx;
		$ip=$ip=='127.0.0.1'?$ipx:$ip;
		
		socketClass::start($ip,DB_SOCKPOST);
	}
//用户实名审核成功   参数获取uid
public function control_user_check_1(){
		if(YAsyn::issign()){
			$obj=Y::import('process','tool');
			$obj->start();
			out('开启成功');
		}else{
			YLog::txt('签名错误，非法访问');
			error('签名错误，非法访问',null,0);
		}
		return 0;
	}
//用户实名审核失败   参数获取uid
public function control_user_check_0(){
		if(YAsyn::issign()){
			$obj=Y::import('process','tool');
			$obj->start();
			out('开启成功');
		}else{
			YLog::txt('签名错误，非法访问');
			error('签名错误，非法访问',null,0);
		}
		return 0;
	}
//订单发货通知用户 参数获取uid ordernum
public function control_user_product_send(){
		if(YAsyn::issign()){
			$obj=Y::import('process','tool');
			$obj->start();
			out('开启成功');
		}else{
			YLog::txt('签名错误，非法访问');
			error('签名错误，非法访问',null,0);
		}
		return 0;
	}
//订单价格修改 参数获取uid ordernum
public function control_user_product_changecash(){
		if(YAsyn::issign()){
			$obj=Y::import('process','tool');
			$obj->start();
			out('开启成功');
		}else{
			YLog::txt('签名错误，非法访问');
			error('签名错误，非法访问',null,0);
		}
		return 0;
	}	
//用户支付完成通知商户 参数获取muid ordernum
public function control_shop_product_send(){
		if(YAsyn::issign()){
			$obj=Y::import('process','tool');
			$obj->start();
			out('开启成功');
		}else{
			YLog::txt('签名错误，非法访问');
			error('签名错误，非法访问',null,0);
		}
		return 0;
	}	
//用户评价完成通知商户 参数获取 ordernum
public function control_user_product_comment(){
		if(YAsyn::issign()){
			$obj=Y::import('process','tool');
			$obj->start();
			out('开启成功');
		}else{
			YLog::txt('签名错误，非法访问');
			error('签名错误，非法访问',null,0);
		}
		return 0;
	}	
//商户评价完成通知商户 参数获取 ordernum
public function control_shop_comment_user(){
		if(YAsyn::issign()){
			$obj=Y::import('process','tool');
			$obj->start();
			out('开启成功');
		}else{
			YLog::txt('签名错误，非法访问');
			error('签名错误，非法访问',null,0);
		}
		return 0;
	}	
//交易完成 参数获取 ordernum   （ 自动判断确认是用户确认，自动确认，后台确认通知）
public function control_order_sure(){
		if(YAsyn::issign()){
			$obj=Y::import('process','tool');
			$obj->start();
			out('开启成功');
		}else{
			YLog::txt('签名错误，非法访问');
			error('签名错误，非法访问',null,0);
		}
		return 0;
	}
//商户认证失败 获取muid
public function control_shop_check_0(){
		if(YAsyn::issign()){
			$obj=Y::import('process','tool');
			$obj->start();
			out('开启成功');
		}else{
			YLog::txt('签名错误，非法访问');
			error('签名错误，非法访问',null,0);
		}
		return 0;
	}	
//商户认证成功 获取muid
public function control_shop_check_1(){
		if(YAsyn::issign()){
			$obj=Y::import('process','tool');
			$obj->start();
			out('开启成功');
		}else{
			YLog::txt('签名错误，非法访问');
			error('签名错误，非法访问',null,0);
		}
		return 0;
	}	
//采集
public function control_catch(){
		if(YAsyn::issign()){
			
			$word=get(array('string'=>array('word'=>1)));
		Y::loadTool('catchhtml','tool');
		$cj=new catchhtml();
		$cj->start($word['word']);
		}else{
			YLog::txt('签名错误，非法访问');
			error('签名错误，非法访问',null,0);
		}
		return 0;
	}

public function control_catch_pro()
{
	if(YAsyn::issign())
	{



		$word = get(array('int'=>array('s'=>1)));
		Y::loadTool('catchhtml','tool');
		$cj   = new catchhtml();
		$s    = intval($word['s']);

		YLog::txt($s);
		$end  = $s - 5000;
		$zise = 500;
		

for($s ; $s > $end; $s=$s-$zise){
	
		$s -= $zise;
			$data = T('tmp2')->order_by(array('s'=>'down','f'=>'id'))->join_table(array('t'=>'tmp1','lid','lid'))->set_where('flag=1  ')->set_limit(array($s,$zise),'id','<')->get_all(null,1);

foreach($data as $ll){
	
	$cj->startpro($ll['tid']);
}
			
			YLog::txt($data['tid']);
}


	}
	else
	{
		YLog::txt('签名错误，非法访问');
		error('签名错误，非法访问',null,0);
	}
	return 0;
}


}
?>
