<?php




if(!defined('IN_OEPHP')) {
	exit('Access Denied');
}
;echo '
';

function sharebar_plugin_loading() {
;echo '
<link href="'; echo PATH_URL;;echo 'source/plugin/sharebar/sharebar.css" rel="stylesheet" type="text/css" />
<script src="'; echo PATH_URL;;echo 'source/plugin/sharebar/sharebar.js"></script>
<script type="text/javascript">
var share_site_url = "'; echo Y::$conf['siteurl'];;echo '";
var share_site_name = "'; echo Y::$conf['sitename'];;echo '";
</script>
';
}

XHook::addAction('index_head', 'sharebar_plugin_loading');
;echo '
';

function sharebar_plugin_show(){
;echo '
<p class="pg_share pg_clearfix">
  <span><a class="t_qq" title="分享到腾讯微博">腾讯微博</a></span>
  <span><a class="sina" title="分享到新浪微博">新浪微博</a></span>
  <span><a class="twitter" title="分享到Twitter">Twitter</a></span>
  <span><a class="renren" title="分享到人人网">人人网</a></span>
  <span><a class="qzone" title="分享到QQ空间">QQ空间</a></span>
</p>

';
}	


XHook::addAction('content_share', 'sharebar_plugin_show');
?>
