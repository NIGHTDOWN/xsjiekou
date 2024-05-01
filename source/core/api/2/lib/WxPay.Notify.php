<?php
/**
 * 
 * 回调基础类
 * @author widyhu
 *
 */
class WxPayNotify extends WxPayNotifyReply
{
	private $data;
	/**
	 * 
	 * 回调入口
	 * @param bool $needSign  是否需要签名输出
	 */
	final public function Handle($needSign = true)
	{
		
		//当返回false的时候，表示notify中调用NotifyCallBack回调失败获取签名校验失败，此时直接回复失败
		$result = WxpayApi::notify(array($this, 'NotifyCallBack'), $msg);
		
		if($result == false){
			$this->SetReturn_code("FAIL");
			$this->SetReturn_msg($msg);
			$this->ReplyNotify(false);
			return false;
		} else {
			//该分支在成功回调到NotifyCallBack方法，处理完成之后流程
			
			$this->SetReturn_code("SUCCESS");
			$this->SetReturn_msg("OK");
		}
		
		$this->ReplyNotify($needSign);
		
	}
	
	/**
	 * 
	 * 回调方法入口，子类可重写该方法
	 * 注意：
	 * 1、微信回调超时时间为2s，建议用户使用异步处理流程，确认成功之后立刻回复微信服务器
	 * 2、微信服务器在调用失败或者接到回包为非确认包的时候，会发起重试，需确保你的回调是可以重入
	 * @param array $data 回调解释出的参数
	 * @param string $msg 如果回调处理失败，可以将错误信息输出到该方法
	 * @return true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
	 */
	public function NotifyProcess($data, &$msg)
	{
		//TODO 用户基础该类之后需要重写该方法，成功的时候返回true，失败返回false
		
		return true;
	}
	
	/**
	 * 
	 * notify回调方法，该方法中需要赋值需要输出的参数,不可重写
	 * @param array $data
	 * @return true回调出来完成不需要继续回调，false回调处理未完成需要继续回调
	 */
	final public function NotifyCallBack($data)
	{
		
		$msg = "OK";
		$result = $this->NotifyProcess($data, $msg);
		/*$this->data=$data;*/
		
		if($result == true){
			$this->SetReturn_code("SUCCESS");
			$this->SetReturn_msg("OK");
			/*return $data;*/
		} else {
			$this->SetReturn_code("FAIL");
			$this->SetReturn_msg($msg);
		}
		
		return $result;
	}
	
	/**
	 * 
	 * 回复通知
	 * @param bool $needSign 是否需要签名输出
	 */
	final private function ReplyNotify($needSign = true)
	{
		//如果需要签名
		if($needSign == true && 
			$this->GetReturn_code($return_code) == "SUCCESS")
		{
			$this->SetSign();
		}
		WxpayApi::replyNotify($this->ToXml());
	}
}
class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		/*d($result);*/
		/*
		Log::DEBUG("query:" . json_encode($result));*/
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		
		/*Log::DEBUG("call back:" . json_encode($data));*/
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
		//业务逻辑
		$bool['soleid']=$data['out_trade_no'];
		$bool['thirdid']=$data['transaction_id'];
		$bool['thirduser']=$data['openid'];
		$bool['paytime']=time();
	
		
		if(!$bool){
	error('支付失败',$orderurl);
}
else{	
	im('affair.php');

	$where=array('soleid'=>$bool['soleid']);
	$info=T('pay')->get_one($where);
	
	if(!$info)error('支付记录不存在',$orderurl);
	if($info['paystatus']==1)out('支付成功',$orderurl,null,1);
	if($info['paystatus']==2)error('交易已经关闭，请勿支付',$orderurl);
		
	if($info['paystatus']==0){
		switch($info['for']){
			case 0:
			$url=product_back($bool);
			if($url){im(LIB.'/class.socket.php');
			$payinfo=T('pay')->get_one(array('soleid'=>$bool['soleid']));
		if(!$payinfo)return false;
		if(!$payinfo['paystatus'])return false;
		
		
		if($payinfo['payduan']!='web'){
			socketClass::phpsend(getip(),DB_SOCKPOST,array('action'=>'wxpay2','orderid'    =>$bool['soleid']));
		}else{
			
			socketClass::phps('wxbuy',array('orderid'    =>$bool['soleid']));
		}
		/*out('支付成功',$url);*/	
		return true;
		}
	/*error('支付失败');*/
			return false;
				break;
			case 1:
			$url=recharge_back($bool);
			if($url){im(LIB.'/class.socket.php');
		//根据类型通知对应的客户端
		//获取envent
		$payinfo=T('pay')->get_one(array('soleid'=>$bool['soleid']));
		if(!$payinfo)return false;
		if(!$payinfo['paystatus'])return false;
		if($payinfo['payduan']!='web'){
			socketClass::phpsend(getip(),DB_SOCKPOST,array('action'=>'wxpay2','orderid'    =>$bool['soleid']));
		}else{
			socketClass::phps('wxcz',array('orderid'    =>$bool['soleid']));
		}
		
		/*out('支付成功',$url);	*/
		return true;
		}
	/*error('支付失败');*/
		return false;	
				break;
			
		}
	
		
	}
}
		
		
		
		return true;
	}
}