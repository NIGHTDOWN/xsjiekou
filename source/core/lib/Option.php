<?php


namespace ng169\lib;
use ng169\Y;

checktop();
class Option extends Y
{

  private static $config = [];
  /**
  * 获取配置
  * @param string $optionname
  * $optionname支持点号分割
  * @return array
  */
  public static function get($optionname = '')
  {
    if (!$optionname) {
      return $config;
    }
    $options = explode('.',$optionname);
    $conf    = self::$config;

    foreach ($options as  $level=>$index) {

      if (isset($conf[$index])) {
        $conf = $conf[$index];
      }
    }

    return $conf;
  }

  /**
  * 加载配置文件
  * inc就加入conf
  * 非inc就引入
  * @return
  */
  public static  function init()
  {
    /*Y::$conf =&self::$config;*/
    $dir  = CONF;
    if (!is_dir($dir)) {
      error($dir.__('目录不存在'));
    }

    $dir = new \DirectoryIterator($dir);

    foreach ($dir as $F) {
      if ($F->isFile()) {

        $conffile = $F->getPathname();
        $name     = pathinfo($conffile,PATHINFO_BASENAME );
        $names    = explode('.',$name);
        
        if ($names[1] == 'inc') {
          $conf = include($conffile);
          self::$config[$names[0]] = $conf;
        }
        else {
        	
        	if ($names[sizeof($names)-1] == 'php') {
        		im($conffile);
        	}
          
        }

      }
    }
    //加载站点设置
    /* self::loadcache();*/
  }
  /**
  * 加载配置缓存
  *
  * @return
  */
  public static function LoadSiteCache()
  {
    /* self::$config['site'] = $conf;*/
    $cache = Y::$cache->get('options');
   
    if ($cache[0]) {
    	self::$config['site'] = ($cache[1]);
    
    }else{
		self::ReloadCache();
	}
    Y::$conf=self::$config['site'];
   
  }
  private static function ReloadCache()
  {
  
    $where = ['flag'=>0];
   
    $data  = T('options');
   
    $data=$data->get_all($where);
     
    if (!$data){error(__('系统设置获取失败'));}
    foreach($data as $list){
	self::$config['site'][$list['optionname']] = $list['optionvalue'];	
	}
    
    Y::$cache->set('options',self::$config['site']);
    
  }
}

?>
