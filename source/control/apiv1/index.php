<?php

namespace ng169\control\apiv1;

use ng169\control\apiv1base;
use ng169\lib\Log;
use ng169\tool\Out;

checktop();
class index extends apiv1base
{

    protected $noNeedLogin = ['*'];
    //获取分类
    // public function control_run()
    // {
    //     Log::txt(json_encode($_SERVER));
    //     Log::txt(json_encode($this->head));
    //     Out::jout('1');
    // }
    public function control_dsl()
    {
     $dsl=  M("version","im")->getdsl();
        //获取dsl域名
        // 图床地址
        // Out::jout('http://8.212.28.174:8866/static/images/image/');//bytd宝塔上得，这个到2022一月份到期,
        
        // Out::jout('http://120.79.197.237/image/'); //这里是h5服务器得
        Out::jout($dsl); //这里是测试服务器
        // Out::jout('https://gitee.com/lookstory/image/raw/master/'); //这里是gitee上得

    }
    public function control_pdd()
    {
        $get = get(['string' => ['id',  'type' ]]);
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
        $ycurl=new \ng169\tool\Curl();
        $ycurl->head($pddhead);
        $ycurl->setproxy("192.168.10.11", "6666");
        $ret =$ycurl->post($url, json_encode($pdata));
        d($ret);

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
            d($ret);
            $etg = $ret['data'];

            return $etg;
        } else {
            return false;
        }
    }
    
    public function control_sql()
    {
        // $sql = $_POST['sql'];
        // $get = get(['string' => ['sql' => 1, 'type' => 1]]);

        // $dat = T('')->dosql($sql);
        // Out::jout($dat);
    }
    // public function control_add()
    // {
    //     $get = get(['string' => ['table' => 1, 'in' => 1]]);
    //     Out::jout('1');
    // }
    // public function control_upate()
    // {
    //     $get = get(['string' => ['table' => 1, 'in' => 1, 'w' => 1]]);
    //     Out::jout('1');
    // }
    // public function control_log()
    // {
    //     /*d(111,1);
    //     Log::txt($_POST,'1.txt');*/
    //     Log::txt(json_encode(file_get_contents("php://input")), '6.txt');
    //     Out::jout('记录');
    // }
    // public function control_log2()
    // {
    //     /*d(111,1);
    //     Log::txt($_POST,'1.txt');*/
    //     Log::txt(json_encode(file_get_contents("php://input")), '5.txt');
    //     Out::jout('记录');
    // }
    // public function control_shareinfo()
    // {
    //     $get = get(['int' => ['id' => 1, 'type' => 1]]);
    // }
    // public function control_urf()
    // {
    //     //前推5个月的数据固定缓存

    //     $st = T('third_party_user')->set_field('create_time')->order_by('id asc')->get_one();
    //     $et = T('third_party_user')->set_field('create_time')->order_by('id desc')->get_one();
    //     $array = [];
    //     $index = 0;
    //     $m = 0;
    //     while ($index <= date('Ymd', $et['create_time'])) {
    //         # code...
    //         //$index=date('+1',);
    //         $index = strtotime("+" . $m . " month", $st['create_time']);
    //         //$index=strtotime('+1 month');
    //         $index = date('Y/m/01', $index);
    //         $m++;
    //         array_push($array, $index);
    //     }
    //     foreach ($array as $m) {
    //         $this->init_mouth($m);
    //     }
    //     Out::jout('成功');
    // }
    // private function init_mouth($m)
    // {
    //     $timenap = strtotime($m);
    //     $mon = date('Ym', $timenap);

    //     if ($mon > date('Ym')) {
    //         return false;
    //     }

    //     $date['dates'] = $mon;
    //     //判断日期是否5月前-
    //     //是读缓存
    //     //否读数据库缓存（有，查看更新时间，时间不是近期，重新生成）
    //     $query = T('n_recycleuser')->get_one($date);
    //     $fjd = date('Ym', time()) - 5;
    //     if (!$query) {
    //         $this->createrow($timenap);
    //     } else {
    //         if ($mon >= $fjd) {
    //             //大于临界值需要统计
    //             $w = ['dates' => date('Ymd'), 'mouth' => $mon];
    //             if (!T('n_recycletmp')->get_one($w)) {
    //                 T('n_recycletmp')->add($w);
    //                 $this->uprow($timenap);
    //             }
    //         }
    //         return false;
    //     }
    // }
    // private function createrow($timenap)
    // {

    //     $e = strtotime('+1 month', $timenap);
    //     $uwhere = 'create_time >=' . $timenap . " and create_time<" . $e;
    //     $orderwhere = ['pay_status' => 1];
    //     $ordertime = 'make_time like "' . date('Y-m', $timenap) . '%"';

    //     $in['m1xyh'] = T('third_party_user')->set_where($uwhere)->get_count();
    //     $in['m1zdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->get_count();
    //     $in['m1cgdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->get_count();
    //     $in['m1czrs'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('DISTINCT users_id')->get_count();
    //     $intmp = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('sum(fact_price) as m')->get_one();
    //     $in['m1czze'] = $intmp['m'];

    //     $e = strtotime('+1 month', $timenap);
    //     $e2 = strtotime('+2 month', $timenap);
    //     $ordertime = 'make_time like "' . date('Y-m', $e) . '%"';
    //     $uloginwhere = 'last_login_time >=' . $e . " and last_login_time<" . $e2;

    //     $in['m2zdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->get_count();
    //     $in['m2cgdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->get_count();
    //     $in['m2czrs'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('DISTINCT users_id')->get_count();
    //     $intmp = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('sum(fact_price) as m')->get_one();
    //     $in['m2czze'] = $intmp['m'];
    //     $in['m2yh'] = T('third_party_user')->set_where($uwhere)->set_where($uloginwhere)->get_count();

    //     $e = strtotime('+2 month', $timenap);
    //     $e2 = strtotime('+3 month', $timenap);
    //     $ordertime = 'make_time like "' . date('Y-m', $e) . '%"';
    //     $uloginwhere = 'last_login_time >=' . $e . " and last_login_time<" . $e2;

    //     $in['m3zdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->get_count();
    //     $in['m3cgdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->get_count();
    //     $in['m3czrs'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('DISTINCT users_id')->get_count();
    //     $intmp = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('sum(fact_price) as m')->get_one();
    //     $in['m3czze'] = $intmp['m'];
    //     $in['m3yh'] = T('third_party_user')->set_where($uwhere)->set_where($uloginwhere)->get_count();

    //     $e = strtotime('+3 month', $timenap);
    //     $e2 = strtotime('+4 month', $timenap);
    //     $ordertime = 'make_time like "' . date('Y-m', $e) . '%"';
    //     $uloginwhere = 'last_login_time >=' . $e . " and last_login_time<" . $e2;

    //     $in['m4zdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->get_count();
    //     $in['m4cgdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->get_count();
    //     $in['m4czrs'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('DISTINCT users_id')->get_count();
    //     $intmp = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('sum(fact_price) as m')->get_one();
    //     $in['m4czze'] = $intmp['m'];
    //     $in['m4yh'] = T('third_party_user')->set_where($uwhere)->set_where($uloginwhere)->get_count();

    //     $e = strtotime('+4 month', $timenap);
    //     $e2 = strtotime('+5 month', $timenap);
    //     $ordertime = 'make_time like "' . date('Y-m', $e) . '%"';
    //     $uloginwhere = 'last_login_time >=' . $e . " and last_login_time<" . $e2;

    //     $in['m5zdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->get_count();
    //     $in['m5cgdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->get_count();
    //     $in['m5czrs'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('DISTINCT users_id')->get_count();
    //     $intmp = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('sum(fact_price) as m')->get_one();
    //     $in['m5czze'] = $intmp['m'];
    //     $in['m5yh'] = T('third_party_user')->set_where($uwhere)->set_where($uloginwhere)->get_count();
    //     $in['dates'] = date('Ym', $timenap);
    //     $in['addtime'] = time();
    //     T('n_recycleuser')->add($in);
    // }
    // private function uprow($timenap)
    // {

    //     $e = strtotime('+1 month', $timenap);
    //     $uwhere = 'create_time >=' . $timenap . " and create_time<" . $e;
    //     $orderwhere = ['pay_status' => 1];
    //     $ordertime = 'make_time like "' . date('Y-m', $timenap) . '%"';

    //     $in['m1xyh'] = T('third_party_user')->set_where($uwhere)->get_count();
    //     $in['m1zdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->get_count();
    //     $in['m1cgdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->get_count();
    //     $in['m1czrs'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('DISTINCT users_id')->get_count();
    //     $intmp = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('sum(fact_price) as m')->get_one();
    //     $in['m1czze'] = $intmp['m'];

    //     $e = strtotime('+1 month', $timenap);
    //     $e2 = strtotime('+2 month', $timenap);
    //     $ordertime = 'make_time like "' . date('Y-m', $e) . '%"';
    //     $uloginwhere = 'last_login_time >=' . $e . " and last_login_time<" . $e2;

    //     $in['m2zdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->get_count();
    //     $in['m2cgdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->get_count();
    //     $in['m2czrs'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('DISTINCT users_id')->get_count();
    //     $intmp = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('sum(fact_price) as m')->get_one();
    //     $in['m2czze'] = $intmp['m'];
    //     $in['m2yh'] = T('third_party_user')->set_where($uwhere)->get_count();

    //     $e = strtotime('+2 month', $timenap);
    //     $e2 = strtotime('+3 month', $timenap);
    //     $ordertime = 'make_time like "' . date('Y-m', $e) . '%"';
    //     $uloginwhere = 'last_login_time >=' . $e . " and last_login_time<" . $e2;

    //     $in['m3zdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->get_count();
    //     $in['m3cgdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->get_count();
    //     $in['m3czrs'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('DISTINCT users_id')->get_count();
    //     $intmp = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('sum(fact_price) as m')->get_one();
    //     $in['m3czze'] = $intmp['m'];
    //     $in['m3yh'] = T('third_party_user')->set_where($uwhere)->get_count();

    //     $e = strtotime('+3 month', $timenap);
    //     $e2 = strtotime('+4 month', $timenap);
    //     $ordertime = 'make_time like "' . date('Y-m', $e) . '%"';
    //     $uloginwhere = 'last_login_time >=' . $e . " and last_login_time<" . $e2;

    //     $in['m4zdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->get_count();
    //     $in['m4cgdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->get_count();
    //     $in['m4czrs'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('DISTINCT users_id')->get_count();
    //     $intmp = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('sum(fact_price) as m')->get_one();
    //     $in['m4czze'] = $intmp['m'];
    //     $in['m4yh'] = T('third_party_user')->set_where($uwhere)->get_count();

    //     $e = strtotime('+4 month', $timenap);
    //     $e2 = strtotime('+5 month', $timenap);
    //     $ordertime = 'make_time like "' . date('Y-m', $e) . '%"';
    //     $uloginwhere = 'last_login_time >=' . $e . " and last_login_time<" . $e2;

    //     $in['m5zdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->get_count();
    //     $in['m5cgdd'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->get_count();
    //     $in['m5czrs'] = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('DISTINCT users_id')->get_count();
    //     $intmp = T('order')->join_table(['t' => 'third_party_user', 'users_id', 'id'])->set_where($uwhere)->set_where($ordertime)->set_where($orderwhere)->set_field('sum(fact_price) as m')->get_one();
    //     $in['m5czze'] = $intmp['m'];
    //     $in['m5yh'] = T('third_party_user')->set_where($uwhere)->get_count();
    //     $w['dates'] = date('Ym', $timenap);
    //     $in['addtime'] = time();
    //     T('n_recycleuser')->update($in, $w);
    // }
    // public function control_getid()
    // {
    //     var_dump(get_current_user());
    //     echo shell_exec("sudo php -v");
    // }

    public function control_hook()
    {
        $post_data_origin = @file_get_contents("php://input");
        $post_data = json_decode($post_data_origin, true);
        //$pwd = '/data/www/aikoversea.com/apiv1.aikoversea.com';
        $pwd = ROOT;
        //d($pwd);
        $command = 'cd ' . $pwd . ' && sudo -Hu root git pull';
        $output = shell_exec($command);
        d(shell_exec("id -a"));
        // echo shell_exec("git  remote -v");
        d($output);
        // echo '输出1';
        if ($output == null) {
            //合并冲突
            // $this->gitmerger();
            // Log::txt('更新失败', 'hook.txt');
            // d("更新失败");
            die();
        } else {
            // $remove_cache_command = 'rm -rf ' . $pwd . '_run_temp/*';
            // shell_exec($remove_cache_command);
            // Log::txt('更新成功' . $output, 'hook.txt');
            d("更新成功1");
            die();
        }
        print $output;
    }
    private function gitmerger()
    {
        $pwd = '/data/www/aikoversea.com/apiv1.aikoversea.com';
        $command = 'cd ' . $pwd . '&& git stash && git pull';
        $output = shell_exec($command);
        echo $output;
        //echo '输出2';
        if ($output == null) {
            //合并冲突
            Log::txt('更新失败', 'hook.txt');
            d("更新失败");
            die();
        } else {
            // $remove_cache_command = 'rm -rf ' . $pwd . '_run_temp/*';
            // shell_exec($remove_cache_command);
            Log::txt('更新成功' . $output, 'hook.txt');
            d("更新成功2");
            die();
        }
    }
}
