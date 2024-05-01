<?php




if(!defined('IN_OEPHP')) {
	exit('Access Denied');
}

require_once(ROOT.'./source/plugin/online/function.php');

function online_plugin_setting() {
    $data = array();
    
    $data = online_read_cache('config');
    require_once(ROOT.'./source/plugin/online/block/adm_config.tpl.php');    
}

XHook::addAction('online_plugin_setting_event', 'online_plugin_setting');


function online_plugin_savesetting() {
    $args = YRequest::getGpc(array(
        'type', 'left_leftpr', 'left_toppr',
        'right_rightpr', 'right_toppr',
        'title', 'close',
        'skin', 'color', 'qqicon', 'msnicon', 'skypeicon',
        'taobaoicon', 'aliicon', 
    ));
    $remark = YRequest::getArgs('remark', '', false);
    $args['type'] = intval($args['type']);
    $args['left_leftpr'] = intval($args['left_leftpr']); 
    $args['left_toppr'] = intval($args['left_toppr']); 
    $args['right_rightpr'] = intval($args['right_rightpr']); 
    $args['right_toppr'] = intval($args['right_toppr']); 
    $args = array_merge($args, array('remark'=>$remark));
    
    online_write_cache($args, 'config');
    error('设置成功', __ADMIN_FILE__.'?c=plugin&plugin_id=online&a=setting', 0);
}


function online_plugin_list(){
    $data = array();
    
    $data = online_read_cache('online');
    if (empty($data)) {
        $count = 0;
    }
    else {
        
        $data = YHandle::sysSortArray($data, 'orders');
        $count = count($data);
    }
    
    require_once(ROOT.'./source/plugin/online/block/adm_list.tpl.php');
    
}

XHook::addAction('online_plugin_list_event', 'online_plugin_list');


function online_plugin_saveadd(){
    $args = YRequest::getGpc(array(
        'orders', 'name', 'type', 'number', 'show'
    ));
    if (empty($args['name'])){
        error('请填写客服名称', '', 1);
    }
    if (empty($args['number'])) {
        error('请填写客服号码', '', 1);
    }
    $args['orders'] = intval($args['orders']);
    $args['show'] = intval($args['show']);
    
    
    $array = array();
    $array = online_read_cache('online');
    if (empty($array)) {
        $array = array($args);
    }
    else {
        $array = array_merge($array, array($args));
    }
    
    online_write_cache($array, 'online');
    error('添加成功', __ADMIN_FILE__.'?c=plugin&plugin_id=online&a=setting&do=list', 0); 
}


function online_plugin_saveupdate() {
    $array_id = YRequest::getArray('id');
    if (empty($array_id)) {
        error('对不起，没有要更新的数据。', '', 1);
    }
    $array = array();
    for($ii=0; $ii<count($array_id); $ii++){
        $id = intval($array_id[$ii]);
        
        $args = NULL;
        $args = array(
            'orders'=>YRequest::getInt('orders_'.$id),
            'name'=>YRequest::getArgs('name_'.$id),
            'type'=>YRequest::getArgs('type_'.$id),
            'number'=>YRequest::getArgs('number_'.$id),
            'show'=>YRequest::getInt('show_'.$id),
        );
        $array[] = $args;      
    }
    if (!empty($array)) {
        
        online_del_cache('online');
        
        online_write_cache($array, 'online');
        
        error('更新成功', __ADMIN_FILE__.'?c=plugin&plugin_id=online&a=setting&do=list', 0);
    }
    else {
        error('更新失败，没有要更新的数据！', '', 1);
    }
}


function online_plugin_del() {
    $id = YRequest::getInt('id');
    if ($id<1) {
        error('请选择要删除的ID', '', 1);
    }    
    
    $array = array();
    $array = online_read_cache('online');
    if (empty($array)) {
        error('对不起，载入数据失败！', '', 1);
    }
    else {
        
        $array = YHandle::sysSortArray($array, 'orders');
        unset($array[$id-1]);        
    }
    
    online_write_cache($array, 'online');
    error('删除成功', __ADMIN_FILE__.'?c=plugin&plugin_id=online&a=setting&do=list', 0);
}


XHook::addAction('online_plugin_preview_event', 'online_plugin_preview');

?>
