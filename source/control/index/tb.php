<?php


namespace ng169\control\index;

use FacebookAds\Api;
use ng169\cache\Rediscache;
use ng169\control\indexbase;
use ng169\lib\Log;
use ng169\model\index\hbyyt;
use ng169\tool\Code;
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
        im(ROOT . "/cli/spiner/spbase/taobaobase.php");
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
        im(ROOT . "/cli/spiner/spbase/taobaobase.php");
        $tbsp = new taobaobase();
        if (!$this->checkid($get['appid']))
            Out::jerror("余额不足；请充值");
        $tbsp->setck($get['cookie']);
        $dt = $tbsp->getbookdetaillink($get['pid']);
        Out::jout($dt);
    }
    public function control_usenum()
    {
        $get = get(['string' => ["appid" => 1]]);
        // $tbsp = new taobaobase();
        if (!$this->checkid($get['appid']))
            Out::jout(0);
        // $tbsp->setck($get['cookie']);

        Out::jout(1);
    }
    private function checkid($appid)
    {
        // $get = get(['string' => ["appid" => 1]]);
        if (!$appid) return false;
        $get = ["appid" => $appid];
        $data =  T('spuser')->set_where($get)->find();
        if (!$data) return false;
        if ($data['num'] < 0) return false;
        T('spuser')->update(['num' => 'num-1'], $get, 0);
        return true;
    }
    public function control_getnum()
    {
        $get = get(['string' => ["appid" => 1]]);

        $data =  T('spuser')->set_where($get)->find();

        if ($data) {
            Out::jout($data['num']);
        } else {
            Out::page404();
        }
    }
    //营业厅
    public function control_ydecbencode()
    {
        $get = get(['string' => ["str" => 1]]);
        $get['str']=base64_decode($get['str']);
        $key = "910BB48C1B4DF9561B530E7340F1EEE82AEA9647635DE2985E56C08F0B3BA6FF14F9020B5F4C7A1A4E0CE74FF388CBB9A6A00F152FD3CEDE50093036DE258CF9,108BB491826D3F228CC15468FAF1F89DE37ABA7BC85B369E983E9432CC943927AE7C5DC23FAFEADCB9BF362B66E22F07EA194BF94176B315E400E494738A926F";
        $code = new Code();
        $data = $code->encode($get['str'], $key);
        $data = hbyyt::encode($data);
        if ($data) {
            Out::jout($data);
        } else {
            Out::page404();
        }
    }
    //营业厅
    public function control_ydEcbdecode()
    {
        $get = get(['string' => ["str" => 1]]);
        $get['str']=base64_decode($get['str']);
        $key = "910BB48C1B4DF9561B530E7340F1EEE82AEA9647635DE2985E56C08F0B3BA6FF14F9020B5F4C7A1A4E0CE74FF388CBB9A6A00F152FD3CEDE50093036DE258CF9,108BB491826D3F228CC15468FAF1F89DE37ABA7BC85B369E983E9432CC943927AE7C5DC23FAFEADCB9BF362B66E22F07EA194BF94176B315E400E494738A926F";
        $code = new Code();
        $data = $code->decode($get['str'], $key);
        if ($data) {
            Out::jout($data);
        } else {
            Out::page404();
        }
    }
}
