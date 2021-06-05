<?php


namespace  ng169\hook;
use ng169\Y;
use ng169\TPL;
use ng169\tool\Url as YUrl;
checktop();

function user($params)
{
	
    if (!empty($params)) {
        @extract($params);
        $uid=@$params['uid'];
      
         
        if(!$uid){
           return false;
        }
      
       
        
        $user= T('user')->get_one(['uid'=>$uid]);
        return $user['username'];
    }
    return false;
}


TPL::regFunction('user', 'user');



?>
