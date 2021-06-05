<?php



namespace ng169\tool;

checktop();

class Asyn
{
    public static $keyname = 'yasyn';
    public static function start($url,$parm = null,$key = null)
    {
        $ssl = substr($url, 0, 8) == 'https://' ? true : false;
        $curl = curl_init();
        if (!is_null($proxy))
        curl_setopt($curl, CURLOPT_PROXY, $proxy);
        curl_setopt($curl, CURLOPT_URL, $url);
        if ($ssl) {
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 1);
        }
        $ng169_asyn_index      = 'ng169'.rand(10000,99999);
        $ng169_asyn_key      = rand(10000,99999);
        /*$index    = (self::$keyname);*/
        $keyarray = array('ng169_asyn_index'=>($ng169_asyn_index),'ng169_asyn_key'=>($ng169_asyn_key));
        T('asyn_key')->add(array('index'=>$ng169_asyn_index,'key'=>$ng169_asyn_key,'flag'=>0,'addtime'=>time()));
        /*Y::LoadCache('file');
        $cache = new cfileClass();
        $cache->set($index,$key);*/
        
        curl_setopt($curl, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);
        curl_setopt($curl, CURLOPT_HEADER, false);
        curl_setopt($curl, CURLOPT_POST, true);
        if (is_array($parm)) {
            $parm = array_merge($parm,$keyarray);
        }else {
            $parm = $keyarray;
        }
        curl_setopt($curl, CURLOPT_POSTFIELDS, $parm);
        @curl_setopt($curl, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 1);
        curl_exec($curl);
        curl_close($curl);
        return 0;
    }
    public static function issign()
    {
        /*$cache = new cfileClass();
        $index = (self::$keyname);
        list($status,$value1) = $cache->get($index);
        $cache->del($index);
        $value2 = get(array('string'=>array($index)));
        $value2 = $value2[$index];
        return md5($value1) == $value2;*/
       $value2 = get(array('string'=>array('ng169_asyn_index'=>1,'ng169_asyn_key'=>1))); 
        
        $where=array('index'=>$value2['ng169_asyn_index'],'key'=>$value2['ng169_asyn_key']);
        $where['flag']=0;
        $in=T('asyn_key')->order_by(array('f'=>'addtime','s'=>'down'))->get_one($where);
        if($in){
			T('asyn_key')->update(array('flag'=>1),$where);
			if((time()-$in['addtime'])<60){
				//大于60秒的链接失败
				return true;
			}
			return false;
		}return false;
        
    }


}

?>
