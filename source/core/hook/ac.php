<?php


namespace  ng169\hook;
use ng169\Y;
use ng169\TPL;
checktop();

function ac($params)
{
   

    if (!empty($params)) {
        @extract(strtolower($params));
        $param=array();
        $group = (trim($params['group']))?(trim($params['group'])):'index';
        $mod = (trim($params['mod']));
        $action = (trim(@$params['action']));

       if($action!=null){
         
           return getactionname($action,$mod,$group);
       }
        
       if($mod!=null){
           return getmodname($mod,$group);
       }

     

    }
}


TPL::regFunction('ac', 'ac');



?>
