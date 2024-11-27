<?php

namespace ng169\control\apiv1;

use ng169\control\apiv1base;
use ng169\tool\Out;
use ng169\tool\Upfile;
checktop();
class upbook extends apiv1base
{
    protected $noNeedLogin = ['*'];
    public $needup = true;
    //获取消息记录
    public function control_needup()
    {
        //是否上传状态

        $this->returnSuccess($this->needup ? 1 : 0);
    }
    //获取消息
    public function control_up()
    {
        if (!$this->needup) Out::jerror('上传已经关闭', null, '10147');

        $conf = $this->config;
		$confs=T('option')->get_one(['option_name'=>'upload_setting']);
		
		$confs=json_decode($confs['option_value'],1);
		
		$conf['filetype']=$confs['file_types']['file']['extensions'];
		$conf['upfilepath']=$confs['upload_url'];
		$conf['upfilesize']=$confs['file_types']['file']['upload_max_filesize'];
		$conf['save_url']=$confs['save_url'];
		$upobj = new Upfile($conf);
		
		$f='';
		if($_FILES){
			$out = null;
			foreach($_FILES as $key => $name){
				$a = $upobj->upload($key,null,'upbook',[],true);
				
				if(!$a['flag']){
					/*M('log','am')->log(false,null,null,null,$a.error);*/
					//					out($a['error'],null,$a['flag']);
					Out::jerror($a['error']);
				}
				if( $a['data']['source']){
					
					$f .= $a['data']['source'].',';
				}
			}
			$f=trim($f,',');
			/*M('log','am')->log(true,null,null,null,$f);*/
			Out::jout($f);
		}
        Out::jerror('没上传任何东西', null, '10148');
    }
}
