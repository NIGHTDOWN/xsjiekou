<?php




if(!defined('IN_OEPHP')) {
	exit('Access Denied');
}

function datatool_adm_sidebar() {
	echo "<li><a href='".__ADMIN_FILE__."?c=plugin&a=setting&plugin_id=datatool' target='main'>数据库备份</a></li>";
	echo "<li><a href='".__ADMIN_FILE__."?c=plugin&a=setting&plugin_id=datatool&do=import' target='main'>数据库恢复</a></li>";
}
XHook::addAction('adm_sidebar_ext', 'datatool_adm_sidebar');
?>
