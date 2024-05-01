<?php


checktop();
class cacheClass
{
    #数据库适配器
    private $db;
    #单例容器
    private static $instance = null;
    public function __construct()
    {
        $this->db =new daoClass();
    }
    
    public function updateCache($cacheMethodName = null)
    {
        if($cacheMethodName!=null){
            $this->cache_del($cacheMethodName);
        }else{
            $this->cache_del();
        }
        if (is_string($cacheMethodName)) {
            if (method_exists($this, 'cache_' . $cacheMethodName)) {
                call_user_func(array($this, 'cache_' . $cacheMethodName));
            }
            return;
        }
        
        if (is_array($cacheMethodName)) {
            foreach ($cacheMethodName as $name) {
                if (method_exists($this, 'cache_' . $name)) {
                    call_user_func(array($this, 'cache_' . $name));
                }
            }
            return;
        }
        
        if ($cacheMethodName == null) {
            
            $cacheMethodNames = get_class_methods($this);

            foreach ($cacheMethodNames as $method) {
                if (preg_match('/^cache_/', $method)) {
                    call_user_func(array($this, $method));
                }
            }
        }
    }

    
    private function writeCache($cacheData, $cacheName)
    {
        $cachefile = ROOT . './data/cache/' . $cacheName;
        @$fp = fopen($cachefile, 'wb') or error('读取缓存数据失败。如果您使用的是Unix/Linux主机，请修改缓存目录 (data/cache) 下所有文件的权限为777。如果您使用的是Windows主机，请联系管理员，将该目录下所有文件设为everyone可写',
            '', 1);
        @$fw = fwrite($fp, $cacheData) or error('写入缓存数据失败，缓存目录 (data/cache) 不可写');
        $this->{$cacheName . '_cache'} = null;
        @fclose($fp);
    }

    
    public function readCache($cacheName)
    {
        

        if ($this->{$cacheName . '_cache'} != null) {
            return $this->{$cacheName . '_cache'};
        } else {

            $cachefile = ROOT . './data/cache/' . $cacheName;
            

            if (!is_file($cachefile) || filesize($cachefile) <= 0) {
                
                if (method_exists($this, 'cache_' . $cacheName)) {
                    call_user_func(array($this, 'cache_' . $cacheName));
                }
            }
            if ($fp = fopen($cachefile, 'r')) {
                $data = fread($fp, filesize($cachefile));
                fclose($fp);
                
                
                if (!empty($data)) {
                    $cache_data = unserialize($data);
                }
                

                return $cache_data;
            }
        }
    }

    
    public function checkCaChe($cachename)
    {
        $cachefile = ROOT . './data/cache/' . $cachename;

        if (file_exists($cachefile) || file_exists($cachefile . '.cache')) {

            return true;
        } else {

            return false;
        }
    }

    
    private function _unSerialize($string)
    {
        if (!empty($string)) {
            if (strtolower(OESOFT_CHARSET) == 'utf-8') {
                return $this->_utf_unserialize($string);

            } else {
                return $this->_gbk_unserialize($string);
            }
        } else {
            return '';
        }
    }
    private function _utf_unserialize($serial_str)
    {
        $serial_str = preg_replace('!s:(\d+):"(.*?)";!se', "'s:'.strlen('$2').':\"$2\";'",
            $serial_str);
        $serial_str = str_replace("\r", "", $serial_str);
        return @unserialize($serial_str);
    }
    private function _gbk_unserialize($serial_str)
    {
        $serial_str = preg_replace('!s:(\d+):"(.*?)";!se', '"s:".strlen("$2").":\"$2\";"',
            $serial_str);
        $serial_str = str_replace("\r", "", $serial_str);
        return @unserialize($serial_str);
    }

    
    
    public function writeDcache($cacheData, $cacheName)
    {
        
        $cachefile = ROOT . './data/cache/' . $cacheName . '';
        @$fp = fopen($cachefile, 'wb') or error('读取缓存数据失败。如果您使用的是Unix/Linux主机，请修改缓存目录 (data/cache) 下所有文件的权限为777。如果您使用的是Windows主机，请联系管理员，将该目录下所有文件设为everyone可写',
            '', 1);
        @$fw = fwrite($fp, $cacheData) or error('写入缓存数据失败，缓存目录 (data/cache) 不可写');
        @fclose($fp);
    }

    
    public function readDcache($cacheName, $serialize = 1)
    {
        $cache_data = null;
        $cachefile = ROOT . './data/cache/' . $cacheName . '.cache';
        if (!is_file($cachefile) || filesize($cachefile) <= 0) {
        } else {
            if ($fp = fopen($cachefile, 'r')) {
                $data = fread($fp, filesize($cachefile));
                fclose($fp);
                if ($serialize == 1) {
                    $cache_data = $this->_unSerialize($data);
                } else {
                    $cache_data = $data;
                }
            }
        }
        return $cache_data;
    }
    
    private function cache_del($val = null)
    {
        $dirName = ROOT . './data/cache';
    
        if (file_exists($dirName) && $handle = opendir($dirName)) {
            while (false !== ($item = readdir($handle))) {
               
                $f=$dirName . '/' . $item;
              
                if ($val != null) {
                    if (strpos($item, $val)) {
                        if (file_exists($f) && is_dir($f)) {
                            
                        } else {
                            if (unlink($f)) {
                            
                            }
                        }
                    }
                } else {
                  
                    if (true) {
                        if (file_exists($f) && is_dir($f)) {
                            
                        } else {
                         
                            if (unlink($f)) {
                               
                            }
                        }
                    }
                }
            }
            
            closedir($handle);
        }

    }

    
    private function cache_options()
    {
        $options_cache = array();
      
     
        $data = $this->db->t("options")->s(0);
    
        foreach ($data as $key => $value) {
            $options_cache[$value['optionname']] = $value['optionvalue'];
        }
        $cacheData = serialize($options_cache);
        $this->writeCache($cacheData, 'options');
        unset($options_cache, $data, $cacheData);
    }

    
    
    
    
    
    
    
    
    
    
    
    
}
?>
