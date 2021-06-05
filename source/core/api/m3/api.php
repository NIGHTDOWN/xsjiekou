<?php
checktop();
class payapi{
	private $alipay_config=array();
	private $apiid=3;
	
	public function __construct($name,$key,$alipay_public_key){
		
		libxml_disable_entity_loader(true);//禁止xml外部引用;防止xxe攻击
		$apiid=$this->apiid;
		
		$this->alipay_config['app_id']		= $name;//应用ID,您的APPID。
		$this->alipay_config['merchant_private_key']		= $key;	//商户私钥
		$this->alipay_config['alipay_public_key']		= $alipay_public_key;//支付宝公钥,查看地址：https://openhome.alipay.com/platform/keyManage.htm 对应APPID下的支付宝公钥。
		/*$this->alipay_config['notify_url']		= '';//异步通知地址
		$this->alipay_config['return_url']		= '';//同步跳转*/
		$this->alipay_config['notify_url'] ='http://'.$_SERVER['SERVER_NAME']."/callback/asynpayback.{$apiid}.php";
		$this->alipay_config['return_url'] = 'http://'.$_SERVER['SERVER_NAME']."/callback/syncpayback.{$apiid}.php";
		$this->alipay_config['charset']		= 'UTF-8';//编码格式
		$this->alipay_config['sign_type']		= 'RSA2';//签名方式
		$this->alipay_config['gatewayUrl']		= 'https://openapi.alipay.com/gateway.do';//支付宝网关
		require_once dirname(__FILE__).'/pagepay/service/AlipayTradeService.php';
		require_once dirname(__FILE__).'/pagepay/buildermodel/AlipayTradePagePayContentBuilder.php';
		
	}
	public function pay($trade,$title,$cash,$desc=''){
		
		if(!$trade)error('支付订单不能空');
		if(!$title)error('支付标题不能空');
		if(!$cash)error('支付金额不能空');
	
    $out_trade_no = trim($trade);
    $subject = trim($title);
    $total_amount = trim($cash)/100;
    $body = trim($desc);
	$payRequestBuilder = new AlipayTradePagePayContentBuilder();
	$payRequestBuilder->setBody($body);
	$payRequestBuilder->setSubject($subject);
	$payRequestBuilder->setTotalAmount($total_amount);
	$payRequestBuilder->setOutTradeNo($out_trade_no);
	$payRequestBuilder->setQr(5);
	$aop = new AlipayTradeService($this->alipay_config);
	
	$response = $aop->wapPay($payRequestBuilder,$this->alipay_config['return_url'],$this->alipay_config['notify_url']);
	//输出表单
	/*YLog::txt($response);*/
	var_dump($response);
    die();
	}
	public function asyn(){
		$arr=$_POST;
$alipaySevice = new AlipayTradeService($this->alipay_config);
$alipaySevice->writeLog(var_export($_POST,true));
$result = $alipaySevice->check($arr);


if($result)  //退耀怀倀
{
 
  $out_trade_no = $_POST['out_trade_no'];

  $trade_no = $_POST['trade_no'];

  $trade_status = $_POST['trade_status'];


  if($_POST['trade_status'] == 'TRADE_FINISHED')
  {

   
  }
  else if ($_POST['trade_status'] == 'TRADE_SUCCESS')
  {
    
  }
		$data['soleid']=$out_trade_no;
		$data['thirdid']=$trade_no;
		$data['thirduser']=$_POST['buyer_email'];
		$data['paytime']=strtotime($_POST['notify_time']);
		
return $data;
}
else
{
  return false;

}
	
	}
	public function sync(){
		$arr=$_GET;
$alipaySevice = new AlipayTradeService($this->alipay_config); 
$result = $alipaySevice->check($arr);
if($result) {//验证成功
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
	//请在这里加上商户的业务逻辑程序代码
	
	//——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
    //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

	//商户订单号
	$out_trade_no = htmlspecialchars($_GET['out_trade_no']);

	//支付宝交易号
	$trade_no = htmlspecialchars($_GET['trade_no']);
		
	/*echo "验证成功<br />支付宝交易号：".$trade_no;*/
	$data['soleid']=$out_trade_no;
		$data['thirdid']=$trade_no;
		$data['thirduser']=$_GET['buyer_email'];
		$data['paytime']=strtotime($_GET['notify_time']);
		
return $data;
	//——请根据您的业务逻辑来编写程序（以上代码仅作参考）——
	
	/////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
}
else {
    //验证失败
   return false;
}
		/*require_once("lib/alipay_notify.class.php");
		$alipayNotify = new AlipayNotify($$this->alipay_config);
		$verify_result = $alipayNotify->verifyReturn();
		if($verify_result){//验证成功	
		$data['soleid']=$_GET['out_trade_no'];
		$data['thirdid']=$_GET['trade_no'];
		$data['thirduser']=$_GET['buyer_email'];
		$data['paytime']=strtotime($_GET['notify_time']);
		return true;
		}
		else{
			return false;
		}*/
	}
}

?>
