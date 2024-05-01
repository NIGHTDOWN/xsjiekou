<?php
checktop();
ini_set('date.timezone','Asia/Shanghai');

class payapi
{
  public  $alipay_config = array();
  public $apiid = 2;
  public function __construct($name,$key,$alipay_public_key)
  {

    /**
    * TODO: 修改这里配置为您自己申请的商户信息
    * 微信公众号信息配置
    *
    * APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
    *
    * MCHID：商户号（必须配置，开户邮件中可查看）
    *
    * KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
    * 设置地址：https://pay.weixin.qq.com/index.php/account/api_cert
    *
    * APPSECRET：公众帐号secert（仅JSAPI支付的时候需要配置， 登录公众平台，进入开发者中心可设置），
    * 获取地址：https://mp.weixin.qq.com/advanced/advanced?action=dev&t=advanced/dev&token=2005451881&lang=zh_CN
    */

    /*require_once dirname(__FILE__).'/lib/WxPay.Config.php';*/
    libxml_disable_entity_loader(true);//禁止xml外部引用;防止xxe攻击
    require_once dirname(__FILE__).'/lib/WxPay.Api.php';
    require_once dirname(__FILE__).'/lib/WxPay.NativePay.php';
    require_once dirname(__FILE__).'/lib/WxPay.Notify.php';
    require_once dirname(__FILE__).'/lib/log.php';
    $apiid = $this->apiid;
    $this->alipay_config['APPID'] = $name;


    /*d(WXAPPID);*/

    $this->alipay_config['MCHID'] = $alipay_public_key;

    $this->alipay_config['KEY'] = $key;
    $this->alipay_config['APPSECRET'] = '';
    //=======【证书路径设置】=====================================
    /**
    * TODO：设置商户证书路径
    * 证书路径,注意应该填写绝对路径（仅退款、撤销订单时需要，可登录商户平台下载，
    * API证书下载地址：https://pay.weixin.qq.com/index.php/account/api_cert，下载之前需要安装商户操作证书）
    * @var path
    */
    $this->alipay_config['SSLCERT_PATH'] = 'cert/apiclient_cert.pem';
    $this->alipay_config['SSLKEY_PATH'] = 'cert/apiclient_key.pem';
    //=======【curl代理设置】===================================
    /**
    * TODO：这里设置代理机器，只有需要代理的时候才设置，不需要代理，请设置为0.0.0.0和0
    * 本例程通过curl使用HTTP POST方法，此处可修改代理服务器，
    * 默认CURL_PROXY_HOST=0.0.0.0和CURL_PROXY_PORT=0，此时不开启代理（如有需要才设置）
    * @var unknown_type
    */
    $this->alipay_config['CURL_PROXY_HOST'] = "0.0.0.0";
    $this->alipay_config['CURL_PROXY_PORT'] = 0;

    //=======【上报信息配置】===================================
    /**
    * TODO：接口调用上报等级，默认紧错误上报（注意：上报超时间为【1s】，上报无论成败【永不抛出异常】，
    * 不会影响接口调用流程），开启上报之后，方便微信监控请求调用的质量，建议至少
    * 开启错误上报。
    * 上报等级，0.关闭上报; 1.仅错误出错上报; 2.全量上报
    * @var int
    */
    $this->alipay_config['REPORT_LEVENL'] = 1;
    $this->alipay_config['NOTIFY_URL'] = 'http://'.$_SERVER['SERVER_NAME']."/callback/asynpayback.{$apiid}.php";
    $this->alipay_config['RETURN_URL'] = 'http://'.$_SERVER['SERVER_NAME']."/callback/syncpayback.{$apiid}.php";
    if (!defined(WXAPPID)) {
      define(WXAPPID,$name);
    }
    if (!defined(WXMCHID)) {
      define(WXMCHID,$alipay_public_key);

    }
    if (!defined(WXKEY)) {
      define(WXKEY,$this->alipay_config['KEY']);
    }
    if (!defined(WXREPORT_LEVENL)) {
      define(WXREPORT_LEVENL,$this->alipay_config['REPORT_LEVENL']);
    }
    if (!defined(WXNOTIFY_URL)) {
      define(WXNOTIFY_URL,$this->alipay_config['NOTIFY_URL']);
    }
    if (!defined(WXRETURN_URL)) {
      define(WXRETURN_URL,$this->alipay_config['RETURN_URL']);
    }
    if (!defined(WXCURL_PROXY_HOST)) {
      define(WXCURL_PROXY_HOST,$this->alipay_config['CURL_PROXY_HOST']);
    }
    if (!defined(WXCURL_PROXY_PORT)) {
      define(WXCURL_PROXY_PORT,$this->alipay_config['CURL_PROXY_PORT']);
    }
    if (!defined(WXSSLCERT_PATH)) {
      define(WXSSLCERT_PATH,$this->alipay_config['SSLCERT_PATH']);
    }
    if (!defined(WXSSLKEY_PATH)) {
      define(WXSSLKEY_PATH,$this->alipay_config['SSLKEY_PATH']);
    }



  }
  public function pay($trade,$title,$cash,$desc = '')
  {

    if (!$trade)error('支付订单不能空');
    if (!$title)error('支付标题不能空');
    if (!$cash)error('支付金额不能空');
    $notify = new NativePay();

    $input  = new WxPayUnifiedOrder();

    $input->SetBody($title);
    $input->SetAttach($title);
    /*$input->SetOut_trade_no($this->alipay_config['MCHID'].date("YmdHis"));*/
    $input->SetOut_trade_no($trade);
    /*$input->SetTotal_fee($cash*100);*/
    $input->SetTotal_fee($cash);
    $input->SetTime_start(date("YmdHis"));
    $input->SetTime_expire(date("YmdHis", time() + 600));
    $input->SetGoods_tag($desc);
    $input->SetNotify_url($this->alipay_config['NOTIFY_URL']);
    $input->SetTrade_type("NATIVE");
    $input->SetProduct_id($trade);
    /*d($trade);*/

    $result = $notify->GetPayUrl($input);
    if ($result['return_code'] != 'SUCCESS') {
      error($result['return_msg']);
    }


    $url2 = $result["code_url"];

    //输出表单
    return $url2;
    /*Y::loadTool('image');
    var_dump(YImage::qr(($url2)));
    die();*/
  }
  public function asyn()
  {

    /*$logHandler= new CLogFileHandler(dirname(__FILE__)."/logs/".date('Y-m-d').'.log');
    $log = Log::Init($logHandler, 15);



    Log::DEBUG("begin notify");*/

    $notify = new PayNotifyCallBack();
    $data   = $notify->Handle(false);

    /*    $arr=$_POST;
    $alipaySevice = new AlipayTradeService($this->alipay_config);
    $alipaySevice->writeLog(var_export($_POST,true));
    $result = $alipaySevice->check($arr);
    if($result)
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

    }*/

  }
  public function sync()
  {
    //无同步处理业务
    return false;

    $arr          = $_GET;
    $alipaySevice = new AlipayTradeService($this->alipay_config);
    $result       = $alipaySevice->check($arr);
    if ($result) {
      //验证成功
      /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
      //请在这里加上商户的业务逻辑程序代码

      //——请根据您的业务逻辑来编写程序（以下代码仅作参考）——
      //获取支付宝的通知返回参数，可参考技术文档中页面跳转同步通知参数列表

      //商户订单号
      $out_trade_no = htmlspecialchars($_GET['out_trade_no']);

      //支付宝交易号
      $trade_no     = htmlspecialchars($_GET['trade_no']);

      /*echo "验证成功<br />支付宝交易号：".$trade_no;*/
      $data['soleid'] = $out_trade_no;
      $data['thirdid'] = $trade_no;
      $data['thirduser'] = $_GET['buyer_email'];
      $data['paytime'] = strtotime($_GET['notify_time']);

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
