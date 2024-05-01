<?php


namespace ng169\control\api;
use ng169\control\apibase;
use ng169\tool\Url as YUrl;
use ng169\tool\Upfile;
use ng169\tool\Image;
use ng169\tool\Out;
use ng169\service\Output;
use ng169\cache\Rediscache;
use ng169\Y;


checktop();

class upimg extends apibase{
	private $dir_base = "data/attachment/";
	private $config=array('filetype'=>'','upfilepath'=>'','upfilesize'=>'');
	protected $noNeedLogin = ['run','admin'];
	public function control_run(){

		
		$conf = $this->config;
		$confs=T('option')->get_one(['option_name'=>'upload_setting']);
		
		$confs=json_decode($confs['option_value'],1);
		
		$conf['filetype']=$confs['file_types']['image']['extensions'];
		$conf['upfilepath']=$confs['upload_url'];
		$conf['upfilesize']=$confs['file_types']['image']['upload_max_filesize'];
		$conf['save_url']=$confs['save_url'];
		$upobj = new Upfile($conf);
		
		$f='';
		if($_FILES){
			$out = null;
			foreach($_FILES as $key => $name){
				$a = $upobj->upload($key);
				
				if(!$a['flag']){
					/*M('log','am')->log(false,null,null,null,$a.error);*/
					//					out($a['error'],null,$a['flag']);
					Out::jerror($a['error']);
				}
				if( $a['data']['source']){
					/*if(YImage::isimg($a['data']['source'])){
					$sizes=explode(',',Y::$conf['max_img_size']);
					$size['width']=$sizes[0];
					$size['height']=$sizes[1];
					YImage::makeThumb($a['data']['source'],$size,$a['data']['source']);
                    
					}*/
					$f .= $a['data']['source'].',';
				}
			}
			$f=trim($f,',');
			/*M('log','am')->log(true,null,null,null,$f);*/
			Out::jout($f);
		}
	}
	public function control_admin(){

		
		$conf = $this->config;
		$confs=T('option')->get_one(['option_name'=>'upload_setting']);
		
		$confs=json_decode($confs['option_value'],1);
		
		$conf['filetype']=$confs['file_types']['image']['extensions'];
		$conf['upfilepath']=$confs['upload_url'];
		$conf['upfilesize']=$confs['file_types']['image']['upload_max_filesize'];
		$conf['save_url']=$confs['save_url'];
		$upobj = new Upfile($conf);
		
		$f='';
		if($_FILES){
			$out = null;
			foreach($_FILES as $key => $name){
				$a = $upobj->upload($key,null,'adminmsg');
				
				if(!$a['flag']){
					/*M('log','am')->log(false,null,null,null,$a.error);*/
					//					out($a['error'],null,$a['flag']);
					Out::jerror($a['error']);
				}
				if( $a['data']['source']){
					/*if(YImage::isimg($a['data']['source'])){
					$sizes=explode(',',Y::$conf['max_img_size']);
					$size['width']=$sizes[0];
					$size['height']=$sizes[1];
					YImage::makeThumb($a['data']['source'],$size,$a['data']['source']);
                    
					}*/
					$f .= $a['data']['source'].',';
				}
			}
			$f=trim($f,',');
			/*M('log','am')->log(true,null,null,null,$f);*/
			Out::jout($f);
		}
	}
	public function control_getresimage(){

		$get=get(['int'=>['chatid'=>1,'size','length']]);		
		$conf = $this->config;
		$conf['filetype']=Y::$conf['filetype'];
		$conf['upfilepath']=Y::$conf['upfilepath'].'/'.D_GROUP.'/upfile/img/';
		$conf['upfilesize']=Y::$conf['upfilesize'];
		$upobj = new Upfile($conf);
		$f='';
		
		if($_FILES){
			$out = [];
			foreach($_FILES as $key => $name){
				$a = $upobj->upload($key);
				if(!$a['flag']){
					Out::jerror($a['error']);
				}
				
				if( $a['data']['source']){
					
					$f .= $a['data']['source'].',';
					/*$f .= $a['data']['source'].',';*/
					$file='data/image/Thumb/'.date('Ymd').'/'.$this->get_userid().'/'.md5(time()).'.'.$a['data']['ext'];
					Image::makeThumb($a['data']['path'].'/'.$a['data']['newName'],null,$file);
					$resid=T('res')->add([
							'uid'=>$this->get_userid(),
							'url'=>$a['data']['source'],
							'chatid'=>$get['chatid'],
							'addtime'=>time(),
						]);
					$httpres='http://'.$_SERVER['SERVER_NAME'].'/'.$file;
					$ret=['resid'=>$resid,'thumb'=>$httpres,'name'=>$a['data']['name'],'size'=>$get['size'],'length'=>$get['length']];
					
					array_push($out,$ret);
				}
			}
	
			Out::jout($out);
		}
	}
	public function control_getresvideo(){

		$get=get(['int'=>['chatid'=>1,'size','length']]);		
		$conf = $this->config;
		$conf['filetype']=Y::$conf['filetype'];
		$conf['upfilepath']=Y::$conf['upfilepath'].'/'.D_GROUP.'/upfile/video/';
		$conf['upfilesize']=Y::$conf['upfilesize'];
		$upobj = new Upfile($conf);
		$f='';
		//Out::jout($_FILES);
		if($_FILES){
			$out = [];$ret=[];
			foreach($_FILES as $key => $name){
				$a = $upobj->upload($key);
				if(!$a['flag']){
					Out::jerror($a['error']);
				}
				$insimg=['png','jpg','jpeg','bmp'];
				/*Out::jout($_FILES);*/
				if( $a['data']['source'] ){					
					$f .= $a['data']['source'].',';
					/*$f .= $a['data']['source'].',';*/
					if(in_array($a['data']['ext'],$insimg)){
						$file='data/image/Thumb/'.date('Ymd').'/'.$this->get_userid().'/'.md5(time()).'.'.$a['data']['ext'];
						Image::makeThumb($a['data']['path'].'/'.$a['data']['newName'],null,$file);
						$httpres='http://'.$_SERVER['SERVER_NAME'].'/'.$file;
						$ret['thumb']=$httpres;
					}else{
						
						$resid=T('res')->add([
								'uid'=>$this->get_userid(),
								'url'=>$a['data']['source'],
								'chatid'=>$get['chatid'],
								'addtime'=>time(),
							]);
						$ret['resid']=$resid;
						$ret['name']=$a['data']['name'];
						$ret['size']=$get['size'];
						$ret['length']=$get['length'];
						/*$ret['resid']=$resid;
						[''=>,'thumb'=>$httpres,'name'=>$a['data']['name'],'size'=>$get['size'],'length'=>$get['length']];	*/
					}					
				}
				/*$ret=array_merge($ret,$ret2);*/
			}
			array_push($out,$ret/*,['file'=>$_FILES]*/);
			Out::jout($out);
		}
	}
	
