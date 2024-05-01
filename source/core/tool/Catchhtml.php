<?php


checktop();
class catchhtml
{
	private $pageint = 0;
	private $proloop = 0;
	private $dieloop_MAX = 4;
	private $dieloop_STRAT = 0;


	private $cj_pronum = 1000;
	private $promuid = array();
  	



	private function checkuser($user = null)
	{
		if(!$user){
			$user = '114141'.rand(000000,999999);
			return $this->checkuser($user);
		}

		$u = T('user')->get_one(array('mobile'=>$user));
		if($u)
		{
			$user = '114141'.rand(000000,999999);
			return $this->checkuser($user);
		}
		return $user;
	}
	public function adduser($name)
	{
		$insert['type'] = 2;
		$insert['mobile'] = $this->checkuser();
		$insert['username'] = $insert['mobile'];
		$insert['nickname'] = $name;
		$insert['userrz'] = 1;
		$insert['email'] = 0;
		$insert['realname'] = $insert['nickname'];
		return T('user')->add($insert);
	}
	public function getaddressid($address)
	{

		if(!$address)return false;
		$add   = explode(' ',$address);
		$proid = T('province')->set_field('provinceID as id')->set_where("province like \"".$add[0]."%\"")->get_one();
		$cityid= T('city')->set_field('cityID as id')->set_where("city like \"".$add[1]."%\"")->get_one();
		return array($proid['id'],$cityid['id']);
	}
	public function getlocalimgname($img)
	{

		if(!preg_match('/\/([^\/]+\.([a-z]{3,4}))$/i',$img,$matches))return false;

		$attchementdir = getcwd().'/data/attachment';
		$ext           = $matches[2];
		$datedir       = date("Ym", time()) . "/" . date("d", time()). "/";
		$path1         = $attchementdir . $datedir;

		$newName       = substr(md5(time() . rand(00000,99999)), 8, 16).'.'.$ext;
		Y::loadTool('file');
		YFile::createDir($path1);
		$image_name    = $path1.$newName;
		return $image_name;
	}
	public function creatlist($id)
	{
		if($this->dieloop_MAX <= $this->dieloop_STRAT)
		{
			die('本次爬行中断#'.$id);
		}
		$obj = new catch_html();
		$out = $obj->exec($id);

		/*$out = str_replace('\\','/',$out);*/
		if(!$out)
		{
			$this->dieloop_STRAT += 1;

		}
		else
		{
			$this->dieloop_STRAT = 0;
		}

		$out   = strip_tags($out);

		$rep1  = '/\"nid\"[\:]{1}\"([^\"]*)\",/';
		$rep2  = '/\"item_loc\"[\:]{1}\"([^\"]*)\",/';
		$rep3  = '/\"nick\"[\:]{1}\"([^\"]*)\",/';
		$rep4  = '/\"raw_title\"[\:]{1}\"([^\"]*)\",/';
		$p     = preg_match_all($rep1,$out,$matcg);
		preg_match_all($rep2,$out,$matcg2);
		preg_match_all($rep3,$out,$matcg3);
		/*preg_match_all($rep3,$out,$matcg4);*/


		$lists = $matcg[1];


		/*$lists = $out['API.CustomizedApi']['itemlist']['auctions'];*/
		/*d($out);*/
		if(!is_array($lists))return false;
		$data = T('tmp1')->get_one(array('lid'=>$id));
		$num = sizeof($lists);
		$this->pageint = $data['s'] + $num;
		T('tmp1')->update(array('s'=>$this->pageint),array('lid'=>$id));

		foreach($lists as $k=>$pro){

			$insert['tid'] = $pro;
			$d = T('tmp2')->get_one($insert);
			/*d($d);
			d($pro['nid']);*/
			if($d)break;
			$size = Y::$conf['qy_shop_pro_num'];
			$index= intval($this->proloop / $size);
			$this->proloop += 1;

			if(isset($this->promuid[$index])){
				$insert['muid'] = $this->promuid[$index];
			}
			else
			{

				$this->promuid[$index] = $this->addstore($matcg3[1][$k],$matcg2[1][$k],null);

				/*$this->promuid[$index]=$insert['muid'];*/
				$insert['muid'] = $this->promuid[$index];
			}

			$insert['type'] = 0;
			$insert['flag'] = 0;
			$insert['lid'] = $id;
			/*d($insert);
			d($matcg4[1][$k]);*/
			try
			{
				T('tmp2')->add($insert);
			} catch(Exception $v)
			{
				return $v;
			}

		}
		return true;
	}
	public function getlogo($muid)
	{

		$obj = new catch_html();
		$out = $obj->catchlogo($muid);

		$out = strip_tags($out);
		$rep = '/\"TBGOODSLINK\"[\:]{1}\"([^\"]*)\"/';
		$p   = preg_match_all($rep,$out,$matcg);

		if($p){
			
			$img = $matcg[1][0];
			$img = str_replace('\\','/',$img);
			$img = str_replace('////','//',$img);//修复采集的 url

			Y::loadTool('image');
			$img = YImage::imgtolocal($img);



			return $img;
		}
		return false;
	}
	public function addstore($name,$address,$pid)
	{
		$merchant['merchantname'] = $name;
		$is = T('merchant')->get_one($merchant);
		if($is)return $is['muid'];
		$merchant['uid'] = $this->adduser($name);
		/*$merchant['uid']=$this->adduser($name);*/
		$merchant['rzflag'] = 2;
		list($merchant['provinceid'],$merchant['cityid']) = $this->getaddressid($address);
		$muid = T('merchant')->add($merchant);
		/*$img  = $this->getlogo($muid);

		if($img){
			T('user')->update(array('headimg'=>$img),array('uid'=>$merchant['uid']));
			T('merchant')->update(array('logo'=>$img),array('muid'=>$muid));
		}*/
		return $muid;
		//每20个商品创建一个店铺
		//通过店铺名称获取地理id
		//通过名称采集店铺logo
		//创建店铺创建用户
		//
		//返回商户id
	}
	public function start($word)
	{

		/*$str=urlencode('衣服');*/
		if(!$word)return false;

		$num = $this->cj_pronum;
		$word= $word;
		$data= T('tmp1')->set_where('word="'.$word.'"')->get_one();

		if($data)
		{

			$id = $data['lid'];
			$this->pageint = $data['s'];
			if($data['s'] >= $num)
			{
				return 1;
			}
			while($this->pageint <= $num){

				$this->creatlist($id);

			}

		}
		else
		{

			$id = T('tmp1')->add(array('word'=>$word,'num' =>$num));

			while($this->pageint <= $num){
				$this->creatlist($id);

			}
		};

		return 1;

		die();

	}
	public function startpro($tid)
	{

		/*$str=urlencode('衣服');*/
	if(!$tid)return false;
		
		$data = T('tmp2')->set_where('flag=1 and tid='.$tid)->join_table(array('t'=>'tmp1','lid','lid'))->get_one();

		


		if($data)
		{
          
			$id=$data['tid'];
			
		if($data['flag']==1){
			$cache=T('tmp4')->get_one(array('tid'=>$id));
			
			$pro=unserialize( base64_decode($cache['cache']));
			if(!$pro){
				$pro=new pro____init($id);
			$prodetail=$pro->getpro();
			$up=array('flag'=>1,'tbcontent'=>$prodetail['tbcontent']);
			T('tmp2')->update($up,array('tid'=>$id));
			if(!T('tmp4')->get_one(array('tid'=>$id))){
				T('tmp4')->add(array('tid'=>$id,'cache'=>base64_encode(serialize($pro))));
			}
				
			};
			
			/*$bool=$this->addprop($data['catid'],$pro->getprops());*/  //添加属性
			$store=T('merchant')->get_one(array('muid'=>$data['muid']));
			
			if($store['merchantname']=='' || $store['provinceid']==0){
				$this->fixstore($store,$pro->getstore(),$data['mcatid']);
			}
			
			$tbdetail=$pro->getpro();
			$tbprops=$pro->getprops();
			$tbsku=$pro->getsku();
			/*d($data,1);*/
			
			
			/*$bool=$this->addproduct();*/
			$bool=M('product','am')->add($data['muid'],$data['catid'],$tbdetail,$tbprops,$tbsku);
			if($bool){
			$up=array('flag'=>2,'tbcontent'=>$prodetail['tbcontent']);
			T('tmp2')->update($up,array('tid'=>$id));
			return 1;
			d('采集完成');
			}else{
					/*$up=array('flag'=>0);
			T('tmp2')->update($up,array('tid'=>$id));*/
			/*T('tmp4')->del(array('tid'=>$id));*/
			}
			
			
			
			
			
			
			
		}else{
			
			
			//这里表示本条已经采集
		}
		return 1;
		if($pro){
			
			
			
			
			
		
			
			$prop=$pro->getprops();
			d($prop);
			
			
			//尝试修正商户或则创建商户
			//添加商品;如果商户商品总数超过上限则跳出重新创建商户
			//尝试添加属性
			//尝试添加规格
			d($data,1);
		}
		
		
		
		
		
		$comlist=$pro->getcomment(1);
		$comlist=$pro->getstore();
		
		$comlist=$pro->getsku();
		$comlist=$pro->getprops();
		d($comlist);

				/*$data=$this->getprodetail($id);*/

			
return $data;
		}else{
			$this->dieloop_STRAT+=1;
		}

		return false;

	}
//修复店铺logo等
public function dofixstore($tid)
	{

		/*$str=urlencode('衣服');*/
	if(!$tid)return false;
		
		$data = T('tmp2')->set_where(' tid='.$tid)->join_table(array('t'=>'tmp1','lid','lid'))->get_one();

		


		if($data)
		{
          
			$id=$data['tid'];
			
		if(1){
			$cache=T('tmp4')->get_one(array('tid'=>$id));
			
			$pro=unserialize( base64_decode($cache['cache']));
			
			if(!$pro){
				$pro=new pro____init($id);
			$prodetail=$pro->getpro();
			
			/*if(!$prodetail){
				
			$prodetail=$pro->getpro();	
			}*/
			$up=array('flag'=>1,'tbcontent'=>$prodetail['tbcontent']);
			T('tmp2')->update($up,array('tid'=>$id));
			if(!T('tmp4')->get_one(array('tid'=>$id))){
				T('tmp4')->add(array('tid'=>$id,'cache'=>base64_encode(serialize($pro))));
			}
				
			};
			
			
			/*$bool=$this->addprop($data['catid'],$pro->getprops());*/  //添加属性
//			$store=T('merchant')->get_one(array('muid'=>$data['muid']));
			$store=T('merchant')->join_table(array('t'=>'product','muid','muid'))->set_field('V.*')->get_one(array('tbid'=>$tid));
		
			if(1){
				$this->fixstore($store,$pro->getstore(),$data['mcatid']);
			}
			
			$tbdetail=$pro->getpro();
			
			$tbprops=$pro->getprops();
			$tbsku=$pro->getsku();
			/*d($data,1);*/
			
			
			/*$bool=$this->addproduct();*/
			$bool=M('product','am')->add($data['muid'],$data['catid'],$tbdetail,$tbprops,$tbsku);
			if($bool){
			$up=array('flag'=>2,'tbcontent'=>$prodetail['tbcontent']);
			T('tmp2')->update($up,array('tid'=>$id));
			return 1;
			d('采集完成');
			}else{
					/*$up=array('flag'=>0);
			T('tmp2')->update($up,array('tid'=>$id));*/
			/*T('tmp4')->del(array('tid'=>$id));*/
			}
			
			
			
			
			
			
			
		}else{
			
			
			//这里表示本条已经采集
		}
		return 1;
		if($pro){
			
			
			
			
			
		
			
			$prop=$pro->getprops();
			d($prop);
			
			
			//尝试修正商户或则创建商户
			//添加商品;如果商户商品总数超过上限则跳出重新创建商户
			//尝试添加属性
			//尝试添加规格
			d($data,1);
		}
		
		
		
		
		
		$comlist=$pro->getcomment(1);
		$comlist=$pro->getstore();
		
		$comlist=$pro->getsku();
		$comlist=$pro->getprops();
		d($comlist);

				/*$data=$this->getprodetail($id);*/

			
return $data;
		}else{
			$this->dieloop_STRAT+=1;
		}

		return false;

	}
//修复商品详情
public function dofixpro($tid)
	{

		/*$str=urlencode('衣服');*/
		if(!$tid)return false;
		
		$data = T('tmp2')->set_where(' tid='.$tid)->join_table(array('t'=>'tmp1','lid','lid'))->get_one();

		


		if($data)
		{
          
			$id=$data['tid'];
			
		if($data['flag']>0){
			
			if(1){
				$pro=new pro____init($id);
			$prodetail=$pro->getpro();
			
			/*if(!$prodetail){
				
			$prodetail=$pro->getpro();	
			}*/
			
			/*$up=array('flag'=>1,'tbcontent'=>$prodetail['tbcontent']);
			T('tmp2')->update($up,array('tid'=>$id));*/
			if(!T('tmp4')->get_one(array('tid'=>$id))){
				T('tmp4')->add(array('tid'=>$id,'cache'=>base64_encode(serialize($pro))));
			}
				
			};
			
			
			/*$bool=$this->addprop($data['catid'],$pro->getprops());*/  //添加属性
			
			
			$tbdetail=$pro->getpro();
			
			
			/*d($data,1);*/
			
			
			/*$bool=$this->addproduct();*/
			
			$bool=M('product','am')->update_content($data['tid'],$tbdetail);
			
			if($bool){
			$up=array('flag'=>2,'tbcontent'=>$prodetail['tbcontent']);
			T('tmp2')->update($up,array('tid'=>$id));
			return 1;
			d('采集完成');
			}else{
					/*$up=array('flag'=>0);
			T('tmp2')->update($up,array('tid'=>$id));*/
			/*T('tmp4')->del(array('tid'=>$id));*/
			}
			
			
			
			
			
			
			
		}else{
			
			
			//这里表示本条已经采集
		}
		return 1;
		if($pro){
			
			
			
			
			
		
			
			$prop=$pro->getprops();
			d($prop);
			
			
			//尝试修正商户或则创建商户
			//添加商品;如果商户商品总数超过上限则跳出重新创建商户
			//尝试添加属性
			//尝试添加规格
			d($data,1);
		}
		
		
		
		
		
		$comlist=$pro->getcomment(1);
		$comlist=$pro->getstore();
		
		$comlist=$pro->getsku();
		$comlist=$pro->getprops();
		d($comlist);

				/*$data=$this->getprodetail($id);*/

			
return $data;
		}else{
			$this->dieloop_STRAT+=1;
		}

		return false;

	}

