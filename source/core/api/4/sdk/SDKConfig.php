<?php

define('CERTS_PATH',dirname(dirname(__FILE__)));


//测试配置
////////////////////////////////////////////////////////////

const SDK_SIGN_CERT_PATH = CERTS_PATH.'/certs/acp_test_sign.pfx';
const SDK_ENCRYPT_CERT_PATH = CERTS_PATH.'/certs/acp_test_enc.cer';
const SDK_FRONT_TRANS_URL = 'https://101.231.204.80:5000/gateway/api/frontTransReq.do';
const SDK_BACK_TRANS_URL = 'https://101.231.204.80:5000/gateway/api/backTransReq.do';
const SDK_BATCH_TRANS_URL = 'https://101.231.204.80:5000/gateway/api/batchTrans.do';
const SDK_SINGLE_QUERY_URL = 'https://101.231.204.80:5000/gateway/api/queryTrans.do';
const SDK_FILE_QUERY_URL = 'https://101.231.204.80:9080/';
const SDK_Card_Request_Url = 'https://101.231.204.80:5000/gateway/api/cardTransReq.do';
const SDK_App_Request_Url = 'https://101.231.204.80:5000/gateway/api/appTransReq.do';
const SDK_LOG_LEVEL = PhpLog::DEBUG;
//线上配置
////////////////////////////////////////////////////////////
/*
const SDK_SIGN_CERT_PWD = '000000';
const SDK_SIGN_CERT_PATH = CERTS_PATH.'/certs/acp_prod_sign.pfx';
const SDK_ENCRYPT_CERT_PATH = CERTS_PATH.'/certs/acp_prod_enc.cer';
const SDK_FRONT_TRANS_URL = 'https://gateway.95516.com/gateway/api/frontTransReq.do';
const SDK_BACK_TRANS_URL = 'https://gateway.95516.com/gateway/api/backTransReq.do';
const SDK_BATCH_TRANS_URL = 'https://gateway.95516.com/gateway/api/batchTrans.do';
const SDK_SINGLE_QUERY_URL = 'https://gateway.95516.com/gateway/api/queryTrans.do';
const SDK_FILE_QUERY_URL = 'https://filedownload.95516.com/';
const SDK_Card_Request_Url = 'https://gateway.95516.com/gateway/api/cardTransReq.do';
const SDK_App_Request_Url = 'https://gateway.95516.com/gateway/api/appTransReq.do';
const SDK_LOG_LEVEL = PhpLog::OFF;*/
////////////////////////////////////////////////////////////




const SDK_VERIFY_CERT_DIR = CERTS_PATH.'/certs/';
const SDK_FILE_DOWN_PATH = CERTS_PATH.'/file/';
const SDK_LOG_FILE_PATH = CERTS_PATH.'/logs/';
/** 以下缴费产品使用，其余产品用不到，无视即可 */
// 前台请求地址
const JF_SDK_FRONT_TRANS_URL = 'https://gateway.95516.com/jiaofei/api/frontTransReq.do';
// 后台请求地址
const JF_SDK_BACK_TRANS_URL = 'https://gateway.95516.com/jiaofei/api/backTransReq.do';
// 单笔查询请求地址
const JF_SDK_SINGLE_QUERY_URL = 'https://gateway.95516.com/jiaofei/api/queryTrans.do';
// 有卡交易地址
const JF_SDK_CARD_TRANS_URL = 'https://gateway.95516.com/jiaofei/api/cardTransReq.do';
// App交易地址
const JF_SDK_APP_TRANS_URL = 'https://gateway.95516.com/jiaofei/api/appTransReq.do';



?>