	public function control_getresother(){
		$get=get(['int'=>['chatid'=>1,'size','length']]);
		$conf = $this->config;
		$conf['filetype']=Y::$conf['filetype'];
		$conf['upfilepath']=Y::$conf['upfilepath'].'/'.D_GROUP.'/upfile/res/';
		$conf['upfilesize']=Y::$conf['upfilesize'];
		$upobj = new Upfile($conf);
		$f='';
		
		if($_FILES){
			$out = [];
			
			foreach($_FILES as $key => $name){
				$a = $upobj->upload($key);
				
				if(!$a['flag']){
					Out::jerror($a['error']);
				}
				if( $a['data']['source']){
					
					$f .= $a['data']['source'].',';
					/*$f .= $a['data']['source'].',';*/
					/*$file='data/image/Thumb/'.date('Ymd').'/'.$this->get_userid().'/'.md5(time()).'.'.$a['data']['ext'];
					Image::makeThumb($a['data']['path'].'/'.$a['data']['newName'],null,$file);*/
					$resid=T('res')->add([
							'uid'=>$this->get_userid(),
							'url'=>$a['data']['source'],
							'chatid'=>$get['chatid'],
							'addtime'=>time(),
					
							/*	'addtime'=>time(),*/
						]);
					//$httpres='http://'.$_SERVER['SERVER_NAME'].'/'.$file;
					$ret=['resid'=>$resid,'name'=>$a['data']['name'],'size'=>$get['size'],'length'=>$get['length']];
					array_push($out,$ret);
				}
			}
	
			Out::jout($out);
		}
	}
}

?>
