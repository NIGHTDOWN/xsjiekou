<?php

/**
 */

namespace ng169\tool;

require_once    "clibase.php";

class pdd extends \ng169\cli\Clibase
{
    public $dovo;
    public $port = 1199;
    public function __construct()
    {
        parent::__construct(); //初始化帮助信息
        $gt = $this->getargv(['do', 'port',]);
        $this->dovo = $gt['do'];
        $this->port = $gt['port'] ?? $this->port;
    }
    public function start()
    {

        if (!$this->dovo) {
            $this->_start();
        } else {
            switch ($this->dovo) {
                case 'start':
                    $this->_start();
                    break;
                case 'stop':

                    break;
                case 'reload':

                    break;
                case 'status':

                    break;
            }
        }
    }

    private function _start()
    {

        $this->init();
        $tk = 'x_eyJ0IjoiRFZFcGo4TXhMRzB6V3p4aGlrcjZVWG0vZDN0N1F5bmhaSjhpaWdUdklYdjNyaW5mWUdXclJ0dzBrQTg3aWMrTCIsInYiOjEsInMiOjEwMDYsInUiOjM1MDg2MDIyMzUwfQ==';
        $clientid = "aUzXyNcq";
        $etg = "XPY2CY3l";
        $anti_content = "";
        $mcmd_enc = "";
        $rctk_sign = "";
        $rctk = "";
        $url = "https://mdkd-api.pinduoduo.com/api/orion/op/cabinet/in/new";
        $pdata = array(
            "is_virtual" => "false",
            "customer_type" => "0",
            "waybill_code" => "JT5500393983850",
            "mobile_type" => "0",
            "temporary_mobile_status" => "false",
            "modify_wp" => "false",
            "modify_waybill_code" => "false",
            "type" => "1",
            "modify_customer_name" => "false",
            "receiver_type" => "0",
            "pickup_code" => "6-9-1",
            "courier_id" => "0",
            "mobile_last_four" => "",
            "name_source" => "100",
            "wp_code" => "JTSD",
            "wp_name" => "极兔速递",
            "mobile" => "13112234215",
            "is_manual_input" => "false",
            "shelf_number" => "6-9",
            "modify_pickup_code" => "false",
            "in_cabinet_type" => "1",
            "modify_mobile" => "true",
            "receiver_type_confirm" => "false",
            "confirm_flag" => "false",
            "customer_name" => "yang",
            "extend_type" => "1",
            "device" => "LM-G820"
        );
        $signs = $this->getenc($url, $clientid, json_encode($pdata));

        if ($signs) {
            $etg = $signs['etag'];
            $tk = $signs['sessionid'];
            $anti_content = $signs['anti-content'];
            $mcmd_enc = $signs['mcmd-enc'];
            $rctk_sign = $signs['rctk-sign'];
            $rctk = $signs['rctk'];
        } else {
            d("获取sign	失败");
            return false;
        }
        $pddhead = array(
            "p-appname" => "DDStore",
            "cookie" => "SUB_PASS_ID=$tk",
            "device-name" => "LM-G820",
            "ETag" => $etg,
            "AccessToken" => $tk,
            "anti-content" => $anti_content,
            "Referer" => "Android",
            "User-Agent" => "Dalvik/2.1.0 (Linux; U; Android 10; LM-G820 Build/QKQ1.191222.002)station_android_version/3.22.0 PackegeName/com.xunmeng.station AppVersion/3.22.0 DeviceType/Mobile AppName/DDStore pstation_android_version/3.22.0",
            "mcmd-enc" => $mcmd_enc,
            "site-code" => "A002683024",
            "PDD-CONFIG" => "V4:069.032200",
            "Content-Type" => "application/json;charset=utf-8",
            "Host" => "mdkd-api.pinduoduo.com",
            "Accept-Encoding" => "gzip",
            "rctk-sign" => $rctk_sign,
            "rctk" => $rctk,
            "vip" => "81.69.68.235"
        );
        $this->head($pddhead);
        $this->spiner->setproxy("192.168.10.11", "6666");
        $ret = $this->post($url, json_encode($pdata));
        d($ret);
    }
    public function getenc($signurl, $clientid, $senddata)
    {

        $url = "http://121.199.168.122:8090/edge/call";
        $pddhead = array("Content-Type" => "application/json;charset=utf-8",);
        $this->head($pddhead);
        $data = '{
        "group": "com.xunmeng.station",
        "action": "api_ddmc_anti_gen",
        "clientid": "' . $clientid . '",
        "latitude": "28.422705",
        "longitude": "118.53839",
        "header":{},
        "url":"' . $signurl . '",
        "params":' . $senddata . '
        }';
        $this->spiner->setproxy("192.168.10.11", "6666");
        $ret = $this->post($url, $data);

        if ($ret) {
            $ret = json_decode($ret, true);
            d($ret);
            $etg = $ret['data'];

            return $etg;
        } else {
            return false;
        }
    }




    //帮助doc参数stop ；start ；reload；restart ；status ；stop ；start ；reloa
    public function help()
    {
        d("接收参数do: start ；stop ；reload；status ；stop\n
      接收参数port: 端口\n
      ");
    }
}
//启动数据库连接池server

$ob = new pdd();
$ob->start();