	public function fixstore($merchant,$info,$mcatid){
		if(!$merchant)return false;
		
		$up['address']=$info['province'].' '.$info['city'];
		if(!$merchant['provinceid']){
			list($up['provinceid'],$up['cityid'])=$this->getaddressid($up['address']);
		}
		
		$up['wlpf']=$info['wlpf'];
		$up['fwpf']=$info['fwpf'];
		$up['mspf']=$info['mspf'];
		$up['catid']=$mcatid;
			Y::loadTool('image');
			$v=trim($info['logo'],'//');
			$img = YImage::imgtolocal($v,'logo');
		$up['logo']=$img;
		$up['merchantname']=$info['name'];
		$up['hits']=1;
		T('merchant')->update($up,array('muid'=>$merchant['muid']));
		
		T('user')->update(array('nickname'=>$info['nick']),array('uid'=>$merchant['uid']));
		return true;
		
	}
	//添加到分类属性里面
	public function addprop($catid,$name){
		
		if(!is_array($name))return false;
		$db='product_category_attribute';
		foreach($name as $i=>$s){
			
			$index=array_keys($s);
			
			$where=array('sname'=>$index[0],'catid'=>$catid);
			$in=T($db)->get_one($where);
			if($in){
				T($db)->update(array('weight'=>$in['weight']+1),$where);
			}else{
				$where['addtime']=time();
				T($db)->add($where);
			}
		}
		
		
		
		return true;
		/*d($name,1);*/
	}
	public function getprodetail($tb_proid){
		
		$obj = new catch_html();
		$out = $obj->catchpro($tb_proid);
		return $out;
	}
}

