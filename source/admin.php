<?php



$c=D_MEDTHOD;
$a=D_FUNC;
#载入控制器文件
$control_base = CONTROL . '/adminbase.php';
$control_path = CONTROL . '/admin/' . $c . '.php';

if (!file_exists($control_path)) {
	
    YOut::page404();
} else {
    im($control_base); 
    im($control_path); 
    if ($c == 'search') {
        $control = new search();
        $method = G_ACTION_PRE.'run';
    } else {
		
        $control = new control();
        $method = G_ACTION_PRE . $a;
    }
	
    if (method_exists($control, $method) && $a{0} != '_') {
		
        $control->$method();
    } else {
        YOut::page404();
        YOut::error('Admin Controller Action [' . $a . '] is not found!');
    }
    unset($control);
}
#验证是否为有效Controller
function _check_valid_controller($name)
{
    $validc = APP::loadingValidController('index');
    if (empty($validc)) {
        YOut::page404();
        YOut::error('All index Controller is forbiden!');
        die();
    } else {
        if (!in_array($name, $validc)) {
            YOut::page404();
            YOut::error('Index Controller [' . $name . '] is forbiden!');
            die();
        }
    }
}
?>
