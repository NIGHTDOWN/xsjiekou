<?php


$c=D_MEDTHOD;
$a=D_FUNC;
#载入控制器文件
$control_base = CONTROL . '/apibase.php';
$control_path = CONTROL . '/api/' . $c . '.php';
 
if (!file_exists($control_path)) {
	
    YOut::page404();
} else {
    im($control_base); 
    im($control_path); 
  
       
        $control = new control();
      
        $method = G_ACTION_PRE . $a;
  /*  }*/

    if (method_exists($control, $method) && $a{0} != '_') {
		
        $control->$method();
	
    } else {
    	
        YOut::page404();
        YOut::error('API Controller Action [' . $a . '] is not found!');
    }
    unset($control);
}


?>
