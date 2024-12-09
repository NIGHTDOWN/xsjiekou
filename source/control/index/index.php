<?php


namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\tool\Out;

checktop();



class index extends indexbase
{

    protected $noNeedLogin = ['*'];


    public function control_run()
    {

        //是否https

        if ($_SERVER['REQUEST_SCHEME'] == 'http') {
            //判断是否主域名
            $main = 'www.love-novel.com';
            if ($_SERVER['SERVER_NAME'] == 'love-novel.com' || $_SERVER['SERVER_NAME'] == $main) {
                gourl('https://' . $main);
            }
        }
        $endpoint = [[1, 4], [2, 3]];
        $newbook = M('index', 'im')->booknew(1, $this->langid, 6);

        $endbook = M('index', 'im')->end(1, $this->langid, 4);
        $newcartoon = M('index', 'im')->booknew(2, $this->langid, 6);
        $endcartoon = M('index', 'im')->end(2, $this->langid, 4);
        $hotcartoon = M('index', 'im')->hot(2, $this->langid, 6);
        foreach ($newcartoon as $key => $value) {
            # code...
            $newcartoon[$key]['tags'] =  M('cate', 'im')->getlable($value['lable'], $value['lang']);
        }

        foreach ($hotcartoon as $key => $value) {
            # code...
            $hotcartoon[$key]['tags'] =  M('cate', 'im')->getlable($value['lable'], $value['lang']);
        }
        $hotbook = M('index', 'im')->hot(1, $this->langid, 6);

        foreach ($endbook as $k => $v) {
            if (in_array($k + 1, $endpoint[0])) {
                $endbook[$k]['big'] = 1;
            } else {
                $endbook[$k]['big'] = 0;
            }
        }
        foreach ($endcartoon as $k => $v) {

            if (in_array($k + 1, $endpoint[1])) {
                $endcartoon[$k]['big'] = 1;
            } else {
                $endcartoon[$k]['big'] = 0;
            }
        }

        $this->view(null, ['newbook' => $newbook, 'newcartoon' => $newcartoon, 'endbook' => $endbook, 'endcartoon' => $endcartoon, 'hotbook' => $hotbook, 'hotcartoon' => $hotcartoon]);
    }
    public function control_red()
    {
        // $cache =  Rediscache::getRedis();
        // $cache->set('bbb', '111');
        // d($cache->get('bbb'));
        // \htmlspecialchars_encode('sss');

        // $s = htmlspecialchars('305e312b3029060355040b0c224372656174656420627920687474703a2f2f7777772e666964646c6572322e636f6d31153013060355040a0c0c444f5f4e4f545f54525553543118301606035504030c0f2a2e61707073666c7965722e636f6d');
        // $s = md5('305e312b3029060355040b0c224372656174656420627920687474703a2f2f7777772e666964646c6572322e636f6d31153013060355040a0c0c444f5f4e4f545f54525553543118301606035504030c0f2a2e61707073666c7965722e636f6d');
        // $string = '1d2a09653432653a3531353838262a0f686b3d0a10355c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5c5cde1b3ca271e7adea037f899abe900d6c8f01f0ec';

        // d(md5($string));
        // d(2222);

    }
    public function control_pdd()
    {
        //customer_name   客户名称
        //waybill_code   运单号
        //pickup_code 取货点
        //wp_code 网点
        //wp_name 网点名称
        //mobile 手机号
        //shelf_number 货架号
        $get = get(['string' => ['customer_name'=>1,  'waybill_code'=>1,  'pickup_code'=>1,"wp_code"=>1,"wp_name"=>1,"mobile"=>1,"shelf_number"=>1 ]]);
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
            "waybill_code" => $get['waybill_code'],
            "mobile_type" => "0",
            "temporary_mobile_status" => "false",
            "modify_wp" => "false",
            "modify_waybill_code" => "false",
            "type" => "1",
            "modify_customer_name" => "false",
            "receiver_type" => "0",
            "pickup_code" => $get['pickup_code'],
            "courier_id" => "0",
            "mobile_last_four" => "",
            "name_source" => "100",
            "wp_code" => $get['wp_code'],
            "wp_name" => $get['wp_name'],
            "mobile" => $get['mobile'],
            "is_manual_input" => "false",
            "shelf_number" =>  $get['shelf_number'],
            "modify_pickup_code" => "false",
            "in_cabinet_type" => "1",
            "modify_mobile" => "true",
            "receiver_type_confirm" => "false",
            "confirm_flag" => "false",
            "customer_name" => $get['customer_name'],
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
            Out::jerror("获取sign失败");
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
        $ycurl=new \ng169\tool\Curl();
        $ycurl->head($pddhead);
        $ycurl->setproxy("192.168.10.11", "6666");
        $ret =$ycurl->post($url, json_encode($pdata));
        d($ret);
        Out::jerror($ret);
    }
    public function getenc($signurl, $clientid, $senddata)
    {
        $ycurl=new \ng169\tool\Curl();
        $url = "http://121.199.168.122:8090/edge/call";
        $pddhead = array("Content-Type" => "application/json;charset=utf-8",);
        $ycurl->head($pddhead);
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
        $ycurl->setproxy("192.168.10.11", "6666");
        $ret = $ycurl->post($url, $data);

        if ($ret) {
            $ret = json_decode($ret, true);
            // d($ret);
            $etg = $ret['data'];

            return $etg;
        } else {
            return false;
        }
    }
}
