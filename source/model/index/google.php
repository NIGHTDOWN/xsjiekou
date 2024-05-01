<?php

namespace ng169\model\index;

use ng169\tool\Out;
use ng169\Y;

use function GuzzleHttp\json_decode;

/*google sdk url https://github.com/googleapis/google-api-php-client*/

im(API . 'googlesdk/autoload.php'); //引入sdk

checktop();

class google extends Y
{
    /*com.aykj.lovechat*/
    private $package_name = 'com.ng.story'; //包名
    private $oauth_file = "google.json"; //谷歌开发者后台下载的凭证
    // private $productpre = "com.ng.";
    //下载地址https://console.developers.google.com/apis/credentials?project=api-6521360320948974572-50110
    //支付效验
    /**
     * @param undefined $product_id 商品号
     * @param undefined $purchase_token 谷歌凭证
     *
     * @return
     */
    public function checkv3($product_id, $purchase_token, $sub = 0)
    {

        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . CONF . $this->oauth_file);

        $client = new \Google_Client();

        $client->useApplicationDefaultCredentials();
        $client->addScope(\Google_Service_AndroidPublisher::ANDROIDPUBLISHER);

        $androidPublishService = new \Google_Service_AndroidPublisher($client);

        //\Google_Client::$io->setOptions(array(CURLOPT_SSL_VERIFYPEER => FALSE));

