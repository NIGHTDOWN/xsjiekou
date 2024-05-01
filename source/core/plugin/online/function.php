<?php



if(!defined('IN_OEPHP')) {
	exit('Access Denied');
}

function online_write_cache($cacheData, $file) {
    $cacheData = serialize($cacheData);
	$cachefile = ROOT.'./source/plugin/online/cache/'.$file;
	@$fp = fopen($cachefile, 'wb') OR error('读取缓存数据失败。如果您使用的是Unix/Linux主机，请修改缓存目录 (source/plugin/online/cache/'.$file.') 下所有文件的权限为777。如果您使用的是Windows主机，请联系管理员，将该目录下所有文件设为everyone可写', '', 1);
	@$fw = fwrite($fp, $cacheData) OR error('写入缓存数据失败，缓存目录 (source/plugin/online/cache/'.$file.') 不可写');
	@fclose($fp);
}


function online_read_cache($file) {
	$cachefile = ROOT . './source/plugin/online/cache/'.$file;
    if (false === file_exists($cachefile)) {
        return array();
    }
    else {
    	if ($fp = fopen($cachefile, 'r')) {
    		$data = fread($fp, filesize($cachefile));
    		fclose($fp);
            return YHandle::dounSerialize($data);
    		
    	}
        else {
            return array();
        }
    }
}


function online_del_cache($file) {
    $cachefile = ROOT.'./source/plugin/online/cache/'.$file;
    if (true === file_exists($cachefile)) {
        @unlink($cachefile);
    }
}


function online_plugin_preview() {
    
    $online = online_read_cache('config');
    if (!empty($online)) {
        
        
        if ($online['type'] == 1 OR $online['type'] == 2) {
            
            
            $array = online_read_cache('online');
            if (!empty($array)) {
                $array = YHandle::sysSortArray($array, 'orders');
            }
            require_once(ROOT.'./source/plugin/online/block/skin_css.tpl.php');
            require_once(ROOT.'./source/plugin/online/block/skin_body.tpl.php');
            
            
            if ($online['type'] == 1){
                require_once(ROOT.'./source/plugin/online/block/float_left.tpl.php');
            }
            else {
                require_once(ROOT.'./source/plugin/online/block/float_right.tpl.php');
            }
        }
    }
}
?>
