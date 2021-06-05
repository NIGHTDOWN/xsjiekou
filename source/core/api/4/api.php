<?php 
require_once  '/sdk/acp_service.php';
class payapi{
	private $apiid=4;
	private $config=null;
	function __construct($name,$key){
		/*$name='777290058110048';//测试使用*/
		//SDK_SIGN_CERT_PWD 证书解密私钥
		libxml_disable_entity_loader(true);//禁止xml外部引用;防止xxe攻击
		if($key){
			/*const SDK_SIGN_CERT_PWD = $key;*/
			define('SDK_SIGN_CERT_PWD',$key);
		}else{
			define('SDK_SIGN_CERT_PWD','000000');
			
		}
	
		$apiid=$this->apiid;
		$frontUrl='http://'.$_SERVER['SERVER_NAME']."/callback/syncpayback.{$apiid}.php";
		$backurl='http://'.$_SERVER['SERVER_NAME']."/callback/asynpayback.{$apiid}.php";

		$this->config = array(
			//以下信息非特殊情况不需要改动
			'version' => '5.0.0',                 //版本号
			'encoding' => 'utf-8',				  //编码方式
			'txnType' => '01',				      //交易类型
			'txnSubType' => '01',				  //交易子类
			'bizType' => '000201',				  //业务类型
			'frontUrl' =>  $frontUrl,  //前台通知地址
			'backUrl' => $backurl,	  //后台通知地址
			'signMethod' => '01',	              //签名方法
			'channelType' => '08',	              //渠道类型，07-PC，08-手机
			'accessType' => '0',		          //接入类型
			'currencyCode' => '156',	          //交易币种，境内商户固定156
			//TODO 以下信息需要填写
			'merId' => $name,		//商户代码，请改自己的测试商户号，
		);
	}
	public function pay($soleid,$title,$pay){
		$this->config['orderId']=$soleid;
		$this->config['txnTime']=date('YmdHis');
	/*	$this->config['txnAmt']=$pay*100;//放大100倍，精确分*/
		$this->config['txnAmt']=$pay;//放大100倍，精确分
		$this->config['reqReserved']=$title;
		AcpService::sign ($this->config);
		$uri = SDK_FRONT_TRANS_URL;
		$html_form = AcpService::createAutoFormHtml( $this->config, $uri );
		echo $html_form;
		die();
	}
	public function asyn(){
		if(isset( $_POST ['signature'] )){
			$flag=AcpService::validate($_POST);
			$respCode = $_POST ['respCode'];
			if($flag && ($respCode=='00' ||$respCode=='A6')){
				$orderId = $_POST ['orderId']; //其他字段也可用类似方式获取
				//判断respCode=00或A6即可认为交易成功
				$accNo = AcpService::decryptData($_POST["accNo"]);//卡号
				$data['soleid']=$_POST ['orderId'];
				$data['thirduser']=$accNo;
				$data['paytime']=time();
				return $data;
			}else{
				return false;
			}
		} else{
			echo '签名为空';
			return false;
		}

	}
	public function sync(){
		
		if(isset( $_POST ['signature'] )){
			$flag=AcpService::validate($_POST);
			$respCode = $_POST ['respCode'];
			if($flag && ($respCode=='00' ||$respCode=='A6')){
				$orderId = $_POST ['orderId']; //其他字段也可用类似方式获取
				//判断respCode=00或A6即可认为交易成功
				$accNo = AcpService::decryptData($_POST["accNo"]);//卡号
				$data['soleid']=$_POST ['orderId'];
				$data['thirduser']=$accNo;
				$data['paytime']=time();
				return $data;
			}else{
				return false;
			}
		} else{
			echo '签名为空';
			return false;
		}
	}
	
}


















?>