<?php


namespace ng169\tool;
use ng169\Y;

checktop();
class YHook extends Y{	
	
	public static function addAction($system_hook, $plugin_action){
		global $plugin_hooks;
		if (!@in_array($plugin_action, $plugin_hooks[$system_hook])){
			$plugin_hooks[$system_hook][] = $plugin_action;
		}
		return true;
	}

	
	public static function doAction($hook){
		global $plugin_hooks;
		$args = array_slice(func_get_args(), 1);
		if (isset($plugin_hooks[$hook])){
			foreach ($plugin_hooks[$hook] as $function){
				$string = call_user_func_array($function, $args);
			}
		}
	}
}
?>
