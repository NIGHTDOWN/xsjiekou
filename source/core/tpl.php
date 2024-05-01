<?php


namespace ng169;

use \ng169\Y;
use \ng169\template\Template_Lite;
use \ng169\service\Output;
checktop();
#smarty class file
class TPL extends Y
{
    #smarty class
    private static $tpl = null;
    #cache dir
    private static $cache_dir = 'tpl/_caches';
    #compiled dir
    private static $compiled_dir = 'tpl/_compiled';
    #起始标签
    private static $left_label = '<!--{';
    #结束标签
    private static $right_label = '}-->';
    #是否检测编译
    private static $compile_check = false;
    /*private static $force_compile = G_SMARTY;*/
    #是否缓存
    private static $cacheing = true;
    private static $cache_lifetime = G_DAY; //缓存有效时间
    #是否允许PHP脚本
    private static $allow_php_tag = false;
    #Initialization smarty
    public static
    function __run()
    {
    	im(CORE.'/template/src/class.template.php');
    	
    	self::$tpl = new Template_Lite;
    	
    	
        self::$tpl->force_compile=1;
       
        self::$tpl->left_delimiter = self::$left_label;
        self::$tpl->right_delimiter = self::$right_label;
        
     
        self::$tpl->compile_dir = self::$compiled_dir;
        self::$tpl->cache_dir = self::$cache_dir;
    
      
        self::$tpl->allow_php_tag = self::$allow_php_tag;
        self::$tpl->cache_lifetime = self::$cache_lifetime;
        self::$tpl->force_compile = true;
		self::$tpl->compile_check = true;
		self::$tpl->force_compile = G_COMPILE_TPL;
		self::$tpl->cache = false;
        self::$tpl->config_overwrite = false;
         
      
        /*
        self::$tpl = new Smarty;
        self::$tpl->setTemplateDir(ROOT);
        self::$tpl->setCacheDir(ROOT . self::$cache_dir);
        self::$tpl->setCompileDir(ROOT . self::$compiled_dir);
        self::$tpl->left_delimiter = self::$left_label;
        self::$tpl->right_delimiter = self::$right_label;
        
        self::$tpl->compile_check = self::$compile_check;
    
      
        self::$tpl->allow_php_tag = self::$allow_php_tag;
        self::$tpl->cache_lifetime = self::$cache_lifetime;
          self::$tpl->caching  = self::$cacheing;
         
        self::$tpl->force_cache  = G_CLEAR_CACHE;*/
        
      
        self::_initLabel();
    }
    public static
    function getValue($who = null)
    {
        if ($who != null) {
            $val = self::$tpl->tpl_vars;
            if (isset($val[$who])) {
                return $val[$who]->value;
            }
            $who = explode('.', $who);
            switch (sizeof($who)) {
                case '1':
                $who = $val['config']->value[$who[0]];
                break;
                case '2':
                $who = $val[$who[0]]->value[$who[1]];
                break;
            }
        }

        return $who;
    }
    #Initialization label
    private static
    function _initLabel()
    {
        $var_array = array(
            'urlpath'          => PATH_URL,#url路劲
            'config'=> parent::$conf,#配置参数
            'page_charset'=> G_CHARSET,#编码
            'tplpath'=> parent::$tplpath,#当前模板路径
            'tplpre'=> G_TPLPRE,#模板后缀
            'static'=> G_STATIC,#模板静态库
            'staticjs'=> G_STATIC.'js/',#模板静态库
            'staticimg'=> G_STATIC.'images/',#模板静态库
            'staticcss'=> G_STATIC.'css/',#模板静态库
          /*  'copyright_header'=> COPYRIGHT_HEADER,
            'copyright_author' => COPYRIGHT_AUTHOR,*/
          /*  'powerby'          =>POWERBY,*/
            /*'copyright_type'   => COPYRIGHT_TYPE,
            'copyright_version'=> COPYRIGHT_VERSION,
            'copyright_release'=> COPYRIGHT_RELEASE,*/
            /*'siteurl'          => G_SITEURL,*/
			'realpath'=>TPL,
			'img404'=>IMG404,
			'GTM'=>gmdate("l d F Y H:i:s"),
        );
        self::assign($var_array);
    }

    public static
    function assign($array)
    {
        if (is_array($array)) {
            foreach ($array as $key => $value) {
                self::$tpl->assign($key, $value);
                Output::set($key,$value);
            }
        }
    }


    public static
    function display($tplfile, $iscache = false)
    {

        if ($iscache) {
        	self::setCache(true);
            $cacheid = self::_getURI($tplfile);
          	$cacheid.=$iscache;
         	$html= self::$tpl->display($tplfile, $cacheid);
       
        }else{
        	
        	/*self::$tpl->setCaching(false);*/
           $html= self::$tpl->display($tplfile,null);
        }
       
        Output::show($html);
    }


    public static
    function setCache()
    {
    		$cache_seconds = CACHE_TIMEOUT;
            self::$tpl->setCaching(true);
            /*self::$tpl->setCacheLifetime($cache_seconds);*/
            return true;
       /* if (intval(parent::$conf['cachstatus']) == 1 ) {
            
            return true;
        }else {
            return false;
        }*/
    }


    public static
    function getCache($tplfile,$id)
    {
        $cacheid = self::_getURI($tplfile).$id;
        
      return self::$tpl->is_cached($tplfile, $cacheid);
        
    }


    private static
    function _getURI($tpl=null)
    {
    	$tpl=$tpl?$tpl:$_SERVER["REQUEST_URI"];
        return md5($_SERVER["REQUEST_URI"]);
    }


    public static
    function clearComplied()
    {
        self::$tpl->clear_compiled_tpl();
    }


    public static
    function clearAllCache()
    {
    	
        self::$tpl->clear_all_cache();
    }


    public static
    function clearCache($tplfile, $cacheid = null)
    {
        if (!empty($tplfile)) {
            $cacheid = self::_getURI();
        }
       
        self::$tpl->clear_cache($tplfile, $cacheid);
    }


    public static
    function regFunction($hook, $function)
    {
    	$namespace="\\ng169\\hook\\";
        self::$tpl->register_function( $function,$namespace.$hook);
    }

    public static
    function fetch($file)
    {
        if (!empty($file)) {
            return self::$tpl->fetch($file);
        }else {
            return null;
        }
    }

}

?>
