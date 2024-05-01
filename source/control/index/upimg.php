<?php


namespace ng169\control\index;
use ng169\control\indexbase;
use ng169\tool\Url as YUrl;
use ng169\tool\Upfile;
use ng169\service\Output;
use ng169\cache\Rediscache;
use ng169\tool\Out;
use ng169\Y;


checktop();

class upimg extends indexbase{
	private $dir_base = "data/attachment/";
	private $config=array('filetype'=>'gif,png,jpg,bmp','upfilepath'=>'','upfilesize'=>'20000');
  
	public function control_run(){
		/*Y::import('upfile', 'tool');
		Y::loadTool('image');*/
		$conf = $this->config;
		$conf['filetype']=$conf['filetype']?$conf['filetype']:Y::$conf['filetype'];
		$conf['upfilepath']=$conf['upfilepath']?$conf['upfilepath']:Y::$conf['upfilepath'].'/'.D_GROUP.'/upfile/';
		$conf['upfilesize']=$conf['upfilesize']?$conf['upfilesize']:Y::$conf['upfilesize'];
		$upobj = new Upfile($conf);
		$f='';
		
		if($_FILES){
			$out = null;
			foreach($_FILES as $key => $name){
				$a = $upobj->upload($key,null,$this->dir_base);
				if(!$a['flag']){
					/*M('log','am')->log(false,null,null,null,$a.error);*/
					out($a['error'],null,$a['flag']);
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
			out($f);
		}
	}
	public function control_jsonp(){
		/*Y::import('upfile', 'tool');
		Y::loadTool('image');*/
		$conf = $this->config;
		$conf['filetype']=$conf['filetype']?$conf['filetype']:Y::$conf['filetype'];
		$conf['upfilepath']=$conf['upfilepath']?$conf['upfilepath']:Y::$conf['upfilepath'].'/'.D_GROUP.'/upfile/';
		$conf['upfilesize']=$conf['upfilesize']?$conf['upfilesize']:Y::$conf['upfilesize'];
		$upobj = new Upfile($conf);
		$f='';
		
		if($_FILES){
			$out = null;
			foreach($_FILES as $key => $name){
				$a = $upobj->upload($key,null,$this->dir_base);
				if(!$a['flag']){
					/*M('log','am')->log(false,null,null,null,$a.error);*/
					out($a['error'],null,$a['flag']);
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
			Out::jpout($f);
		}
	}
	public function control_file(){
		
		Y::import('upfile', 'tool');
		Y::loadTool('image');
		$conf = $this->config;
		$conf['filetype']=Y::$conf['filetype'];
		$conf['upfilepath']=Y::$conf['upfilepath'].'/tmp/';
		$conf['upfilesize']=Y::$conf['upfilesize'];
		$upobj = new upfileClass($conf);
		if($_FILES){
			$out = null;
			foreach($_FILES as $key => $name){
				$a = $upobj->upload($key);
                
				if(!$a['flag']){
					M('log','am')->log(false,null,null,null,$a.error);
					out($a['error'],null,$a['flag']);
                    
				}
				$f = $a['data']['source'];
			}
			M('log','am')->log(true,null,null,null,$f);
			out($f,null,1);
		}
	}
	public function control_json_up(){

		Y::import('upfile', 'tool');
		Y::loadTool('image');
		$conf = $this->config;
		$conf['upfilepath']=Y::$conf['upfilepath'].'/kfile/';
		if(YRequest::getGet('dir')=='image'){
			$conf['filetype']=Y::$conf['imgtype'];
			$conf['upfilesize']=Y::$conf['imgfilesize'];
		}else{
			$conf['filetype']=Y::$conf['filetype'];
			$conf['upfilesize']=Y::$conf['upfilesize'];
		}
        
		$upobj = new upfileClass($conf);
		if($_FILES){
			$out = null;
			foreach($_FILES as $key => $name){
				$a = $upobj->upload($key);
				if(!$a['flag']){
					echo json_encode(array('error' => 1, 'message' => $a['error']));
					exit;
				}
				$f = $a['data']['source'];
			}
			echo json_encode(array('error' => 0, 'url' => $f));
			exit;
		}
        
        
		die();
        
        


	}
	
}

?>
