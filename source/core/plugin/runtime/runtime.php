<?php




if(!defined('IN_OEPHP')) {
	exit('Access Denied');
}
;echo '';

function runtime_plugin_view() {
	echo "<div align='center'><p style='font-size:10px; font-family:Arial, Helvetica, sans-serif; line-height:120%;color:#999999'>Processed in ".XRunTime::display()." second(s) , ".X::$obj->querynum." queries</p></div>";
}
XHook::addAction('adm_footer', 'runtime_plugin_view');
XHook::addAction('event_runtime', 'runtime_plugin_view');
?>
