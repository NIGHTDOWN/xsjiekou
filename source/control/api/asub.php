<?php

namespace ng169\control\api;

use ng169\control\apibase;
use ng169\lib\Log;
use ng169\tool\Out;
checktop();



class asub extends apibase
{

    protected $noNeedLogin = ['callback'];
    public function control_pay()
    {
		$get=get(['string'=>['ordernum'=>1,'receipt'=>1,'thirdpayid'=>1]]);
		$uid=$this->get_userid();
		$in=M('pay','im')->checksuborder($uid,$get['ordernum']);	
		$data=M('apple','im')->check($get['receipt'],$get['thirdpayid']);
		
		if($data && $data['status']==1){
			//支付成功		
			//M('pay','im')->checkthirdid($get['thirdpayid']);
			M('pay','im')->backvip($this->get_userid(),$get['ordernum'],$get['thirdpayid'],$data['expires_date_ms'],$in['create_syntony']);
			M('order','im')->s2s($get['ordernum']);
			$user=T('third_party_user')->set_field('vip_end_time,isvip')->get_one(['id'=>$this->get_userid()]);
			$user['vip_end_time'] =date('d/m/Y H:i:s',strtotime($user['vip_end_time']));
			Out::jout($user);
		}else{
			//支付失败
			Out::jerror('apple支付失败',null,'10215');
		}
	}
    public function control_callback()
    {
		$get=file_get_contents("php://input");		
		//Log::txt(($get),'apple.txt');
		$get=json_decode($get,1);			
		$receipt=$get['latest_receipt'];
		$original_transaction_id=$get['latest_receipt_info']['original_transaction_id'];
		$transaction_id=$get['latest_receipt_info']['transaction_id'];
		$product_id=$get['latest_receipt_info']['product_id'];
		// $product_id=explode('.',$product_id);
		// $sid=$product_id[2];		
		$data=M('apple','im')->check($receipt,null);		
		//判断是否初次请求
		//Log::txt(($data),'aikan.txt');
		// Out::jout($data);
		if($data['status']!=1){
			Out::jerror('苹果支付状态无效',null,'10219');
		}
		//判断支付ID是否已经验证过了
		//M('apple','im')->checkthirdid($transaction_id);
		M('pay','im')->checkthirdid($transaction_id);		
		if($transaction_id==$original_transaction_id){
			Out::jerror('购买已在客户端请求完成',null,'10230');
		}	
		$uid=M('pay','im')->serverbackvip(null, $original_transaction_id,$transaction_id,$product_id);			
		Out::jout(T('third_party_user')->set_field('vip_end_time,isvip')->get_one(['id'=>$uid]));
		//判断是否续期
		/*$uid=$this->get_userid();*/
		//找到老订单信息
		//  $old=T('order')->get_one(['trade_num'=>$original_transaction_id,'pay_status'=>1]);
		//  if(!$old){
		//  	Out::jerror('未找到旧订单',null,'10231');
		//  }
		// $old['book_name']=1;
		// $old['cartoon_name']=$original_transaction_id;
		// $old['trade_num']=$transaction_id;
		
		
		// $arr['trade_num'] = $transaction_id;
        // $arr['pay_status'] = 1;
        // $arr['pay_time'] = date('Y-m-d H:i:s');
        // $arr['local_time'] = date('Y-m-d H:i:s');
		// //  $product=T('coin_set')->get_one(['sid'=>$sid,'stype'=>1,'flag'=>0]);
		// //  if(!$product){
		// //  	Out::jerror('未找到商品或已下架',null,'10132');
		// //  }
		// $old['cash']=$product['prices'];
		// $old['sid']=$product['sid'];
		// $old['viplong']=$product['dhcoin'];
       // unset($old['oid']);
        //增加金币逻辑
        // M('order','am')->serverbackvip(T('viporder')->add($old));
        //输出用户金币
		//Out::jout(T('user')->set_field('vip,viptime')->get_one(['uid'=>$old['uid']]));
		
	}
}

