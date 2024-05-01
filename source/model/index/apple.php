<?php


namespace ng169\model\index;
use ng169\Y;
use ng169\lib\Log;
use ng169\tool\Out;


checktop();

class apple extends Y{
	private $isdebug=true;
	private $pwd='8e4e838ef3644ca5a4dbea06c1cce499';//开通了订阅的苹果开发者账号必须带密码（苹果后台配置此密码），
	public function check($receipt,$transaction_id=null){ 
		//d($this->acurl($receipt),1);
		// 正式环境验证地址
		$ios_verify_url = 'https://buy.itunes.apple.com/verifyReceipt';
		// 测试环境验证地址
		$ios_sandbox_verify_url = 'https://sandbox.itunes.apple.com/verifyReceipt';
		$url=$this->isdebug?$ios_sandbox_verify_url:$ios_verify_url;
		
		$POSTFIELDS='{"receipt-data":"'.$receipt.'","password":"'.$this->pwd.'"}';
      
		$defaults = array(
			CURLOPT_POST => 1,
			CURLOPT_HEADER => 0,
			CURLOPT_URL =>$url,
			CURLOPT_FRESH_CONNECT => 1,
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_FORBID_REUSE => 1,
			CURLOPT_TIMEOUT => 60,
			CURLOPT_SSL_VERIFYPEER => FALSE,
			CURLOPT_POSTFIELDS => $POSTFIELDS // 苹果购买后返回的票据信息
		);
 
		/*curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $POSTFIELDS);*/
 
 
 
		$ch       = curl_init();
		curl_setopt_array($ch, $defaults);
		$result   = curl_exec($ch);
		$errno    = curl_errno($ch);
		$errmsg   = curl_error($ch);
		curl_close($ch);
 
		// 判断时候出错，抛出异常
		if($errno != 0){
		
			Out::jerror('apple支付失败'.$errmsg,null,'10115');
		}
		
		

		$object = json_decode($result);
		
		// 判断返回的数据是否是对象
		if(!is_object($object) || !isset($object->status)){
			//throw new Exception('Invalid response data');
			Out::jerror('Invalid response data'.$errmsg,null,'10116');
		} 
		if($object->status === 0){
			// 认证成功
			//$thisorder=$object->receipt->in_app[0];
			$return = array(
				'status'             => 1,
				'receipt'            => $object,
				'latest_receipt_info' => $object->receipt->latest_receipt_info,
				'expires_date_ms'    => $object->receipt_creation_date_ms/1000,
				'thirdpayid'    =>$transaction_id,
				'remote_server_code' => $object->status
			);
			//in_app效验
			
			
			//d($thisorder);
			/*if($thisorder){
				
			}else{
				$return['status']=0;
			}*/
			if(isset($object->latest_receipt) && !empty($object->latest_receipt)){
				// 如果是订阅购买，需要将票据保存到数据库中
				// 如果苹果返回了这个值，那么需要同步将这个值更新到数据库中
			}
 
			if(isset($object->latest_receipt_info) && !empty($object->latest_receipt_info)){
				// 获取过期时间
				$last_receipt_info = end($object->latest_receipt_info);
				$expires_date_ms   = $last_receipt_info->expires_date_ms / 1000;
			}
			return $return;
			// 其他操作
		} else{
			return array(
				'status'             => 0,
				'receipt'            => $object,
				'latest_receipt_info'     => null,
				'expires_date_ms'    => null,
				'thirdpayid'    => $transaction_id,
				'remote_server_code' => $object->status
			);
		}
	}

	
}

?>