class pro____init{
	private $sku;//产品规格
	private $props;//产品属性
	private $tb_ulogo;//淘宝商户logo
	private $content;//商品详情
	private $tb_content;//商品未本地化图片
	private $ng_pid,$tb_pid;//本站商品id
	private $title;//商品标题
	private $desc;//商品摘要
	private $images;//商品图片
	private $tb_userid,$tb_name,$tb_nickname;//淘宝用户id
	private $tb_shopid;//淘宝用户id
	private $tb_price;//淘宝价格
	private $tb_mspf,$tb_wlpf,$tb_fwpf;//淘宝价格
	private $ng_price;//淘宝价格
	private $tb_num;//商品价格
	private $tb_logisfee;//淘宝邮费
	private $merchant;//本地商户信息
	private $tbaddress;//本地商户信息
	private $province;//本地商户信息
	private $city;//本地商户信息
	private function _imgto_locals($tbstr,$type){
		Y::loadTool('image');
		
		if(is_array($tbstr)){
			foreach($tbstr as $k=>$v){
				/*$v=str_ireplace(array('//'),array(),$v);*/
				$v=trim($v,'//');
				$tbstr[$k]=YImage::imgtolocal($v,$type);
			}
		}else{
			/*$img=str_ireplace(array('//'),array(),$img);*/
			$img=trim($img,'//');
			$img = YImage::imgtolocal($img,$type);
			return $img;
		}
		return $tbstr;
	}
	private function setsku($tbdata){
		$ret=array();
		if(is_array($tbdata)){
			foreach($tbdata as $sku){
				if(is_array($sku['values'])){
					$str='';
					foreach($sku['values'] as $val){
					$str=$str.','.$val['name'];
					}
					$str=trim($str,',');
				}
				$ret[$sku['name']]=$str;
			}
			return $ret;
		}return false;
	}
	private function setprops($tbdata){
		$ret=array();
		$data=$tbdata[0]['基本信息'];
		
		
		if(is_array($data)){
			foreach($data as $k=>$sku){
				$ret[$k]=$sku;
			
			}
			return $ret;
		}return false;
	}
	private function set_p_n($tbdata){
		$data=json_decode($tbdata,1);
		$need=$data['skuCore']['sku2info']['0'];
		$num=$need['quantity'];
		
		$tb_price=$need['price']['priceMoney'];
		
		$tb_fee=intval($data['delivery']['postage'])*100;//放大100倍
		$this->tb_logisfee=$tb_fee;
		$this->tbaddress=$data['delivery']['from'];
		
		$tb_price+=$tb_fee;
		return array($tb_price,$num);
	}
	public function getdetail($tb_pid){
		
		$tbpro_detail_url="https://hws.m.taobao.com/cache/mtop.wdetail.getItemDescx/4.1/?data=%7Bitem_num_id%3A%22{$tb_pid}%22%7D";
		
		$curlobj=Y::import('curl','tool');
		$detail=$curlobj->get($tbpro_detail_url);
		$data=json_decode($detail,1);
		if(!$data)return false;
		if(!$data['pages'])return false;
		/*if(!$data['images'])return false;*/
		
		$data=$data['data'];
		
		$imgtolocal=$this->_imgto_locals($data['images'],'detail');
		$imgbom=array();
		$imgbom1=array();
		foreach($imgtolocal as $k=>$img){
			$imgbom[$k]="<img src=$img />";
			$old=$data['images'][$k];
			$imgbom1[$k]="<img src=$old />";
			/*$data['images'][$k]="<img>".$data['images'][$k]."<img>";*/
		}
		
		$content='';
		foreach($data['pages'] as $str){
			$content.=$str;
		}
		$content1=$content;
		$content=str_ireplace($data['images'],$imgbom,$content);
		$content=str_ireplace('<img>','',$content);
		$content=$this->addzy($content);
		$content1=str_ireplace($data['images'],$imgbom1,$content1);
		$content1=str_ireplace('<img>','',$content1);
		$content1=$this->addzy($content1);
		$this->tb_content=$content1;
		
		
		return $content;
	}
public function get_tbdetail(){
		
		
		/*$this->tb_content=$content1;*/
		$tb_pid=$this->tb_pid;
		if($this->tb_content==NULL){
			$tbpro_detail_url="https://hws.m.taobao.com/cache/mtop.wdetail.getItemDescx/4.1/?data=%7Bitem_num_id%3A%22{$tb_pid}%22%7D";
		
		$curlobj=Y::import('curl','tool');
		$detail=$curlobj->get($tbpro_detail_url);
		$data=json_decode($detail,1);
		
		if(!$data)return false;
		$data=$data['data'];
		if(!$data['pages'])return false;
		/*if(!$data['images'])return false;*/
		
		
		
		$imgtolocal=$data['images'];
		$imgbom=array();
		$imgbom1=array();
		
		foreach($imgtolocal as $k=>$img){
			$imgbom[$k]="<img src=$img />";
			$old=$data['images'][$k];
			$imgbom1[$k]="<img src=$old />";
			/*$data['images'][$k]="<img>".$data['images'][$k]."<img>";*/
		}
		
		$content='';
		foreach($data['pages'] as $str){
			$content.=$str;
		}
		$content1=$content;
		$content=str_ireplace($data['images'],$imgbom,$content);
		$content=str_ireplace('<img>','',$content);
	
		$this->tb_content=$content;
		}
		
		return $content;
	}
	private function addzy($str){
//		return addslashes($str);
return $str;
	}
	private function StrLenW($str,$charset) {
    $n = 0; $p = 0; $c = '';
    $len = strlen($str);
    if($charset == 'utf-8') {
      for($i = 0; $i < $len; $i++) {
        $c = ord($str{$i});
        if($c > 252) {
          $p = 5;
        } elseif($c > 248) {
          $p = 4;
        } elseif($c > 240) {
          $p = 3;
        } elseif($c > 224) {
          $p = 2;
        } elseif($c > 192) {
          $p = 1;
        } else {
          $p = 0;
        }
        $i+=$p;$n++;
      }
    } else {
      for($i = 0; $i < $len; $i++) {
        $c = ord($str{$i});
        if($c > 127) {
          $p = 1;
        } else {
          $p = 0;
      }
        $i+=$p;$n++;
      }
    }
    return $n;
}
	private function get_p_c($address){
		$size=$this->StrLenW($address,'utf-8');
		$pi=intval($size/2);
		$ret[0]=mb_substr($address, 0, $pi, 'utf-8');
		$ret[1]=mb_substr($address, $pi, $size, 'utf-8');
		return $ret;
	}
	public function __construct($tid){
		$curlobj=Y::import('curl','tool');

		$tbpro_sku_url="https://acs.m.taobao.com/h5/mtop.taobao.detail.getdetail/6.0/?data=%7B%22itemNumId%22%3A%22{$tid}%22%7D ";
		
		$tbsku=$curlobj->get($tbpro_sku_url);
		$data=json_decode($tbsku,1);
		if(is_array($data) && $data){
			
		$pro=$data['data'];	
		/*d($pro['apiStack'][0]['value']);*/
		list($price,$num)=$this->set_p_n($pro['apiStack'][0]['value']);
		
		$this->title=$pro['item']['title'];	
		$this->tb_num=$num;	
		$this->tb_price=$price;	
		$this->ng_price=ceil(intval($price*1.1)/100)*100;	//本地价格放大10% 在取整
		$this->desc=$pro['item']['subtitle'];	
		$this->tb_pid=$pro['item']['itemId'];	
		//$this->images=$this->_imgto_locals($pro['item']['images'],'pro_img');	//已经本地化了
		$this->images=$pro['item']['images'];	
		$this->props=$this->setprops($pro['props']['groupProps']);	
		$this->sku=$this->setsku($pro['skuBase']['props']);
		$this->tb_userid=$pro['seller']['userId'];
		$this->tb_shopid=$pro['seller']['shopId'];
		$this->tb_ulogo=$pro['seller']['shopIcon'];
		$this->tb_name=$pro['seller']['shopName'];
		$this->tb_nickname=$pro['seller']['sellerNick'];
		$this->tb_fwpf=$pro['seller']['evaluates'][1]['score'];
		$this->tb_wlpf=$pro['seller']['evaluates'][2]['score'];
		$this->tb_mspf=$pro['seller']['evaluates'][0]['score'];
		list($this->province,$this->city)=$this->get_p_c($this->tbaddress);
//		$this->content=$this->getdetail($this->tb_pid); //本地化详情
		$this->content=$this->get_tbdetail();
		
		return $this;
			
			
		}
		return false;
	}
	public function getcomment($page,$ispic=1){
		$itemId=$this->tb_pid;
		$sellerId=$this->tb_userid;
		if($ispic){
			$order=3;
		}
		$url="https://rate.tmall.com/list_detail_rate.htm?itemId=$itemId&sellerId=$sellerId&order=$order&currentPage=$page";
		$curlobj=Y::import('curl','tool');

		$tbsku=$curlobj->get($url);
		$tbsku=iconv('gb2312','utf-8',$tbsku);
		
		$tbsku='{'.$tbsku.'}';
		
		
		$data=json_decode($tbsku,1);
		if(!$data)return false;
		
		return $data['rateDetail']['rateList'];
		
		
		
		
	}
	public function getstore(){
	$data['province']=$this->province;
	$data['city']=$this->city;
	$data['wlpf']=$this->tb_wlpf;
	$data['fwpf']=$this->tb_fwpf;
	$data['mspf']=$this->tb_mspf;
	$data['logo']=$this->tb_ulogo;
	$data['name']=$this->tb_name;
	$data['nick']=$this->tb_nickname;
	return $data;
	}
	public function getpro(){
	$data['title']=($this->title);
	$data['tid']=($this->tb_pid);
	$data['desc']=$this->desc;
	$data['price']=$this->ng_price;
	$data['tbprice']=$this->tb_price;
	$data['num']=$this->tb_num;
	$data['images']=$this->images;
	$data['content']=$this->content ;
	$data['tbcontent']=$this->get_tbdetail();
	return $data;
	}
	public function getsku(){
	$data=$this->sku;
	
	return $data;
	}
	public function getprops(){
	$data=$this->props;
	
	return $data;
	}
}
class catch_html
{

