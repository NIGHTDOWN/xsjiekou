<?php

namespace ng169\model\index;

use ng169\Y;

checktop();
//统计埋点
class city extends Y
{
     private  $key="citycache";
    public function getinfo($cityname)
    {
        $key=$this->key.$cityname;
        list($bool,$data)=Y::$cache->get($key);
        if($bool)return $data; 
        $in = T('city')->get_one(['cityname' => $cityname]);
       
        if($in){
            Y::$cache->set($key,$in,G_DAY*31);
        }
        return $in; 
    }
}
