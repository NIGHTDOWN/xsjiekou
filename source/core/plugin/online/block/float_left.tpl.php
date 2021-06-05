<?php



if(!defined('IN_OECMS')) {
	exit('Access Denied');
}


;echo '<script language="javascript" type="text/javascript">
function Mouseclose(){
	document.getElementById(\'floatDiv\').style.display=\'none\';
}
window.onload = function(){
	var floatObj = document.getElementById(\'floatDiv\');
	Floaters.addItem(floatObj, '; echo $online['left_leftpr'];;echo ', '; echo $online['left_toppr'];;echo ');
	Floaters.sPlay();
	document.getElementById(\'floatDiv\').style.display=\'block\';
}
</script>';?>
