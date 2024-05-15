<?php
namespace ng169\control\api;

use ng169\control\apibase;
use ng169\tool\Out;
checktop();
class im extends apibase
{
    protected $noNeedLogin = ['*'];
    //获取websocket信息
    public function control_getws()
    {
     $server=T('sockserver')->set_field("ip,port")->set_where(['flag'=>1])->get_one(); 
     if($server){
        $server['url']="ws://".$server['ip'].":".$server['port'];
        Out::jout($server);
     }  else{
        // Out::jout("");
        Out::jerror('im服务器信息获取失败', [], '20246');
     }
    }


}
