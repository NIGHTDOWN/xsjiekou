<?php




if(!defined('IN_OEPHP')) {
	exit('Access Denied');
}

require_once(ROOT.'./source/plugin/online/function.php');

function online_adm_sidebar() {
	echo "<li><a href='".__ADMIN_FILE__."?c=plugin&a=setting&plugin_id=online' target='main'>在线客服设置</a></li>";
	echo "<li><a href='".__ADMIN_FILE__."?c=plugin&a=setting&plugin_id=online&do=list' target='main'>添加客服</a></li>";
}
XHook::addAction('adm_sidebar_ext', 'online_adm_sidebar');


XHook::addAction('event_online', 'online_plugin_preview');
?>
