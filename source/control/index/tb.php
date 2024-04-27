<?php


namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\tool\Out;
use taobaobase;
checktop();
class tb extends indexbase
{
    protected $noNeedLogin = ['*'];
    public function control_run()
    {
        //是否https
        $this->view(null);
    }
    public function control_getInfo()
    {
        $get = get(['string' => ['cookie' => 1, "appid" => 1, "pid" => 1]]);
        im(ROOT."/cli/spiner/spbase/taobaobase.php");
        $tbsp = new taobaobase();
        if (!$this->checkid($get['appid']))
            Out::jerror("余额不足；请充值");
        $tbsp->setck($get['cookie']);
        $dt = $tbsp->getbookdetail($get['pid']);
        Out::jout($dt);
    }
    public function control_getlink()
    {
        $get = get(['string' => ['cookie' => 1, "appid" => 1, "pid" => 1]]);
        im(ROOT."/cli/spiner/spbase/taobaobase.php");
        $tbsp = new taobaobase();
        if (!$this->checkid($get['appid']))
            Out::jerror("余额不足；请充值");
        $tbsp->setck($get['cookie']);
        $dt = $tbsp->getbookdetaillink($get['pid']);
        Out::jout($dt);
    }
    private function checkid($appid)
    {
        // $get = get(['string' => ["appid" => 1]]);
        if(!$appid)return false;
        $get=["appid" => $appid];
        $data=  T('spuser')->set_where($get)->find();
        if(!$data)return false;
        if($data['num']<0)return false;
        T('spuser')->update(['num'=>'num-1'],$get,0);
        return true;
    }
    public function control_getnum()
    {
        $get = get(['string' => ["appid" => 1]]);
      
         $data=  T('spuser')->set_where($get)->find();
        
         if($data){
            Out::jout($data['num']);
         }else{
            Out::page404();
         }
    }
}