	private $phantomjs_file = 'e:\phantomjs\bin\phantomjs.exe';
	private $js_path = 'e:\phantomjs\bin\\';
	//加载phantomjs
	//写入url到数据库
	//把id传给抓取工具
	//抓去工具在把url获取出来
	//在去抓取
	//抓去到了在把详情地址传给后台整理
	//抓去到了在把分页地址传给后台整理
	public  function load_phantomjs($js,$id)
	{
		$domain = "http://".$_SERVER['SERVER_NAME'];
		$command= "{$this->phantomjs_file} {$this->js_path}{$js} {$domain} ".' ';

		/*$command = "e:\phantomjs\bin\phantomjs.exe e:\phantomjs\bin\catch.js {$domain} ".' ';*/

		return $command .= $id;
	}
	public function exec($id)
	{

		$com = $this->load_phantomjs('catch.js',$id);

		$out = shell_exec($com);
		return  ($out);
	}
	public function catchlogo($id)
	{


		$com = $this->load_phantomjs('catchlogo.js',$id);
		$out = shell_exec($com);

		return  ($out);
	}
	public function saveimgtolocal($id)
	{


		$com = $this->load_phantomjs('imgtolocal.js',$id);
		$out = shell_exec($com);

		return  ($out);
	}
public function catchpro($id)
	{

		$url="https://detail.tmall.com/item.htm?id=$id";
		$url="https://detail.m.tmall.com/item.htm?id=$id";
		$url="https://detail.m.tmall.com/item.htm?id=543589353922";
		$url="https://market.m.taobao.com/app/dinamic/h5-tb-detail/index.html?ft=t&id=543589353922";
		$com = $this->load_phantomjs('catchpro.js',$url);
		echo $com;
		$out = shell_exec($com);

		return  ($out);
	}


}
?>