        try {
            if ($sub == 1) {
                //订阅回调验证接口
                $result = $androidPublishService->purchases_subscriptions->get(
                    $this->package_name,
                    $product_id,
                    $purchase_token
                );
            } else {
                //内购接口
                $result = $androidPublishService->purchases_products->get(
                    $this->package_name,
                    $product_id,
                    $purchase_token
                );
            }


            if ($result) {
                $payload = $result['modelData'];
                $result = get_object_vars($result);
                if (!$result) {
                    Out::jerror('google抓取内容失败', null, '10141');
                }
               
                //判断是否消费
                //判断订单
                $return = array(
                    'status' => 1,
                    'receipt' => $result,
                    'thirdpayid' => $result['orderId'],
                    'acknowledgementState' => $result['acknowledgementState'],
                    'developerPayload' => $payload,
                );
                
                /*    kind    String    这种类型代表androidpublisher服务中的inappPurchase对象。
                purchaseTimeMillis    long    购买产品的时间，自纪元（1970年1月1日）以来的毫秒数。
                purchaseState    integer    订单的购买状态; 0:购买 1:取消 2:挂起(待支付)
                developerPayload    String    开发人员指定的字符串，包含有关订单的补充信息。
                orderId    String    与购买inapp产品相关联的订单ID。
                purchaseType    integer    购买inapp产品的类型。仅当未使用标准应用内结算流程进行此购买时，才会设置此字段。可能的值是：0. 测试（即从许可证测试帐户购买）1. 促销（即使用促销代码购买）2. 奖励（即观看视频广告而非付费）
                acknowledgementState    integer    inapp产品的确认状态。0:待确认 1:已确认
                consumptionState    integer    inapp消费状态。0:未消费 1:已消费*/
                $ourinfo = $return['developerPayload'];
                if (!$ourinfo) {
                    $return['status'] = 0;
                    Out::jerror('订单解析失败', $return, '10143');
                }
                if ($ourinfo) {
                    //如果存在developerPayload 就更新输出订单信息，否则不输出developerPayload信息
                    $json = $ourinfo;

                    if (!$json) {
                        $return['status'] = 0;
                        Out::jerror('订单json解析失败', $return, '10144');
                    }
                    $return['ourorderid'] = $json['obfuscatedExternalProfileId'];
                    $return['productid'] = $json['obfuscatedExternalAccountId'];
                }
                if ($sub) {
                    //订阅验证
                    /*    cancelReason    integer    订阅被取消或未自动更新的原因。可能的值为：
                    0用户取消了订阅
                    1订阅已被系统取消，例如由于帐单问题
                    2订阅已替换为新订阅
                    3订阅已被开发者取消
                    paymentState    integer    订阅的付款状态。可能的值为：
                    0付款等待中
                    1已收到付款
                    2免费试用
                    3待推迟的升级/降级
                     */
                    if ($result['paymentState'] != 1) {
                        $return['status'] = 0;
                        Out::jerror('支付状态未验证', $return, '10150');
                    }
                    if ($result['paymentState'] != 1) {
                        $return['status'] = 0;
                        Out::jerror('支付状态未验证', $return, '10150');
                    }
                    $return['paytime'] = substr($result['startTimeMillis'], 0, 10);
                    return $return;
                } else {
                    //内购验证
                    if ($result['consumptionState'] == 1) {

                        $return['paytime'] = substr($result['purchaseTimeMillis'], 0, 10);
                        /*d($return);*/
                        // if ($return['productid'] != $product_id) {
                        //     $return['status'] = 0;
                        //     Out::jerror('商品验证失败', $return, '10145');
                        // }
                        return $return;
                    } else {
                        $return['status'] = 0;
                        Out::jerror('订单未消费', $return, '10142');
                    }
                    return $return;
                }
            } else {
                return false;
                Out::jerror('google抓取内容失败', null, '10141');
            }
        } catch (\Exception $e) {
            //return false;
            Out::jerror('purtoekn验证错误' . $e->getMessage(), null, '10140');
            //d('error_msg = ' . $e->getMessage(),'debug');
            return false;
        }
        return false;
    }
    public function check($product_id, $purchase_token, $sub = 0)
    {

        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . CONF . $this->oauth_file);

        $client = new \Google_Client();

        $client->useApplicationDefaultCredentials();
        $client->addScope(\Google_Service_AndroidPublisher::ANDROIDPUBLISHER);

        $androidPublishService = new \Google_Service_AndroidPublisher($client);

        //\Google_Client::$io->setOptions(array(CURLOPT_SSL_VERIFYPEER => FALSE));

        try {
            if ($sub == 1) {
                //订阅回调验证接口
                $result = $androidPublishService->purchases_subscriptions->get(
                    $this->package_name,
                    $product_id,
                    $purchase_token
                );
            } else {
                //内购接口
                $result = $androidPublishService->purchases_products->get(
                    $this->package_name,
                    $product_id,
                    $purchase_token
                );
            }

            if ($result) {
                $result = get_object_vars($result);
                if (!$result) {
                    Out::jerror('google抓取内容失败', null, '10141');
                }

                //判断是否消费
                //判断订单
                $return = array(
                    'status' => 1,
                    'receipt' => $result,
                    'thirdpayid' => $result['orderId'],
                    'acknowledgementState' => $result['acknowledgementState'],
                    /*'developerPayload' => $result['developerPayload'],*/
                );
                /*    kind    String    这种类型代表androidpublisher服务中的inappPurchase对象。
                purchaseTimeMillis    long    购买产品的时间，自纪元（1970年1月1日）以来的毫秒数。
                purchaseState    integer    订单的购买状态; 0:购买 1:取消 2:挂起(待支付)
                developerPayload    String    开发人员指定的字符串，包含有关订单的补充信息。
                orderId    String    与购买inapp产品相关联的订单ID。
                purchaseType    integer    购买inapp产品的类型。仅当未使用标准应用内结算流程进行此购买时，才会设置此字段。可能的值是：0. 测试（即从许可证测试帐户购买）1. 促销（即使用促销代码购买）2. 奖励（即观看视频广告而非付费）
                acknowledgementState    integer    inapp产品的确认状态。0:待确认 1:已确认
                consumptionState    integer    inapp消费状态。0:未消费 1:已消费*/
                $ourinfo = $result['developerPayload'];
                // if (!$ourinfo) {
                //     $return['status'] = 0;
                //     Out::jerror('订单解析失败', $return, '10143');
                // }
                if ($ourinfo) {
                    //如果存在developerPayload 就更新输出订单信息，否则不输出developerPayload信息
                    $json = json_decode($ourinfo, 1);

                    if (!$json) {
                        $return['status'] = 0;
                        Out::jerror('订单json解析失败', $return, '10144');
                    }
                    $return['ourorderid'] = $json['ourorderid'];
                    $return['productid'] = $json['productid'];
                }
                if ($sub) {
                    //订阅验证
                    /*    cancelReason    integer    订阅被取消或未自动更新的原因。可能的值为：
                    0用户取消了订阅
                    1订阅已被系统取消，例如由于帐单问题
                    2订阅已替换为新订阅
                    3订阅已被开发者取消
                    paymentState    integer    订阅的付款状态。可能的值为：
                    0付款等待中
                    1已收到付款
                    2免费试用
                    3待推迟的升级/降级
                     */
                    if ($result['paymentState'] != 1) {
                        $return['status'] = 0;
                        Out::jerror('支付状态未验证', $return, '10150');
                    }
                    if ($result['paymentState'] != 1) {
                        $return['status'] = 0;
                        Out::jerror('支付状态未验证', $return, '10150');
                    }
                    $return['paytime'] = substr($result['startTimeMillis'], 0, 10);
                    return $return;
                } else {
                    //内购验证
                    if ($result['consumptionState'] == 1) {

                        $return['paytime'] = substr($result['purchaseTimeMillis'], 0, 10);
                        /*d($return);*/
                        // if ($return['productid'] != $product_id) {
                        //     $return['status'] = 0;
                        //     Out::jerror('商品验证失败', $return, '10145');
                        // }
                        return $return;
                    } else {
                        $return['status'] = 0;
                        Out::jerror('订单未消费', $return, '10142');
                    }
                    return $return;
                }
            } else {
                return false;
                Out::jerror('google抓取内容失败', null, '10141');
            }
        } catch (\Exception $e) {
            //return false;
            Out::jerror('purtoekn验证错误' . $e->getMessage(), null, '10140');
            //d('error_msg = ' . $e->getMessage(),'debug');
            return false;
        }
        return false;
    }
    public function subcheck($product_id, $purchase_token, $sub = 0)
    {

        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . CONF . $this->oauth_file);

        $client = new \Google_Client();

        $client->useApplicationDefaultCredentials();
        $client->addScope(\Google_Service_AndroidPublisher::ANDROIDPUBLISHER);

        $androidPublishService = new \Google_Service_AndroidPublisher($client);

        try {

            if ($sub == 1) {

                //订阅回调验证接口
                $result = $androidPublishService->purchases_subscriptions->get(
                    $this->package_name,
                    $product_id,
                    $purchase_token
                );
            } else {
                //内购接口

                $result = $androidPublishService->purchases_products->get(
                    $this->package_name,
                    $product_id,
                    $purchase_token
                );
            }

            if ($result) {
                $result = get_object_vars($result);
                if (!$result) {
                    Out::jerror('google抓取内容失败', null, '10141');
                }
                //判断是否消费
                //判断订单
                $return = array(
                    'status' => 1,
                    'receipt' => $result,
                    'thirdpayid' => $result['orderId'],
                    'acknowledgementState' => $result['acknowledgementState'],
                    /*'developerPayload' => $result['developerPayload'],*/
                );

                $ourinfo = $result['developerPayload'];
                if (!$ourinfo) {
                    $return['status'] = 0;
                    Out::jerror('订单解析失败', $return, '10143');
                }
                $json = json_decode($ourinfo, 1);

                if (!$json) {
                    $return['status'] = 0;
                    Out::jerror('订单json解析失败', $return, '10144');
                }
                $return['ourorderid'] = $json['ourorderid'];
                $return['productid'] = $json['productid'];

                if ($sub) {
                    if ($result['paymentState'] != 1) {
                        $return['status'] = 0;
                        //Out::jerror('支付状态未验证',$return,'10150');
                    }
                    if ($result['paymentState'] != 1) {
                        $return['status'] = 0;
                        //Out::jerror('支付状态未验证',$return,'10150');
                    }
                    $return['paytime'] = substr($result['startTimeMillis'], 0, 10);
                    return $return;
                } else {
                    //内购验证
                    if ($result['consumptionState'] == 1) {

                        $return['paytime'] = substr($result['purchaseTimeMillis'], 0, 10);

                        if ($return['productid'] != $product_id) {
                            $return['status'] = 0;
                            Out::jerror('商品验证失败', $return, '10145');
                        }
                        return $return;
                    } else {
                        $return['status'] = 0;
                        Out::jerror('订单未消费', $return, '10142');
                    }
                    return $return;
                }
            } else {
                return false;
                Out::jerror('google抓取内容失败', null, '10141');
            }
        } catch (\Exception $e) {
            return false;
            Out::jerror('purtoekn验证错误' . $e->getMessage(), null, '10140');
            //d('error_msg = ' . $e->getMessage(),'debug');
            return false;
        }
        return false;
    }
    public function getpackagename()
    {
        return $this->package_name;
    }
}
