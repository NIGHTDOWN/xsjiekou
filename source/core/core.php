<?php

namespace ng169;

use ng169\lib\Lang;
use ng169\lib\Log;
use ng169\lib\Option;
use ng169\cache\Rediscache;
checktop();
class Y
{
    protected static $instance = array();
    public static $wrap_admin = array();
    public static $wrap_merchant = array();
    public static $wrap_where = array();
    public static $wrap_page = array();
    public static $wrap_user = array();
    public static $wrap_head = array();
    public static $wrap_city = '';
    public static $urlpath = null;
    public static $conf = array();
    public static $newconf = array();
    public static $urlsuffix = 'php';
    public static $tplpath = 'tpl/templets/';
    public static $skinpath = null;
    public $seo = array('desc' => '', 'keyword' => '', 'title' => '');
    #全局缓存对象
    public static $cache = null;
    public static $dao = null;
    //需要登入的方法(空表示禁止所有，*表示允许所有)
    protected $noNeedLogin = [];
    //需要验权限方法(空表示禁止所有，*表示允许所有)
    protected $noNeedPower = [];
    public static function __run()
    {
        //载入配置
        //载入配置语言
        //载入配置缓存
        //初始化service
        // Lang::init();
        Option::init();
        #载入目录
        self::$urlpath = PATH_URL;
        #载入缓存
        /* self::loadLib('widget');*/
        #载入插件
        #开启全局缓存对象

        self::$newconf = include CONF . '/txtconf.php';
        switch (CACHE_TYPE) {
            case 'nosql':
                Y::$cache = new \ng169\cache\Nosql();
                break;
            case 'mysql':
                Y::$cache = new \ng169\cache\Mysql();
                break;
            case 'file':
                Y::$cache = new \ng169\cache\File();
                break;
            case 'redis':
                Y::$cache = Rediscache::getRedis();
                break;
        }
        #载入全局缓存
        #载入模板
        //Option::LoadSiteCache();
        self::_runView();
        #载入钩子
        APP::initHook();
        // self::$dao = new \ng169\db\daoClass;
        #载入异步组件
        Y::loadTool('asyn');
    }

    #smarty
    public static function _runView()
    {

        #载入模板文件
        im(CORE . 'tpl.php');
        /*im(CORE.'smarty/class.smarty.php');*/
        TPL::__run();
    }
    public function log($txt)
    {
        Log::txt($txt);
    }

    public static function loadTool($packs)
    {
        if (is_array($packs)) {
            foreach ($packs as $key => $value) {
                $packname = $value;
                $class_path = TOOL . FG . $value . G_EXT;

                if (file_exists($class_path)) {
                    require_once $class_path;
                }
            }
        } else {
            $class_path = TOOL . FG . $packs . G_EXT;
            if (file_exists($class_path)) {
                require_once $class_path;
            }
        }
    }
    public static function loadAPI($apiid)
    {
        $apifile = API . $apiid . '/api.php';

        if (file_exists($apifile)) {

            require_once $apifile;
        } else {
            error($apiid . '支付接口不存在');
        }
        return true;
    }

    public static function loadLib($packs)
    {
        if (is_array($packs)) {
            foreach ($packs as $key => $value) {
                $packname = $value;
                $class_path = LIB . FG . $value . G_EXT;
                if (file_exists($class_path)) {
                    require_once $class_path;
                }
            }
        } else {
            /* $class_path = self::_getStaticPath($packs, 'lib');*/

            $class_path = LIB . FG . $packs . G_EXT;
            if (file_exists($class_path)) {
                require_once $class_path;
            }
        }
    }

    public static function import($name, $type)
    {
        $index = $name . $type;
        $name = ucfirst($name);
        $cls = "ng169\\$type\\$name";
        if (!isset(self::$instance['class'][$index])) {

            if (!class_exists($cls)) {
                error($cls . __('导入类不存在'));
            }
            $my_class = new $cls;
            self::$instance['class'][$index] = $my_class;
        }
        return self::$instance['class'][$index];
    }

    //加载模型
    public static function model($model_name, $type)
    {

        $typedir = ['im' => 'index', 'am' => 'admin'];
        $dir = $type;
        if (isset($typedir[$type])) {
            $dir = $typedir[$type];
        }
        $index = $model_name . $type;
        $modelcls = "ng169\\model\\$dir\\$model_name";

        if (!isset(self::$instance['model'][$index])) {

            if (!class_exists($modelcls)) {

                error($modelcls . __('模型不存在'));
            }
            $my_model = new $modelcls;
            self::$instance['model'][$index] = $my_model;
        }
        return self::$instance['model'][$index];
    }

   
    public static function table($name)
    {
        $file = 'model';
        if (\ng169\tool\Request::getGet('m') == 'admin') {
            $file = 'A' . $file;
        } else {
            $file = 'I' . $file;
        }
        /*require_once (MODEL . $file);*/
        $calss = "\\ng169\\model\\" . $file;
        return new $calss($name);
    }
}
